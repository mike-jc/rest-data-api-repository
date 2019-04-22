<?php

namespace AppBundle\Repository\MetaData;

use DeepCopy\DeepCopy;
use AppBundle\Entity\EntityInterface;
use AppBundle\Entity\Type\EntityCollection;

/**
 * Keep snapshots of entities (their state right after getting from Data API)
 * and detect properties that have been changed so that only those properties will be send back
 */
class Monitor implements MonitorInterface {
    /**
     * @var DeepCopy
     */
    private $copier;
    /**
     * @var LoaderInterface
     */
    private $loader;
    /**
     * @var array
     */
    private $snapshots = [];
    /**
     * @var array
     */
    private $hollowSnapshots = [];

    /**
     * @param LoaderInterface $loader
     */
    public function __construct(LoaderInterface $loader) {
        $this->copier = new DeepCopy();
        $this->loader = $loader;
    }

    /**
     * @param EntityInterface $entity
     * @return string
     */
    public function getSnapshotKey(EntityInterface $entity) {
        return get_class($entity) ."::". $entity->getId();
    }

    /**
     * @param EntityInterface $entity
     * @param bool $hollow entity has only id not null
     * @return EntityInterface
     */
    public function addSnapshot(EntityInterface $entity, $hollow = false) {
        if ($entity->isNew()) {
            return null;
        }

        $key = $this->getSnapshotKey($entity);
        $wasHollow = !empty($this->hollowSnapshots[$key]) && !$hollow; // we've got the same entity but with more info

        if (empty($this->snapshots[$key]) || $wasHollow) {
            $this->snapshots[$key] = $this->copier->copy($entity);
            if ($hollow) {
                $this->hollowSnapshots[$key] = true;
            } elseif ($wasHollow) {
                unset($this->hollowSnapshots[$key]);
            }
        }
        return $this->snapshots[$key];
    }

    /**
     * @param EntityInterface $entity
     * @return EntityInterface|null
     */
    public function getSnapshot(EntityInterface $entity) {
        $key = $this->getSnapshotKey($entity);
        if (!empty($this->snapshots[$key]) && get_class($this->snapshots[$key]) === get_class($entity)) {
            return $this->snapshots[$key];
        }
        return null;
    }

    /**
     * @param mixed $valueSnapshot
     * @param mixed $value
     * @return EntityInterface|null
     */
    public function getSnapshotFromValueIfNull($valueSnapshot, $value) {
        if ($value instanceof EntityInterface && !$valueSnapshot) {
            return $value->isNew() ? null : $this->getSnapshot($value);
        }
        return $valueSnapshot;
    }

    /**
     * @param EntityInterface|null $snapshot
     * @param string $propName
     * @return EntityInterface|null
     */
    public function getSnapshotProperty($snapshot, $propName) {
        return is_null($snapshot) ? null : $this->getActualSnapshot($snapshot->{$propName});
    }

    /**
     * @param EntityInterface|null $snapshot
     * @param string $methodName
     * @return EntityInterface|null
     */
    public function runSnapshotMethod($snapshot, $methodName) {
        return is_null($snapshot) ? null : $this->getActualSnapshot($snapshot->{$methodName}());
    }

    /**
     * @param EntityCollection|null $snapshot
     * @param EntityInterface $item
     * @return EntityInterface|null
     */
    public function findSnapshot($snapshot, EntityInterface $item) {
        if ($item->isNew()) {
            return null;
        }
        if (is_null($snapshot)) {
            return null;
        }
        /** @var EntityCollection $snapshot */
        return $this->getActualSnapshot($snapshot->find($item));
    }

    /**
     * @param EntityCollection|null $collection
     * @param EntityCollection|null $collectionSnapshot
     * @return bool
     */
    public function bothEmpty($collection, $collectionSnapshot) {
        $collectionIsEmpty = is_null($collection) || $collection->count() == 0;
        $snapshotIsEmpty = is_null($collectionSnapshot) || $collectionSnapshot->count() == 0;
        return $collectionIsEmpty && $snapshotIsEmpty;
    }

    /**
     * Compare value and its snapshot.
     * Here we have value after mapping (filtered std object, with only changed values).
     * So value is equal to its snapshot if after filtering only id is left and it's not changed
     *
     * @param mixed $value
     * @param mixed $valueSnapshot
     * @return bool
     */
    public function isEqualAfterMapping($value, $valueSnapshot) {

        if (is_array($value) && $valueSnapshot instanceof EntityCollection) {

            $testArray = [];
            /** @var EntityInterface $item */
            foreach ($valueSnapshot as $item) {
                $testObj = new \stdClass();
                $testObj->id = $item->getId();
                $testArray[] = $testObj;
            }

            $valueClone = [];
            /** @var \stdClass $item */
            foreach ($value as $item) {
                $itemClone = clone $item;
                unset($itemClone->endPoint);
                $valueClone[] = $itemClone;
            }

            return $valueClone == $testArray;

        } elseif ($value instanceof \stdClass && $valueSnapshot instanceof EntityInterface) {

            $testObj = new \stdClass();
            $testObj->id = $valueSnapshot->getId();

            $valueClone = clone $value;
            unset($valueClone->endPoint);

            return $valueClone == $testObj;

        } elseif ($this->isIRI($value)) {

            if (is_null($valueSnapshot)) {
                return true;

            } elseif ($valueSnapshot instanceof EntityInterface) {
                $classMetaData = $this->loader->load($valueSnapshot);
                $snapshotId = !empty($classMetaData['endPoint']) ? '/'. trim($classMetaData['endPoint'], '/') .'/'. $valueSnapshot->getId() : $valueSnapshot->getId();

                return $value == $snapshotId;
            }
        }

        if ((is_null($value) && is_bool($valueSnapshot)) || (is_bool($value) && is_null($valueSnapshot))) {
            return $value === $valueSnapshot; // for the possibility to unset property
        } else {
            return $value == $valueSnapshot;
        }
    }

    /**
     * @param mixed $value
     * @return EntityInterface|null
     */
    protected function getActualSnapshot($value) {

        if ($value instanceof EntityInterface) {
            $deeperSnapshot = $this->getSnapshot($value);
            return $deeperSnapshot ? $deeperSnapshot : $value;
        }
        return $value;
    }

    protected function isIRI($value) {
        return $value && is_string($value) && $value{0} == '/' && strlen($value) < 128;
    }
}