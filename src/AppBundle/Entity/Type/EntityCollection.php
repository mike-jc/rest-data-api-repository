<?php

namespace AppBundle\Entity\Type;

use DeepCopy\DeepCopy;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\EntityInterface;
use AppBundle\Helper\MethodGuesserTrait;

class EntityCollection extends ArrayCollection {
    use MethodGuesserTrait;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @return bool
     */
    public function isInitialized() {
        return $this->initialized;
    }

    /**
     * @param bool $initialized
     * @return $this
     */
    public function setInitialized($initialized) {
        $this->initialized = $initialized;
        return $this;
    }

    /**
     * @param mixed $element
     * @return bool
     */
    public function contains($element) {

        if ($element instanceof EntityInterface) {
            return (bool)current($this->filterByElement($element));
        } else {
            return parent::contains($element);
        }
    }

    /**
     * @param mixed $element
     * @return bool
     */
    public function removeElement($element) {

        if ($element instanceof EntityInterface) {
            $key = key($this->filterByElement($element));
            if ($key === false) {
                return false;
            }
            return $this->remove($key);
        } else {
            return parent::removeElement($element);
        }
    }

    /**
     * @param EntityInterface $element
     * @return EntityInterface|null
     */
    public function find(EntityInterface $element) {
        return current($this->filterByElement($element));
    }

    /**
     * @param bool $idAsKey
     * @return array
     */
    public function toArray($idAsKey = false) {
        $elements = parent::toArray();

        if ($idAsKey) {
            $elementsById = [];

            /** @var EntityInterface $entity */
            foreach ($elements as $entity) {
                $elementsById[$entity->getId()] = $entity;
            }
            return $elementsById;
        }
        return $elements;
    }

    /**
     * @param string $keyField
     * @param string|null $valueField
     * @return array
     */
    public function toAssocArray($keyField, $valueField = null) {
        if ($this->isEmpty()) {
            return [];
        }

        $first = $this->first();
        $keyGetter = $this->findGetter($first, $keyField);
        $valueGetter = $valueField ? $this->findGetter($first, $valueField) : null;

        $assoc = [];
        /** @var EntityInterface $item */
        foreach ($this->toArray(false) as $item) {
            $assoc[$item->$keyGetter()] = $valueGetter ? $item->$valueGetter() : $item;
        }

        return $assoc;
    }

    /**
     * @param string $field
     * @param bool $ascending
     * @param bool $idAsKey
     * @return array
     */
    public function toArraySortedBy($field, $ascending = true, $idAsKey = false) {
        $method = 'get'. ucfirst($field);
        $order = $ascending ? 1 : -1;
        $sorter = function ($a, $b) use ($method, $order) {
            $a = $a->{$method}();
            $b = $b->{$method}();
            return ($a == $b) ? 0 : ($a < $b ? -1 : 1) * $order;
        };

        $elements = $this->toArray($idAsKey);
        if ($idAsKey) {
            uasort($elements, $sorter);
        } else {
            usort($elements, $sorter);
        }
        return $elements;
    }

    /**
     * @return array
     */
    public function getKeys() {
        $ids = [];

        /** @var EntityInterface $entity */
        foreach ($this->toArray(false) as $entity) {
            $ids[] = $entity->getId();
        }
        return $ids;
    }

    /**
     * @param \Closure|null $filter
     * @return EntityInterface|mixed|null
     */
    public function getRandom(\Closure $filter = null) {
        $elements = $filter ? array_filter($this->toArray(false), $filter): $this->toArray(false);
        if (!$elements) {
            return null;
        }

        $randKey = array_rand($elements, 1);
        return $elements[$randKey];
    }

    /**
     * @param int|string $key
     * @return EntityInterface|mixed|null
     */
    public function getById($key) {
        $elements = $this->toArray(true);
        return isset($elements[$key]) ? $elements[$key] : null;
    }

    /**
     * @param \Closure|null $filter
     * @return EntityInterface|mixed|null
     */
    public function first(\Closure $filter = null) {
        if (is_null($filter)) {
            return parent::first();
        }

        foreach ($this->toArray(false) as $item) {
            if ($filter($item)) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @param \Closure|null $filter
     * @return int
     */
    public function count(\Closure $filter = null) {
        return count($filter ? array_filter($this->toArray(false), $filter): $this->toArray(false));
    }

    /**
     * @param EntityInterface $needle
     * @return array
     */
    protected function filterByElement(EntityInterface $needle) {
        return array_filter($this->toArray(false), function ($e) use ($needle) {
            if ($e instanceof EntityInterface) {
                if ($e->isNew() && $needle->isNew()) {
                    return $e == $needle;
                } elseif ($e->isNew() || $needle->isNew()) {
                    $copier = new DeepCopy();
                    $elementWithoutId = $copier->copy($e);
                    $elementWithoutId->setId(null);
                    $needleWithoutId = $copier->copy($needle);
                    $needleWithoutId->setId(null);
                    return $elementWithoutId == $needleWithoutId;
                } else {
                    return $e->getId() == $needle->getId();
                }
            } else {
                return $e == $needle;
            }
        });
    }
}