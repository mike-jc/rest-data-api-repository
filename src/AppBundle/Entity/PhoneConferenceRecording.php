<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Repository\Annotation as Api;

/**
 * Review in the meeting
 *
 * @Api\Entity(endPoint="phone_conference_recordings")
 */
class PhoneConferenceRecording extends BaseEntity implements \JsonSerializable {
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var string recorded file
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     */
    private $name;
    /**
     * @var string duration of recorded file (in seconds)
     *
     * @Api\Property()
     * @Assert\Type(type="integer")
     */
    private $duration;
    /**
     * @var string size of recorded file (in bytes)
     *
     * @Api\Property()
     * @Assert\Type(type="integer")
     */
    private $size;
    /**
     * @var Meeting meeting assigned to this review
     *
     * @Api\Property(entity="MeetingProgress", writeOnly=true)
     */
    private $meetingProgress;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return integer
     */
    public function getDuration() {
        return $this->duration;
    }

    /**
     * @param integer $duration
     */
    public function setDuration($duration) {
        $this->duration = $duration;
    }

    /**
     * @return integer
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @param integer $size
     */
    public function setSize($size) {
        $this->size = $size;
    }

    /**
     * @return Meeting
     */
    public function getMeetingProgress() {
        return $this->meetingProgress;
    }

    /**
     * @param Meeting $meetingProgress
     */
    public function setMeetingProgress($meetingProgress) {
        $this->meetingProgress = $meetingProgress;
    }

    /**
     * The set of entity data which should be serialized to JSON.
     *
     * @return array
     */
    function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'duration' => $this->getDuration(),
            'name' => $this->getName()
        ];
    }
}
