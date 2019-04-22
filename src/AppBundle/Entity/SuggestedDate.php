<?php

namespace AppBundle\Entity;

use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;

/**
 * Date suggested for the meeting
 *
 * @Api\Entity(endPoint="suggested_dates")
 */
class SuggestedDate extends BaseEntity implements JsonSerializable {
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var \DateTime date suggested for the meeting
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $date;

    /**
     * @var Meeting meeting assigned to this date
     *
     * @Api\Property(entity="Meeting", writeOnly=true)
     */
    private $meeting;

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
     * @return \DateTime
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return $this
     */
    public function setDate($date) {
        $this->date = $date;
        return $this;
    }

    /**
     * @param int $t
     * @param \DateTimeZone $tz
     * @return $this
     */
    public function setTimestamp($t, \DateTimeZone $tz = null) {
        $this->date = (new \DateTime())->setTimezone($tz ?: new \DateTimeZone('UTC'))->setTimestamp($t);
        return $this;
    }

    /**
     * @return Meeting
     */
    public function getMeeting() {
        return $this->meeting;
    }

    /**
     * @param Meeting $meeting
     * @return $this
     */
    public function setMeeting(Meeting $meeting = null) {
        $this->meeting = $meeting;
        return $this;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'date' => $this->getDate()->format('c')
        ];
    }
}
