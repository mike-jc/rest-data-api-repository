<?php

namespace Tests\AppBundle\Repository\MetaData;

use AppBundle\Entity\Meeting;
use AppBundle\Helper\RepositoryTrait;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase {
    use RepositoryTrait;

    public function testLoad() {

        $loader = self::createMetaDataLoader();
        $data = $loader->load(Meeting::class);

        $this->assertEquals($data['endPoint'], 'meetings');
        $this->assertEquals($data['entity'], Meeting::class);

        $this->assertArraySubset([
            'id' => [
                'name' => 'id',
                'type' => 'integer',
            ],
            'date' => [
                'name' => 'date',
                'type' => 'datetime',
            ],
            'status' => [
                'name' => 'status',
                'type' => 'string',
            ],
            'created' => [
                'name' => 'created',
                'type' => 'datetime',
            ],
            'finished' => [
                'name' => 'finished',
                'type' => 'datetime',
            ],
            'duration' => [
                'name' => 'duration',
                'type' => 'integer'
            ],
            'wrapup' => [
                'name' => 'wrapup',
                'type' => 'string',
            ],
            'description' => [
                'name' => 'description',
                'type' => 'string',
            ]
        ], $data['extra']['properties']);

        $this->assertArraySubset([
            'name' => 'user',
            'type' => 'AppBundle\\Entity\\User',
            'class' => true,
            'endPoint' => 'users',
            'extra' => [
                'id' => 'id',
                'properties' => [
                    'id' => [
                        'name' => 'id',
                        'type' => 'integer'
                    ],
                    'name' => [
                        'name' => 'name',
                        'type' => 'string'
                    ],
                    'email' => [
                        'name' => 'email',
                        'type' => 'string'
                    ],
                    'blocked' => [
                        'name' => 'blocked',
                        'type' => 'boolean'
                    ],
                    'online' => [
                        'name' => 'online',
                        'type' => 'boolean'
                    ],
                    'created' => [
                        'name' => 'created',
                        'type' => 'datetime'
                    ],
                ]
            ]
        ], $data['extra']['properties']['user']);

        $this->assertArraySubset([
            'name' => 'groups',
            'public' => false,
            'type' => 'AppBundle\\Entity\\Group',
            'class' => true,
            'collection' => true,
            'endPoint' => 'groups',
            'extra' => [
                'id' => 'id',
                'properties' => [
                    'id' => [
                        'name' => 'id',
                        'type' => 'integer'
                    ],
                    'name' => [
                        'name' => 'name',
                        'type' => 'string'
                    ]
                ]
            ]
        ], $data['extra']['properties']['user']['extra']['properties']['groups']);

        $this->assertArraySubset([
            'name' => 'userGroupRoles',
            'public' => false,
            'type' => 'AppBundle\\Entity\\UserGroupRole',
            'class' => true,
            'collection' => true,
            'endPoint' => 'user_group_roles',
            'extra' => [
                'id' => 'id',
                'properties' => [
                    'role' => [
                        'name' => 'role',
                        'type' => 'AppBundle\\Entity\\Role',
                        'class' => true,
                        'endPoint' => 'roles',
                        'extra' => [
                            'id' => 'id',
                            'properties' => [
                                'id' => [
                                    'name' => 'id',
                                    'type' => 'integer'
                                ],
                                'name' => [
                                    'name' => 'name',
                                    'type' => 'string'
                                ]
                            ]
                        ]
                    ],
                    'group' => [
                        'name' => 'group',
                        'type' => 'AppBundle\\Entity\\Group',
                        'class' => true,
                        'endPoint' => 'groups',
                        'extra' => [
                            'id' => 'id',
                            'properties' => [
                                'id' => [
                                    'name' => 'id',
                                    'type' => 'integer'
                                ],
                                'name' => [
                                    'name' => 'name',
                                    'type' => 'string'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $data['extra']['properties']['user']['extra']['properties']['userGroupRoles']);

        $this->assertArraySubset([
            'name' => 'guest',
            'type' => 'AppBundle\\Entity\\Guest',
            'class' => true,
            'endPoint' => 'guests',
            'extra' => [
                'id' => 'id',
                'properties' => [
                    'id' => [
                        'name' => 'id',
                        'type' => 'integer'
                    ],
                    'name' => [
                        'name' => 'name',
                        'type' => 'string'
                    ],
                    'email' => [
                        'name' => 'email',
                        'type' => 'string'
                    ],
                    'accessKey' => [
                        'name' => 'accessKey',
                        'type' => 'string'
                    ]
                ]
            ]
        ], $data['extra']['properties']['guest']);

        $this->assertArraySubset([
            'name' => 'type',
            'type' => 'AppBundle\\Entity\\MeetingType',
            'class' => true,
            'endPoint' => 'meeting_types',
            'extra' => [
                'id' => 'id',
                'properties' => [
                    'id' => [
                        'name' => 'id',
                        'type' => 'integer'
                    ],
                    'name' => [
                        'name' => 'name',
                        'type' => 'string'
                    ],
                    'duration' => [
                        'name' => 'duration',
                        'type' => 'integer'
                    ]
                ]
            ]
        ], $data['extra']['properties']['type']);

        $this->assertArraySubset([
            'name' => 'suggestedDates',
            'type' => 'AppBundle\\Entity\\SuggestedDate',
            'class' => true,
            'collection' => true,
            'endPoint' => 'suggested_dates',
            'extra' => [
                'id' => 'id',
                'properties' => [
                    'id' => [
                        'name' => 'id',
                        'type' => 'integer'
                    ],
                    'date' => [
                        'name' => 'date',
                        'type' => 'datetime'
                    ]
                ]
            ]
        ], $data['extra']['properties']['suggestedDates']);

        $this->assertArraySubset([
            'name' => 'reviews',
            'type' => 'AppBundle\\Entity\\Review',
            'class' => true,
            'collection' => true,
            'endPoint' => 'reviews',
            'extra' => [
                'id' => 'id',
                'properties' => [
                    'id' => [
                        'name' => 'id',
                        'type' => 'integer'
                    ],
                    'text' => [
                        'name' => 'text',
                        'type' => 'string'
                    ],
                    'created' => [
                        'name' => 'created',
                        'type' => 'datetime'
                    ],
                    'anonymous' => [
                        'name' => 'anonymous',
                        'type' => 'boolean'
                    ]
                ]
            ]
        ], $data['extra']['properties']['reviews']);
    }
}