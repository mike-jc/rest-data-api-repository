<?php

namespace AppBundle\Repository\MetaData;

use DeepCopy\Reflection\ReflectionHelper;
use AppBundle\Entity\EntityInterface;

/**
 * Keep all entities that have id in cache and make all nested entity properties refer to the same subentity.
 * It decrease memory usage and cause synchronization of data in all properties that are of one type
 */
class Associator implements AssociatorInterface {
    /**
     * @var array
     */
    private $objects = [];
    /**
     * @var array
     */
    private $hollowObjects = [];

    /**
     * @param EntityInterface $entity
     * @param bool $hollow
     * @return EntityInterface|null
     */
    public function addObject(EntityInterface $entity, $hollow = false) {
        if ($entity->isNew()) {
            return null;
        }

        $key = get_class($entity) ."::". $entity->getId();
        $wasHollow = !empty($this->hollowObjects[$key]) && !$hollow; // we've got the same entity but with more info

        if (empty($this->objects[$key])) {
            $this->objects[$key] = $entity;
            if ($hollow) {
                $this->hollowObjects[$key] = true;
            }
        } elseif ($wasHollow) {
            $this->flatCopyObject($entity, $this->objects[$key]); // copy new object to the one that already linked with properties
            unset($this->hollowObjects[$key]);
        }

        return $this->objects[$key];
    }

    /**
     * @param string $class
     * @param int|null $id
     * @return bool
     */
    public function isHollowObject($class, $id) {
        $key = "{$class}::{$id}";
        return !empty($this->hollowObjects[$key]);
    }

    /**
     * Check if array of new object's data is fuller than existed object (have more non-empty properties).
     * For example, every time when new data from API is parsed and mapped to the proper object this method
     * is called and helps to decide whether we need to update object or use the cached one. Thus we always
     * have objects with maximum information.
     *
     * @param object|null $oldObject
     * @param array $dataForNewObject
     * @return bool
     */
    public function isFuller($oldObject, $dataForNewObject) {
        if (is_null($oldObject) || empty($dataForNewObject)) {
            return false;
        }

        // Get list of property's names and values of the cached object
        $oldProperties = [];
        foreach (ReflectionHelper::getProperties(new \ReflectionObject($oldObject)) as $property) {
            $oldProperties[$property->getName()] = $property->getValue($oldObject);
        }

        // Check if there is some new non-empty property
        foreach ($dataForNewObject as $propName => $newValue) {
            $oldValue = isset($oldProperties[$propName]) ? $oldProperties[$propName] : null;
            if (!empty($newValue) && empty($oldValue)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $class
     * @param int|null $id
     * @return EntityInterface|null
     */
    public function getObject($class, $id) {
        if ($id) {
            $key = "{$class}::{$id}";
            return !empty($this->objects[$key]) ? $this->objects[$key] : null;
        }
        return null;
    }

    /**
     * @param string $class
     * @param string|array $data
     * @param \Closure $mapping
     * @return EntityInterface|null
     */
    public function getCachedOrMappedObject($class, $data, \Closure $mapping) {
        $id = is_array($data) && !empty($row['id']) ? $row['id'] : null;

        if ($id) {
            $key = "{$class}::{$id}";
            $object = !empty($this->objects[$key]) ? $this->objects[$key] : null;

            // object with more information may appear after mapping
            if ($this->isHollowObject($class, $id) || $this->isFuller($object, $data)) {
                return $mapping($class);
            } elseif ($object) {
                return $object;
            }
        }
        return $mapping($class);
    }

    /**
     * Copy object without cloning.
     * For properties that are objects we copy only their references
     *
     * @param object $object
     * @param object $newObject
     */
    protected function flatCopyObject($object, $newObject) {
        $reflectedObject = new \ReflectionObject($object);

        foreach (ReflectionHelper::getProperties($reflectedObject) as $property) {
            $property->setAccessible(true);
            $property->setValue($newObject, $property->getValue($object));
        }
    }
}