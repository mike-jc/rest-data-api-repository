<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * Default Timeslot that is blocked in availability of the user or group
 *
 * Object is READ-ONLY. Plz use it only for reading (default collection).
 *
 * @Api\Entity(endPoint="timeslots/blocked-default")
 */
class DefaultBlockedTimeslot extends BlockedTimeslot
{
    /**
     * @var string
     *
     * @Api\Property()
     * @Api\Property(type="string")
     */
    private $id;

    /**
     * Represente object as BlockedTimeslot model.
     *
     * @param bool $initial
     * @param \DateTimeZone $timezone
     * @return BlockedTimeslot
     */
    public function asBlockedTimeslot($initial = false, $timezone = null)
    {
        $object = new BlockedTimeslot();

        if ($initial) {
            $object->setId($this->getId());
        }
        $object->setFromDate($this->getFromDate());
        $object->setToDate($this->getToDate());
        $object->setRepeat($this->getRepeat());
        $object->setInterval($this->getInterval());
        $object->setByDay(is_array($this->getByDay()) ? $this->getByDay() : null);
        $object->setCount($this->getCount());
        if ($this->getUntil() instanceof \DateTime) {
            $object->setUntil($this->getUntil());
        }
        $object->setExternalId($this->getExternalId());
        $object->setExternalRecurringId($this->getExternalRecurringId());
        $object->setTitle($this->getTitle());
        $object->setLowerLevel($this->isLowerLevel());
        $object->setAvailability($this->getAvailability());

        if ($timezone) {
            /**
             * If there is timezone for user, method injects it in DateTime object without converting time of object.
             */
            $object->setFromDate(new \DateTime($object->getFromDate()->format('Y-m-d H:i:s'), $timezone));
            $object->setToDate(new \DateTime($object->getToDate()->format('Y-m-d H:i:s'), $timezone));
        }

        return $object;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }
}
