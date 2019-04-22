<?php

namespace Tests\AppBundle\Repository;

use AppBundle\Repository\EntityRepository;
use AppBundle\Entity\Availability;
use AppBundle\Entity\BlockedTimeslot;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Helper\RepositoryTrait;
use PHPUnit\Framework\TestCase;

class EntityRepositoryTest extends TestCase  {
    use RepositoryTrait;

    /**
     * @var EntityRepository
     */
    static private $repository;

    public function testMock() {
        //to avoid warnings
    }

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        self::$repository = self::createRepository();
    }

    /**
     * @return Availability
     */
    public function testInsert() {
        $entity = (new Availability())->setBlockedTimeslots(new EntityCollection([
            (new BlockedTimeslot())
                ->setFromDate(new \DateTime("2025-07-07 10:00", new \DateTimeZone('UTC')))
                ->setToDate(new \DateTime("2025-07-07 15:00", new \DateTimeZone('UTC'))),
            (new BlockedTimeslot())
                ->setFromDate(new \DateTime("2020-07-05 13:00", new \DateTimeZone('UTC')))
                ->setToDate(new \DateTime("2020-07-05 14:30", new \DateTimeZone('UTC'))),
        ]));

        self::$repository->setEntity(Availability::class, false)->save($entity);

        $this->assertGreaterThan(0, $entity->getId());
        $this->assertGreaterThan(0, $entity->getBlockedTimeslots()->offsetGet(0)->getId());
        $this->assertGreaterThan(0, $entity->getBlockedTimeslots()->offsetGet(1)->getId());
        return $entity;
    }

    /**
     * @depends testInsert
     * @param Availability $entity
     * @return Availability
     */
    public function testFind(Availability $entity) {
        $foundEntity = self::$repository->find($entity->getId());
        $this->assertEquals($entity, $foundEntity);
        return $entity;
    }

    /**
     * @depends testFind
     * @param Availability $entity
     * @return Availability
     */
    public function testUpdate(Availability $entity) {
        $entityId = $entity->getId();

        $entity->addBlockedTimeslot(
            (new BlockedTimeslot())
                ->setFromDate(new \DateTime("2023-03-03 12:20", new \DateTimeZone('UTC')))
                ->setToDate(new \DateTime("2023-03-03 14:40", new \DateTimeZone('UTC')))
        );

        self::$repository->setEntity(Availability::class, false)->save($entity);

        $this->assertEquals($entityId, $entity->getId());
        $this->assertGreaterThan(0, $entity->getBlockedTimeslots()->offsetGet(0)->getId());
        $this->assertGreaterThan(0, $entity->getBlockedTimeslots()->offsetGet(1)->getId());
        $this->assertGreaterThan(0, $entity->getBlockedTimeslots()->offsetGet(2)->getId());
        return $entity;
    }

    /**
     * @depends testUpdate
     * @param Availability $entity
     */
    public function testDelete(Availability $entity) {
        $result = self::$repository->setEntity(Availability::class, false)->delete($entity);
        $this->assertTrue($result);
        $foundEntity = self::$repository->find($entity->getId());
        $this->assertNull($foundEntity);
    }
}