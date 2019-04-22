<?php

namespace AppBundle\Helper;

use AppBundle\Service\InstanceConfig;
use Doctrine\Common\Annotations\AnnotationReader;
use League\OAuth2\Client\Token\AccessToken;
use AppBundle\Repository\MetaData\Associator;
use AppBundle\Repository\MetaData\Loader;
use AppBundle\Repository\MetaData\Mapper;
use AppBundle\Repository\MetaData\Monitor;
use AppBundle\Repository\RestClient;
use AppBundle\Repository\EntityRepository;
use AppBundle\Repository\EntityManager;

trait RepositoryTrait {
    use SystemParametersTrait;

    /**
     * @return RestClient
     */
    static public function createRestClient() {
        $params = self::getSystemParameters();

        $instanceConfiguration = new InstanceConfig($params['data_api_url'], null, null, null,null, !empty($options['is_local']));
        $params['data_api_url'] = $instanceConfiguration->getDataApiUrl();

        $baseUrl = !empty($params['data_api_url']) ? $params['data_api_url'] : "http://127.0.0.1:8000";

        $client = new RestClient($baseUrl);
        // TODO: set here some test access token
        $client->setAccessToken("");

        return $client;
    }

    /**
     * @return Loader
     */
    static public function createMetaDataLoader() {
        return new Loader(new AnnotationReader(), 'AppBundle\\Entity');
    }

    /**
     * @return Mapper
     */
    static public function createMetaDataMapper() {
        return new Mapper(new Associator(), new Monitor(self::createMetaDataLoader()));
    }

    /**
     * @return EntityRepository
     */
    public static function createRepository() {
        return new EntityRepository(self::createRestClient(), self::createMetaDataLoader(), self::createMetaDataMapper());
    }

    /**
     * @return EntityManager
     */
    public static function createRepositoryManager() {
        return new EntityManager(self::createRepository());
    }
}