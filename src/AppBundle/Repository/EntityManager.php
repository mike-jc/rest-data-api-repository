<?php

namespace AppBundle\Repository;

use AppBundle\Entity\EntityInterface;

class EntityManager implements ManagerInterface {
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * EntityManager constructor.
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository) {
        $this->repository = $repository;
    }

    /**
     * @param string $class
     * @return RepositoryInterface
     */
    public function getRepository($class) {
        return $this->repository->setEntity($class, true);
    }

    /**
     * @return RestClient
     */
    public function getDataClient() {
        return $this->repository->getDataClient();
    }

    /**
     * @param EntityInterface $entity
     * @param array $filteredProperties
     * @return EntityInterface
     */
    public function save(EntityInterface $entity, array $filteredProperties = []) {
        if ($filteredProperties) {
            $entity->sendOnlyProperties($filteredProperties);
        }
        return $this->repository->setEntity(get_class($entity))->save($entity);
    }

    /**
     * @param EntityInterface $entity
     * @return bool
     */
    public function delete(EntityInterface $entity) {
        return $this->repository->setEntity(get_class($entity))->delete($entity);
    }
}