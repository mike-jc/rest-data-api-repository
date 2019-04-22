<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Helper\DatetimeTrait;
use AppBundle\Repository\Annotation as Api;

/**
 * Availability for user or group of users
 *
 * @Api\Entity(endPoint="availabilities")
 */
class Availability extends BaseEntity {
    use DatetimeTrait;

    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;

    /**
     * @var EntityCollection<BlockedTimeslot> blocked timeslots for this availability
     *
     * @Api\Property(entity="BlockedTimeslot")
     * @Api\Collection()
     */
    private $blockedTimeslots;
    /**
     * @var User user assigned to this availability
     *
     * @Api\Property(entity="User", writeOnly=true)
     */
    private $user;
    /**
     * @var Group group assigned to this availability
     *
     * @Api\Property(entity="Group", writeOnly=true)
     */
    private $group;

    public function __construct() {
        $this->blockedTimeslots = new EntityCollection();
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return EntityCollection
     */
    public function getBlockedTimeslots() {
        return $this->blockedTimeslots;
    }

    /**
     * @param EntityCollection $blockedTimeslots
     * @return $this
     */
    public function setBlockedTimeslots($blockedTimeslots) {
        $this->blockedTimeslots = $blockedTimeslots;
        return $this;
    }

    /**
     * @param BlockedTimeslot $blockedTimeslot
     * @return $this
     */
    public function addBlockedTimeslot(BlockedTimeslot $blockedTimeslot) {
        if (!$this->blockedTimeslots->contains($blockedTimeslot)) {
            $this->blockedTimeslots->add($blockedTimeslot);
        }
        return $this;
    }

    /**
     * @param BlockedTimeslot $blockedTimeslot
     * @return $this
     */
    public function removeBlockedTimeslot(BlockedTimeslot $blockedTimeslot) {
        if ($this->blockedTimeslots->contains($blockedTimeslot)) {
            $this->blockedTimeslots->removeElement($blockedTimeslot);
        }
        return $this;
    }

    /**
     * @param string $externalId
     * @param EntityCollection $blockedTimeSlots
     * @return $this
     */
    public function updateBlockedTimeslotsByExternalId($externalId, EntityCollection $blockedTimeSlots) {
        $toRemove = array_filter($this->getBlockedTimeslots()->toArray(), function (BlockedTimeslot $t) use ($externalId) {
            return $t->getExternalId() == $externalId;
        });
        /** @var BlockedTimeslot $t */
        foreach ($toRemove as $t) {
            $this->removeBlockedTimeslot($t);
        }
        foreach ($blockedTimeSlots as $t) {
            $t->setExternalId($externalId);
            $this->addBlockedTimeslot($t);
        }
        return $this;
    }

    /**
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user = null) {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Group
     */
    public function getGroup() {
        return $this->group;
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function setGroup(Group $group = null) {
        $this->group = $group;
        return $this;
    }

    /**
     * Get all changed instances of recurrent blocking events:
     * e.g. we have recurrent event that occurs every Monday;
     * then we decided to change hours for one particular Monday on July, 6th.
     * This instance becomes a separate event with recurring id equaling to the original event.
     * In this case on July, 6th we should show only changed instance and do not show original event.
     *
     * @param string|\DateTimeZone|null $timezone
     * @return array
     */
    public function getDerivativeKeys($timezone = null) {
        $slots = [];
        $tz = $timezone ? $this->getDateTimeZone($timezone) : null;

        /** @var BlockedTimeslot $slot */
        foreach ($this->getBlockedTimeslots() as $slot) {
            if ($slot->getExternalRecurringId()) {
                $slots[$slot->getDerivativeKey($tz)] = true;
            }
        }
        return $slots;
    }
}
