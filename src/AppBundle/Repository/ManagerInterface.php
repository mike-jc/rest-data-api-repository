<?php

namespace AppBundle\Repository;

use AppBundle\Entity\EntityInterface;

interface ManagerInterface {

    /**
     * EntityManager constructor.
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository);

    /**
     * @param string $class
     * @return RepositoryInterface
     */
    public function getRepository($class);

    /**
     * @return RestClient
     */
    public function getDataClient();

    /**
     * @param EntityInterface $entity
     * @param array $filteredProperties
     * @return EntityInterface
     */
    public function save(EntityInterface $entity, array $filteredProperties = []);

    /**
     * @param EntityInterface $entity
     * @return bool
     */
    public function delete(EntityInterface $entity);
}