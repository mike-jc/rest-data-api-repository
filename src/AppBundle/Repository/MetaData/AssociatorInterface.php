<?php

namespace AppBundle\Repository\MetaData;

use AppBundle\Entity\EntityInterface;

interface AssociatorInterface {

    /**
     * @param EntityInterface $entity
     * @param bool $hollow entity has only id not null, the rest of the properties are null
     * @return EntityInterface|null
     */
    public function addObject(EntityInterface $entity, $hollow = false);

    /**
     * @param string $class
     * @param int|null $id
     * @return bool
     */
    public function isHollowObject($class, $id);

    /**
     * @param string $class
     * @param int|null $id
     * @return EntityInterface|null
     */
    public function getObject($class, $id);

    /**
     * @param string $class
     * @param string|array $data
     * @param \Closure $mapping
     * @return EntityInterface|null
     */
    public function getCachedOrMappedObject($class, $data, \Closure $mapping);
}