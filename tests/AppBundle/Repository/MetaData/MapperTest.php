<?php

namespace Tests\AppBundle\Repository\MetaData;

use AppBundle\Repository\MetaData\Mapper;
use AppBundle\Entity\Group;
use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Entity\UserGroupRole;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Helper\RepositoryTrait;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase {
    use RepositoryTrait;

    /**
     * @var Mapper
     */
    static public $mapper;

    /**
     * @var array
     */
    static private $metaData = [
        "entity" => "AppBundle\\Entity\\User",
        "endPoint" => "users",
        "extra" => [
            "id" => "id",
            "properties" => [
                "id" => [
                    "name" => "id",
                    "public" => false,
                    "type" => "integer"
                ],
                "name" => [
                    "name" => "name",
                    "public" => false,
                    "type" => "string"
                ],
                "email" => [
                    "name" => "email",
                    "public" => false,
                    "type" => "string"
                ],
                "groups" => [
                    "name" => "groups",
                    "public" => false,
                    "type" => "AppBundle\\Entity\\Group",
                    "class" => true,
                    "endPoint" => "groups",
                    "extra" => [
                        "id" => "id",
                        "properties" => [
                            "id" => [
                                "name" => "id",
                                "public" => false,
                                "type" => "integer"
                            ],
                            "name" => [
                                "name" => "name",
                                "public" => false,
                                "type" => "string"
                            ],
                            "users" => [
                                "name" => "users",
                                "public" => false,
                                "type" => "AppBundle\\Entity\\User",
                                "class" => true,
                                "isTracked" => true,
                                "endPoint" => "users",
                                "collection" => true
                            ]
                        ]
                    ],
                    "collection" => true
                ],
                "userGroupRoles" => [
                    "name" => "userGroupRoles",
                    "public" => false,
                    "type" => "AppBundle\\Entity\\UserGroupRole",
                    "class" => true,
                    "endPoint" => "user_group_roles",
                    "extra" => [
                        "id" => "id",
                        "properties" => [
                            "id" => [
                                "name" => "id",
                                "public" => false,
                                "type" => "integer"
                            ],
                            "role" => [
                                "name" => "role",
                                "public" => false,
                                "type" => "AppBundle\\Entity\\Role",
                                "class" => true,
                                "endPoint" => "roles",
                                "extra" => [
                                    "id" => "id",
                                    "properties" => [
                                        "id" => [
                                            "name" => "id",
                                            "public" => false,
                                            "type" => "integer"
                                        ],
                                        "name" => [
                                            "name" => "name",
                                            "public" => false,
                                            "type" => "string"
                                        ]
                                    ]
                                ]
                            ],
                            "group" => [
                                "name" => "group",
                                "public" => false,
                                "type" => "AppBundle\\Entity\\Group",
                                "class" => true,
                                "endPoint" => "groups",
                                "extra" => [
                                    "id" => "id",
                                    "properties" => [
                                        "id" => [
                                            "name" => "id",
                                            "public" => false,
                                            "type" => "integer"
                                        ],
                                        "name" => [
                                            "name" => "name",
                                            "public" => false,
                                            "type" => "string"
                                        ],
                                        "users" => [
                                            "name" => "users",
                                            "public" => false,
                                            "type" => "AppBundle\\Entity\\User",
                                            "class" => true,
                                            "isTracked" => true,
                                            "endPoint" => "users",
                                            "collection" => true,
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "collection" => true
                ]
            ]
        ]
    ];

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::$mapper = self::createMetaDataMapper();
    }

    /**
     * @return mixed
     */
    public function testMapDataToEntity() {

        $entity = new User();
        $data = [
            "id" => 1,
            "name" => "User1",
            "email" => "user1@gmail.com",
            "groups" => [
                [
                    "id" => 1,
                    "name" => "Enterprises managers"
                ]
            ],
            "userGroupRoles" => [
                [
                    "id" => 1,
                    "role" => [
                        "id" => 1,
                        "name" => "Operator"
                    ],
                    "group" => [
                        "id" => 1,
                        "name" => "Enterprises managers"
                    ]
                ]
            ]
        ];
        self::$mapper->mapDataToEntity($data, $entity, self::$metaData);

        $expectedGroup = (new Group())
            ->setId(1)
            ->setName("Enterprises managers");
        $expectedGroups = new EntityCollection([$expectedGroup]); // reference to the same object
        $expectedGroups->setInitialized(true);

        $expectedUserGroupRole = (new UserGroupRole())
            ->setId(1)
            ->setRole((new Role())->setId(1)->setName("Operator"))
            ->setGroup($expectedGroup); // reference to the same object
        $expectedUserGroupRoles = new EntityCollection([$expectedUserGroupRole]);
        $expectedUserGroupRoles->setInitialized(true);

        $expectedObject = (new User())
            ->setId(1)
            ->setName("User1")
            ->setEmail("user1@gmail.com")
            ->setGroups($expectedGroups)
            ->setUserGroupRoles($expectedUserGroupRoles);
        $this->assertEquals($expectedObject, $entity);

        return $entity;
    }

    /**
     * @depends testMapDataToEntity
     * @param mixed $entity
     */
    public function testMapEntityToObject($entity) {
        /** @var User $entity */
        $entity->setName("New user1");

        $object = self::$mapper->mapEntityToObject($entity, self::$metaData);

        // Object must consist of only changed properties
        $this->assertJsonStringEqualsJsonString("{
            \"id\": 1,
            \"name\": \"New user1\",
            \"endPoint\": \"users\"
        }", json_encode($object));
    }
}