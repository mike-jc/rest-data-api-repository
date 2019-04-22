<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Repository\Annotation as Api;

/**
 * A meeting request.
 *
 * @Api\Entity(endPoint="meeting_requests")
 */
class MeetingRequest extends BaseEntity {

    static public $notArchivedStatuses = ['pending', 'missed'];

    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id
     */
    private $id;
    /**
     * @var string Request status
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\Choice({"pending", "canceled", "missed", "accepted", "archived"})
     */
    private $status = 'pending';
    /**
     * @var \DateTime Date when request has been created
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $created;
    /**
     * @var string Request description
     * Warning: use filtered_symbols option if open this field for user via TextareaType to prevent XSS attacks
     *
     * @Api\Property()
     */
    private $description;

    /**
     * @var Guest guest assigned to this request (author of request)
     *
     * @Api\Property(entity="Guest")
     * @Assert\Valid()
     */
    private $guest;
    /**
     * @var MeetingType type of this request
     *
     * @Api\Property(entity="MeetingType")
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $type;
    /**
     * @var BookingForm form through wich this request was created
     *
     * @Api\Property(entity="BookingForm")
     */
    private $form;
    /**
     * @var User target user who only can accept this request
     *
     * @Api\Property(entity="User")
     */
    private $targetUser;
    /**
     * @var Group target group which users only can accept this request
     *
     * @Api\Property(entity="Group")
     */
    private $targetGroup;
    /**
     * @var Meeting meeting that is the answer to this request
     *
     * @Api\Property(entity="Meeting")
     */
    protected $meeting;
    /**
     * @var EntityCollection<MeetingCustomValue> values of custom fields of booking form
     *
     * @Api\Property(entity="MeetingCustomValue")
     * @Api\Collection()
     */
    private $customValues;

    public function __construct() {
        $this->customValues = new EntityCollection();
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets id.
     *
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
    public function getStatus() {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     * @return $this
     */
    public function setCreated($created) {
        $this->created = $created;
        return $this;
    }

    /**
     * @return int
     */
    public function getPendingMinutes() {
        return floor((time() - $this->getCreated()->getTimestamp()) / 60);
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @return BookingForm
     */
    public function getForm() {
        return $this->form;
    }

    /**
     * @param BookingForm $form
     * @return $this
     */
    public function setForm(BookingForm $form = null) {
        $this->form = $form;
        return $this;
    }

    /**
     * @return User
     */
    public function getTargetUser() {
        return $this->targetUser;
    }

    /**
     * @param User $targetUser
     * @return $this
     */
    public function setTargetUser(User $targetUser = null) {
        $this->targetUser = $targetUser;
        return $this;
    }

    /**
     * @return Group
     */
    public function getTargetGroup() {
        return $this->targetGroup;
    }

    /**
     * @param Group $targetGroup
     * @return $this
     */
    public function setTargetGroup(Group $targetGroup = null) {
        $this->targetGroup = $targetGroup;
        return $this;
    }

    /**
     * @return Guest
     */
    public function getGuest() {
        return $this->guest;
    }

    /**
     * @param Guest $guest
     * @return $this
     */
    public function setGuest(Guest $guest = null) {
        $this->guest = $guest;
        return $this;
    }

    /**
     * @return MeetingType
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param MeetingType $type
     * @return $this
     */
    public function setType(MeetingType $type = null) {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Meeting
     */
    public function getMeeting() {
        return $this->meeting;
    }

    /**
     * @param Meeting|null $meeting
     * @return $this
     */
    public function setMeeting(Meeting $meeting = null) {
        $this->meeting = $meeting;
        return $this;
    }

    /**
     * @return EntityCollection
     */
    public function getCustomValues() {
        return $this->customValues;
    }

    /**
     * @param EntityCollection $customValues
     * @return $this
     */
    public function setCustomValues($customValues) {
        $this->customValues = $customValues;
        return $this;
    }

    /**
     * @param MeetingCustomValue $customValue
     * @return $this
     */
    public function addCustomValue(MeetingCustomValue $customValue) {
        if (!$this->customValues->contains($customValue)) {
            $this->customValues->add($customValue);
            $customValue->setMeetingRequest($this);
        }
        return $this;
    }

    /**
     * @param MeetingCustomValue $customValue
     * @return $this
     */
    public function removeCustomValue(MeetingCustomValue $customValue) {
        if ($this->customValues->contains($customValue)) {
            $this->customValues->removeElement($customValue);
            $customValue->setMeetingRequest(null);
        }
        return $this;
    }
}
