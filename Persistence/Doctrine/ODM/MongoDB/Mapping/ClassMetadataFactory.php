<?php

/**
 * This file is part of the Cubiche package.
 *
 * Copyright (c) Cubiche
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cubiche\Infrastructure\Model\Persistence\Doctrine\ODM\MongoDB\Mapping;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadataFactory as BaseClassMetadataFactory;
use Doctrine\ODM\MongoDB\DocumentManager;
use Cubiche\Infrastructure\Model\Persistence\Doctrine\ODM\MongoDB\Events;
use Cubiche\Infrastructure\Model\Persistence\Doctrine\ODM\MongoDB\Event\PreLoadClassMetadataEventArgs;
use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;

/**
 * ClassMetadata Factory Class.
 *
 * @author Karel Osorio Ramírez <osorioramirez@gmail.com>
 */
class ClassMetadataFactory extends BaseClassMetadataFactory
{
    /**
     * @var ClassMetadata[]
     */
    private $loadedMetadata = [];

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * {@inheritdoc}
     *
     * @see \Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory::getMetadataFor()
     */
    public function getMetadataFor($className)
    {
        if (!isset($this->loadedMetadata[$className])) {
            $evm = $this->dm->getEventManager();
            if ($evm->hasListeners(Events::PRE_LOAD_CLASSMETADATA)) {
                $eventArgs = new PreLoadClassMetadataEventArgs($className, $this->dm);
                $evm->dispatchEvent(Events::PRE_LOAD_CLASSMETADATA, $eventArgs);
            }

            $classMetadata = parent::getMetadataFor($className);

            if ($evm->hasListeners(Events::POST_LOAD_CLASS_METADATA)) {
                $eventArgs = new LoadClassMetadataEventArgs($classMetadata, $this->dm);
                $evm->dispatchEvent(Events::POST_LOAD_CLASS_METADATA, $eventArgs);
            }

            $this->loadedMetadata[$className] = $classMetadata;
        }

        return $this->loadedMetadata[$className];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Doctrine\ODM\MongoDB\Mapping\ClassMetadataFactory::setDocumentManager()
     */
    public function setDocumentManager(DocumentManager $dm)
    {
        parent::setDocumentManager($dm);
        $this->dm = $dm;
    }
}
