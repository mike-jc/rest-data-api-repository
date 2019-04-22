<?php

namespace AppBundle\Entity;

abstract class BaseEntity implements EntityInterface {
    /**
     * @var array List of object properties which are sent to Data API
     */
    private $filteredProperties = [];

    /**
     * @return array
     */
    public function getFilteredProperties() {
        return $this->filteredProperties;
    }

    /**
     * @param array $filteredProperties
     * @return $this
     */
    public function sendOnlyProperties(array $filteredProperties) {
        $properties = [];
        foreach ($filteredProperties as $property) {
            if (property_exists($this, $property)) {
                $properties[] = $property;
            }
        }
        // We always need id field for updating
        if ($this->getId() && !in_array('id', $properties)) {
            $properties[] = 'id';
        }
        $this->filteredProperties = $properties;
        return $this;
    }

    public function sendOnlyId() {
        return $this->sendOnlyProperties(['id']);
    }

    /**
     * @return int
     */
    abstract public function getId();

    /**
     * @param int $id
     * @return $this
     */
    abstract public function setId($id);

    /**
     * @return bool
     */
    public function isNew() {
        return !$this->getId();
    }
}