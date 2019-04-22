<?php

namespace AppBundle\Repository\MetaData;

use AppBundle\Entity\EntityInterface;
use AppBundle\Entity\Type\EntityCollection;

interface MonitorInterface {

    /**
     * @param LoaderInterface $loader
     */
    public function __construct(LoaderInterface $loader);

    /**
     * @param EntityInterface $entity
     * @return string
     */
    public function getSnapshotKey(EntityInterface $entity);

    /**
     * @param EntityInterface $entity
     * @param bool $hollow entity has only id not null, the rest of the properties are null
     * @return EntityInterface
     */
    public function addSnapshot(EntityInterface $entity, $hollow = false);

    /**
     * @param EntityInterface $entity
     * @return EntityInterface|null
     */
    public function getSnapshot(EntityInterface $entity);

    /**
     * @param mixed $valueSnapshot
     * @param mixed $value
     * @return EntityInterface|null
     */
    public function getSnapshotFromValueIfNull($valueSnapshot, $value);

    /**
     * @param EntityInterface|null $snapshot
     * @param string $propName
     * @return EntityInterface|null
     */
    public function getSnapshotProperty($snapshot, $propName);

    /**
     * @param EntityInterface|null $snapshot
     * @param string $methodName
     * @return EntityInterface|null
     */
    public function runSnapshotMethod($snapshot, $methodName);

    /**
     * @param EntityCollection|null $snapshot
     * @param EntityInterface $item
     * @return EntityInterface|null
     */
    public function findSnapshot($snapshot, EntityInterface $item);

    /**
     * @param EntityCollection|null $collection
     * @param EntityCollection|null $snapshot
     * @return bool
     */
    public function bothEmpty($collection, $snapshot);

    /**
     * @param mixed $value
     * @param mixed $snapshot
     * @return bool
     */
    public function isEqualAfterMapping($value, $snapshot);
}