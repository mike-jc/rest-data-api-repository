<?php

namespace Tests\AppBundle\Helper;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\Container;
use AppBundle\Repository\MetaData\Associator;
use AppBundle\Repository\MetaData\Loader;
use AppBundle\Repository\MetaData\Mapper;
use AppBundle\Repository\MetaData\Monitor;
use AppBundle\Repository\RestClient;
use AppBundle\Repository\EntityRepository;
use AppBundle\Repository\EntityManager;

trait MockRepositoryTrait {
    use MockConfigTrait;

    /**
     * @var RestClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restClientMock;

    protected $bookingForms;
    protected $meetingTypes;
    protected $users;
    protected $publicFormLogins;
    protected $settings;

    public function initRestClientData() {
        $this->meetingTypes = [
            1 => [
                'id' => 1,
                'name' => 'Meeting 30 min',
                'duration' => 30,
                'deleted' => false,
                'enabled' => true,
            ]
        ];
        $this->users = [
            2 => [
                'id' => 2,
                'name' => 'Bob Dillon',
                'username' => 'bobby@bobby.com',
                'email' => 'bobby@bobby.com',
                'enabled' => true
            ]
        ];
        $this->bookingForms = [
            1 => [
                'id' => 1,
                'name' => 'bobby-dillon-form',
                'target' => 'users',
                'webpageSlug' => 'bobbydillon',
                'showTimezone' => true,
                'meetingTypes' => $this->meetingTypes,
                'manager' => $this->users[2],
                'users' => $this->users,
            ]
        ];
        $this->settings = [
            'test-scope' => [
                ['name' => 'setting1', 'value' => 'value1'],
            ]
        ];
    }

    /**
     * @return RestClient|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRestClientMock() {

        if (!isset($this->restClientMock)) {
            /** @var \PHPUnit_Framework_MockObject_MockBuilder $restClientBuilder */
            $restClientBuilder = $this->getMockBuilder(RestClient::class);

            $that = $this;
            $this->restClientMock = $restClientBuilder->disableOriginalConstructor()->getMock();

            $this->restClientMock->method('__call')->will($this->returnCallback(function($method, $arguments) use ($that) {
                if ($method == 'getById') {
                    $endpoint = $arguments[0];
                    $id = $arguments[1];

                    if ($endpoint == 'users') {
                        return $that->users[2];
                    } elseif ($endpoint == 'meeting_types') {
                        return $that->meetingTypes[1];
                    } elseif ($endpoint == 'booking_forms') {
                        return $that->bookingForms[1];
                    }

                    return [];

                } elseif ($method == 'get') {
                    $endpoint = $arguments[0];
                    $query = $arguments[1];

                    if ($endpoint == 'addons') {
                        if (!empty($query['alias']) && $query['alias'] == 'disabled-addon') {
                            return [
                                ['alias' => 'disabled-addon', 'enabled' => false],
                            ];
                        } elseif (!empty($query['alias']) && $query['alias'] == 'enabled-addon') {
                            return [
                                ['alias' => 'enabled-addon', 'enabled' => true],
                            ];
                        } else {
                            return [
                                ['alias' => 'enabled-addon', 'enabled' => true],
                                ['alias' => 'disabled-addon', 'enabled' => false]
                            ];
                        }
                    } elseif ($endpoint == 'meeting_types') {
                        return array_values($that->meetingTypes);
                    } elseif ($endpoint == 'users') {
                        return array_values($that->users);
                    } elseif ($endpoint == 'booking_forms') {
                        return array_values($that->bookingForms);
                    } elseif ($endpoint == 'settings') {
                        return array_values($this->settings['test-scope']);
                    }

                    return [];
                }
                return [];
            }));

        }

        return $this->restClientMock;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $restClientMock
     */
    public function setRestClientMock($restClientMock) {
        $this->restClientMock = $restClientMock;
    }

    /**
     * @return Loader
     */
    public function createMetaDataLoader() {
        return new Loader(new AnnotationReader(), 'AppBundle\\Entity');
    }

    /**
     * @return Mapper
     */
    public function createMetaDataMapper() {
        return new Mapper(new Associator(), new Monitor($this->createMetaDataLoader()));
    }

    /**
     * @return EntityRepository
     */
    public function createRepository() {
        $this->initRestClientData();
        return new EntityRepository($this->createRestClientMock(), $this->createMetaDataLoader(), $this->createMetaDataMapper());
    }

    /**
     * @return EntityManager
     */
    public function createRepositoryManager() {
        return new EntityManager($this->createRepository());
    }

    /**
     * @return  Container|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createContainerMock() {
        /** @var \PHPUnit_Framework_MockObject_MockBuilder $containerBuilder */
        $containerBuilder = $this->getMockBuilder(Container::class);

        $containerMock = $containerBuilder->disableOriginalConstructor()->getMock();
        $containerMock->method('get')->will($this->returnCallback(function($id) {
            if ($id == 'app.entities.manager') {
                return $this->createRepositoryManager();
            }
            return null;
        }));

        return $containerMock;
    }
}