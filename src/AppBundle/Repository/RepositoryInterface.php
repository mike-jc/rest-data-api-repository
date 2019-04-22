<?php

namespace AppBundle\Repository;

use AppBundle\Entity\EntityInterface;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Repository\MetaData\LoaderInterface;
use AppBundle\Repository\MetaData\MapperInterface;

interface RepositoryInterface {

    /**
     * @param RestClient $restClient
     * @param LoaderInterface $loader
     * @param MapperInterface $mapper
     */
    public function __construct(RestClient $restClient, LoaderInterface $loader, MapperInterface $mapper);

    /**
     * @return RestClient
     */
    public function getDataClient();

    /**
     * @param string $class
     * @param bool $clone
     * @return $this
     */
    public function setEntity($class, $clone = true);

    /**
     * @param string|int $id
     * @return EntityInterface
     */
    public function find($id);

    /**
     * @param array $filters
     * @param mixed $order
     * @param int|null $page
     * @return EntityCollection<EntityInterface>
     */
    public function findBy(array $filters = [], $order = [], $page = null);

    /**
     * @param array $filters
     * @param mixed $order
     * @return EntityInterface
     */
    public function findOneBy(array $filters = [], $order = []);

    /**
     * @param mixed $order
     * @return EntityCollection<EntityInterface>
     */
    public function findAll($order = []);

    /**
     * @param EntityInterface $entity
     * @return EntityInterface
     */
    public function save(EntityInterface $entity);

    /**
     * @param EntityInterface $entity
     * @return bool
     */
    public function delete(EntityInterface $entity);
}