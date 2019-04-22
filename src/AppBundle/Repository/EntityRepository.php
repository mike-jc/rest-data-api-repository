<?php

namespace AppBundle\Repository;

use AppBundle\Repository\MetaData\LoaderInterface;
use AppBundle\Repository\MetaData\MapperInterface;
use AppBundle\Entity\EntityInterface;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Exception\HttpException;

class EntityRepository implements RepositoryInterface {
    /**
     * @var RestClient
     */
    private $restClient;
    /**
     * @var LoaderInterface
     */
    private $metadataLoader;
    /**
     * @var MapperInterface
     */
    private $metadataMapper;
    /**
     * @var array
     */
    private $metaData;

    /**
     * @param RestClient $restClient
     * @param LoaderInterface $loader
     * @param MapperInterface $mapper
     */
    public function __construct(RestClient $restClient, LoaderInterface $loader, MapperInterface $mapper) {
        $this->restClient = $restClient;
        $this->metadataLoader = $loader;
        $this->metadataMapper = $mapper;
    }

    /**
     * @return RestClient
     */
    public function getDataClient() {
        return $this->restClient;
    }

    /**
     * @param mixed $class
     * @param bool $clone
     * @return $this
     */
    public function setEntity($class, $clone = true) {
        if ($clone) {
            $newRepository = new self($this->restClient, $this->metadataLoader, $this->metadataMapper);
            return $newRepository->setEntity($class, false);
        } else {
            $this->metaData = $this->metadataLoader->load($class);
            return $this;
        }
    }

    /**
     * @param string|int $id
     * @return EntityInterface|null
     * @throws HttpException
     */
    public function find($id) {
        try {
            $data = $this->restClient->getById($this->metaData['endPoint'], $id);
            return $this->metadataMapper->mapDataToEntity($data, new $this->metaData['entity'], $this->metaData);

        } catch (HttpException $e) {
            $msg = @json_decode($e->getMessage(), true);
            if (!empty($msg['hydra:description']) && $msg['hydra:description'] == 'Not Found') {
                return null;
            }
            throw $e;
        }
    }

    /**
     * @param array $filters
     * @param mixed $order
     * @param int|null $page
     * @return EntityCollection<EntityInterface>
     */
    public function findBy(array $filters = [], $order = [], $page = null) {
        if ($page) {
            $data = $this->restClient->getPage($this->metaData['endPoint'], $page, $this->getQuery($filters, $order));
        } else {
            $data = $this->restClient->get($this->metaData['endPoint'], $this->getQuery($filters, $order));
        }

        $collection = new EntityCollection();
        foreach ($data as $row) {
            $collection->add($this->metadataMapper->mapDataToEntity($row, new $this->metaData['entity'], $this->metaData));
        }
        return $collection;
    }

    /**
     * @param array $filters
     * @param mixed $order
     * @return EntityInterface
     */
    public function findOneBy(array $filters = [], $order = []) {
        $collection = $this->findBy($filters, $order);
        return $collection->first();
    }

    /**
     * @param mixed $order
     * @return EntityCollection<EntityInterface>
     */
    public function findAll($order = []) {
        return $this->findBy([], $order);
    }

    /**
     * @param EntityInterface $entity
     * @return EntityInterface
     */
    public function save(EntityInterface $entity) {
        $object = $this->metadataMapper->mapEntityToObject($entity, $this->metaData);
        // Don't send entity without changed properties
        if (is_null($object)) {
            return $entity;
        }

        if ($entity->getId()) {
            $data = $this->restClient->put($this->metaData['endPoint'], $entity->getId(), $object);
        } else {
            $data = $this->restClient->post($this->metaData['endPoint'], $object);
        }
        return $this->metadataMapper->mapDataToEntity($data, $entity, $this->metaData);
    }

    /**
     * @param EntityInterface $entity
     * @return bool
     */
    public function delete(EntityInterface $entity) {
        if ($entity->getId()) {
            return $this->restClient->delete($this->metaData['endPoint'], $entity->getId());
        } else {
            return true;
        }
    }

    /**
     * @param array $filters
     * @param mixed $order
     * @return array
     */
    protected function getQuery(array $filters = [], $order = []) {
        $query = $filters;

        $orderQuery = [];
        foreach ((array)$order as $key => $value) {
            if (is_string($key)) {
                $orderQuery[$key] = strtolower($value);
            } else {
                foreach (explode(',', $value) as $part) {
                    list($field, $direction) = explode(' ', trim($part));
                    $orderQuery[trim($field)] = strtolower(trim($direction));
                }
            }
        }
        if ($orderQuery) {
            $query['order'] = $orderQuery;
        }

        return $query;
    }
}