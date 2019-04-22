<?php

namespace AppBundle\Repository\MetaData;

use AppBundle\Entity\EntityInterface;
use AppBundle\Exception\MapperException;

interface MapperInterface {

    /**
     * @param array $data
     * @param EntityInterface $entity
     * @param array $metaData
     * @return EntityInterface
     * @throws MapperException
     */
    public function mapDataToEntity($data, EntityInterface $entity, $metaData);

    /**
     * @param EntityInterface $entity
     * @param array $metaData
     * @return \stdClass
     * @throws MapperException
     */
    public function mapEntityToObject(EntityInterface $entity, $metaData);
}