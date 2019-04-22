<?php

namespace AppBundle\Entity;

interface EntityInterface {

    /**
     * @return array
     */
    public function getFilteredProperties();

    /**
     * @param array $filteredProperties
     * @return $this
     */
    public function sendOnlyProperties(array $filteredProperties);

    /**
     * @return $this
     */
    public function sendOnlyId();

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return bool
     */
    public function isNew();
}