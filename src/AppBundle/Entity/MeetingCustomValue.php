<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;

/**
 * Entity containing values of custom fields of booking forms.
 *
 * @Api\Entity(endPoint="meeting_custom_values")
 */
class MeetingCustomValue extends BaseEntity {
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var string
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     */
    private $label;
    /**
     * @var string
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     */
    private $value;

    /**
     * @var CustomField field that has this value
     *
     * @Api\Property(entity="CustomField")
     */
    private $field;
    /**
     * @var MeetingRequest meeting request contained this custom field's value
     *
     * @Api\Property(entity="MeetingRequest")
     */
    private $meetingRequest;
    /**
     * @var Meeting meeting contained this custom field's value
     *
     * @Api\Property(entity="Meeting")
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
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label) {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    /**
     * @return CustomField
     */
    public function getField() {
        return $this->field;
    }

    /**
     * @param CustomField $field
     * @return $this
     */
    public function setField($field) {
        $this->field = $field;
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

    /**
     * @return MeetingRequest
     */
    public function getMeetingRequest() {
        return $this->meetingRequest;
    }

    /**
     * @param MeetingRequest $meetingRequest
     * @return $this
     */
    public function setMeetingRequest(MeetingRequest $meetingRequest = null) {
        $this->meetingRequest = $meetingRequest;
        return $this;
    }
}
