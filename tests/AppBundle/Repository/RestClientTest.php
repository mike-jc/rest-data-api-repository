<?php

namespace Tests\AppBundle\Repository;

use AppBundle\Helper\RepositoryTrait;
use AppBundle\Repository\RestClient;
use PHPUnit\Framework\TestCase;

class RestClientTest extends TestCase  {
    use RepositoryTrait;

    /**
     * @var RestClient
     */
    static private $client;

    public function testMock() {
        //to avoid warnings
    }

    /**
     * @var array
     */
    static private $data = [
        "blockedTimeslots" => [
            [
                "fromDate" => "2025-07-07T10:00:00+00:00",
                "toDate" => "2025-07-07T15:00:00+00:00",
                "repeat" => null
            ],
            [
                "fromDate" => "2020-07-05T13:00:00+00:00",
                "toDate" => "2020-07-05T14:30:00+00:00",
                "repeat" => null
            ]
        ]
    ];

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::$client = self::createRestClient();
    }

    /**
     * @return mixed
     */
    public function testPost() {
        $timeSlot1 = new \stdClass();
        $timeSlot1->fromDate = "2025-07-07T10:00:00+00:00";
        $timeSlot1->toDate = "2025-07-07T15:00:00+00:00";

        $timeSlot2 = new \stdClass();
        $timeSlot2->fromDate = "2020-07-05T13:00:00+00:00";
        $timeSlot2->toDate = "2020-07-05T14:30:00+00:00";

        $object = new \stdClass();
        $object->blockedTimeslots = [$timeSlot1, $timeSlot2];

        $data = self::$client->post('availabilities', $object);
        $object->id = $data['id'];

        $this->assertArraySubset(self::$data, $data);
        return $object;
    }

    /**
     * @depends testPost
     * @param mixed $object
     * @return mixed
     */
    public function testGet($object) {
        $data = self::$client->getById('availabilities', $object->id);
        $this->assertArraySubset(self::$data, $data);
        return $object;
    }

    /**
     * @depends testGet
     * @param mixed $object
     * @return array
     */
    public function testPut($object) {
        $timeSlot3 = new \stdClass();
        $timeSlot3->fromDate = "2023-03-03T12:20:00+00:00";
        $timeSlot3->toDate = "2023-03-03T14:40:00+00:00";

        $object->blockedTimeslots[] = $timeSlot3;

        $newData = self::$data['blockedTimeslots'];
        $newData[] = [
            "fromDate" => "2023-03-03T12:20:00+00:00",
            "toDate" => "2023-03-03T14:40:00+00:00",
            "repeat" => null
        ];

        $data = self::$client->put('availabilities', $object->id, $object);
        $this->assertArraySubset($newData, array_values($data['blockedTimeslots']));
        return $object;
    }

    /**
     * @depends testPut
     * @param mixed $object
     */
    public function testDelete($object) {
        $data = self::$client->delete('availabilities', $object->id);
        $this->assertTrue($data);

        try {
            $data = self::$client->getById('availabilities', $object->id);
        } catch (\Exception $e) {
            $data = json_decode($e->getMessage(), true);
        }
        $this->assertEquals('Not Found', $data['hydra:description']);
    }
}