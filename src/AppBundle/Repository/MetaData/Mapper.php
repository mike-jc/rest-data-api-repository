<?php

namespace AppBundle\Repository\MetaData;

use Tree\Node\Node as TreeNode;
use AppBundle\Entity\EntityInterface;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Exception\MapperException;
use AppBundle\Helper\TreeTrait;

class Mapper implements MapperInterface {
    use TreeTrait;

    /**
     * @var AssociatorInterface
     */
    private $associator;
    /**
     * @var MonitorInterface
     */
    private $monitor;

    const DIRECTION_IN = 'in';
    const DIRECTION_OUT = 'out';

    /**
     * @param AssociatorInterface $associator
     * @param MonitorInterface $monitor
     */
    public function __construct(AssociatorInterface $associator, MonitorInterface $monitor) {
        $this->associator = $associator;
        $this->monitor = $monitor;
    }

    /**
     * @param array $data
     * @param EntityInterface $entity
     * @param array $metaData
     * @return EntityInterface
     * @throws MapperException
     */
    public function mapDataToEntity($data, EntityInterface $entity, $metaData) {

        // In deep recursion API returns only @id string
        if (is_string($data) && $data{0} === '/') {
            $id = (int)substr($data, strrpos($data, '/') + 1);
            return $this->makeHollowEntityOrGetFromCache($entity, $id);
        }
        // Always set id, even if there's no properties
        if (empty($metaData['extra']['properties'])) {
            return $this->makeHollowEntityOrGetFromCache($entity, !empty($data['id']) ? $data['id'] : null);
        }

        $hasProperty = false;
        foreach ($metaData['extra']['properties'] as $prop) {
            if (empty($prop['type']) || !isset($data[$prop['name']])) {
                continue;
            }

            $hasProperty = true;
            $value = $data[$prop['name']];
            if (!empty($prop['collection'])) {
                $value = $this->castToCollection($value, $prop['type'], $prop);
            } elseif (!empty($prop['class'])) {
                $value = $this->castToEntity($value, $prop['type'], $prop);
            } else {
                $value = $this->castToStdType($value, $prop['type'], self::DIRECTION_IN);
            }

            if (!empty($prop['public'])) {
                $entity->{$prop['name']} = $value;
            } else {
                $setter = 'set'. ucfirst($prop['name']);
                if (method_exists($entity, $setter)) {
                    $entity->{$setter}($value);
                } else {
                    throw new MapperException("There is no method $setter in entity class ". get_class($entity));
                }
            }
        }
        $this->associator->addObject($entity, !$hasProperty);
        $this->monitor->addSnapshot($entity, !$hasProperty);
        return $entity;
    }

    /**
     * @param EntityInterface $entity
     * @param array $metaData
     * @return \stdClass
     * @throws MapperException
     */
    public function mapEntityToObject(EntityInterface $entity, $metaData) {
        $object = new \stdClass();
        $trackedClasses = new TreeNode(get_class($entity));
        $snapshot = $this->monitor->getSnapshot($entity);

        $this->mapEntityPropertiesToObject($entity, $metaData, $trackedClasses, $snapshot, $object);

        if ($this->monitor->isEqualAfterMapping($object, $snapshot)) {
            return null; // don't send non-changed object
        }
        return $object;
    }

    /**
     * @param EntityInterface $entity
     * @param int|null $id
     * @return EntityInterface
     */
    protected function makeHollowEntityOrGetFromCache(EntityInterface $entity, $id) {
        if (!$id) {
            return $entity;
        }

        $entity->setId($id);

        $class = get_class($entity);
        if ($this->associator->getObject($class, $id)) {
            $entity = $this->associator->getObject($class, $id);
            $this->monitor->addSnapshot($entity, $this->associator->isHollowObject($class, $id));
        } else {
            $this->associator->addObject($entity, true);
            $this->monitor->addSnapshot($entity, true);
        }
        return $entity;
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param array $metaData
     * @return EntityCollection
     */
    protected function castToCollection($data, $class, $metaData) {
        $collection = new EntityCollection();
        $collection->setInitialized(true);

        foreach ($data as $row) {
            $entity = $this->associator->getCachedOrMappedObject($class, $data, function ($class) use ($row, $metaData) {
                return $this->mapDataToEntity($row, new $class, $metaData);
            });
            $collection->add($entity);
        }
        return $collection;
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param array $metaData
     * @return mixed
     */
    protected function castToEntity($data, $class, $metaData) {
        return $this->associator->getCachedOrMappedObject($class, $data, function ($class) use ($data, $metaData) {
            return $this->mapDataToEntity($data, new $class, $metaData);
        });
    }

    /**
     * @param mixed $value
     * @param string $type
     * @param string $direction "in|out"
     * @return mixed
     */
    protected function castToStdType($value, $type, $direction) {
        if (is_null($value)) {
            return $value;
        }

        switch ($type) {
            case 'bool':
            case 'boolean':
                return boolval($value);
            case 'double':
                return doubleval($value);
            case 'float':
                return floatval($value);
            case 'int':
            case 'integer':
                return intval($value);
            case 'string':
                return strval($value);
            case 'datetime':
            case 'date':
                return $direction == self::DIRECTION_IN ? new \DateTime($value, new \DateTimeZone('UTC')) : ($value instanceof \DateTime ? $value->format(\DateTime::W3C) : (string)$value);
            case 'blob':
                return $value;
            case 'array':
                return $value;
        }
        return $value;
    }

    /**
     * @param EntityInterface $entity
     * @param array $metaData
     * @param TreeNode $trackedClasses
     * @param EntityInterface|null $snapshot
     * @param \stdClass|null $object
     * @return \stdClass
     * @throws MapperException
     */
    protected function mapEntityPropertiesToObject(EntityInterface $entity, $metaData, TreeNode $trackedClasses, EntityInterface $snapshot = null, \stdClass $object = null) {
        if (is_null($object)) {
            $object = new \stdClass();
        }
        if (!$entity->isNew()) {
            $object->id = $entity->getId(); // always send id
        }
        if (!empty($metaData['endPoint'])) {
            $object->endPoint = $metaData['endPoint'];
        }
        if (empty($metaData['extra']['properties'])) {
            return $object;
        }

        foreach ($metaData['extra']['properties'] as $prop) {
            if (empty($prop['type']) || empty($prop['name']) || !property_exists($entity, $prop['name'])) {
                continue;
            }
            if ($prop['name'] == $metaData['extra']['id'] && !$entity->getId()) {
                continue;
            }
            if ($entity->getFilteredProperties() && !in_array($prop['name'], $entity->getFilteredProperties())) {
                continue;
            }

            if (!empty($prop['public'])) {
                $value = $entity->{$prop['name']};
                $valueSnapshot = $this->monitor->getSnapshotProperty($snapshot, $prop['name']);
            } else {
                $methods = [
                    'get'. ucfirst($prop['name']),
                    'has'. ucfirst($prop['name']),
                    'is'. ucfirst($prop['name']),
                ];
                $methodFound = false;
                $value = null;
                $valueSnapshot = null;
                foreach ($methods as $method) {
                    if (method_exists($entity, $method)) {
                        $methodFound = true;
                        $value = $entity->{$method}();
                        $valueSnapshot = $this->monitor->runSnapshotMethod($snapshot, $method);
                        break;
                    };
                }
                if (!$methodFound) {
                    $methods = implode(', ', $methods);
                    throw new MapperException("There is no methods $methods in entity class ". get_class($entity));
                }
            }

            if (!is_null($value)) {
                if (!empty($prop['collection'])) {
                    /** @var EntityCollection $value */
                    /** @var EntityCollection $valueSnapshot */
                    $result = [];
                    $collectionType = !empty($prop['type']) ? $prop['type'] : get_class($value->first());
                    $trackedClasses->addChild($trackedChild = new TreeNode($collectionType));
                    /** @var EntityInterface $item */
                    foreach ($value as $item) {
                        $itemSnapshot = $this->monitor->findSnapshot($valueSnapshot, $item);
                        $itemSnapshot = $this->monitor->getSnapshotFromValueIfNull($itemSnapshot, $item);
                        $propObject = $this->mapEntityPropertiesToObject($item, $prop, $trackedChild, $itemSnapshot);
                        if (!is_null($propObject)) {
                            $result[] = $propObject;
                        }
                    }
                    $value = $result;

                } elseif (!empty($prop['class'])) {
                    if ($this->isTracked($trackedClasses, $prop['type'])) {
                        // Send only id if it exists
                        if ($value instanceof EntityInterface && !$value->isNew() && !empty($prop['endPoint'])) {
                            $value = '/'. trim($prop['endPoint'], '/') .'/'. $value->getId();
                        } else {
                            continue;
                        }
                    } else {
                        $trackedClasses->addChild($trackedChild = new TreeNode($prop['type']));
                        $propSnapshot = $this->monitor->getSnapshotFromValueIfNull($valueSnapshot, $value);
                        $value = $this->mapEntityPropertiesToObject($value, $prop, $trackedChild, $propSnapshot);
                    }


                } else {
                    $value = $this->castToStdType($value, $prop['type'], self::DIRECTION_OUT);
                    $valueSnapshot = $this->castToStdType($valueSnapshot, $prop['type'], self::DIRECTION_OUT);
                }

                if ($this->monitor->isEqualAfterMapping($value, $valueSnapshot)) {
                    continue; // value isn't changed
                }
            } elseif (!is_null($snapshot) && is_null($valueSnapshot)) {
                continue; // if we don't have snapshot it's better to send value
            }

            $object->{$prop['name']} = $value;
        }

        return $object;
    }
}