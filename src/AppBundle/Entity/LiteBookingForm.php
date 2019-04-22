<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * Form to book meetings
 *
 * @Api\Entity(endPoint="lite_booking_forms")
 */
class LiteBookingForm extends BaseEntity {

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
     * @Assert\NotBlank
     * @AppAssert\Name()
     */
    private $name;
    /**
     * @var string type of list of target users
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     * @Assert\Choice({"all", "groups", "users"})
     */
    private $target = 'users';
    /**
     * @var string the way the guest can choose a date and time for meeting
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     * @Assert\Choice({"date", "live_now", "any"})
     */
    private $schedulingMode = 'date';
    /**
     * @var string text of label for meeting types list
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Label()
     */
    private $meetingTypesLabel = 'Topic';
    /**
     * @var string text of label for message
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Label()
     */
    private $messageLabel = 'Message';
    /**
     * @var string text of label for guest name
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Label()
     */
    private $guestNameLabel = 'Your name';
    /**
     * @var string text of label for guest email
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Label()
     */
    private $guestEmailLabel = 'Your email';
    /**
     * @var string text of label for guest phone (for sms reminders)
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Label()
     */
    private $guestPhoneLabel = 'Your phone number';
    /**
     * @var string text of the submit button
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Label()
     */
    private $submitButtonText = 'Book';
    /**
     * @var int timeslot length when guest suggest time for meeting
     *
     * @Api\Property(type="integer")
     * @Assert\Type(type="integer")
     * @Assert\NotBlank
     * @Assert\Choice({15, 30, 45, 60})
     */
    private $timeSeparation = 30;
    /**
     * @var boolean flag that indicates if guest can suggest multiple dates for meeting
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $multipleSuggestions = false;
    /**
     * @var int how much time from now booking timeslots are blocked (in hours)
     *
     * @Api\Property(type="integer")
     * @Assert\Type(type="integer")
     */
    private $bookingBlockedBefore = 0;
    /**
     * @var boolean flag that indicates if guest can choose target user in form
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $showUserList = false;
    /**
     * @var boolean flag that indicates if form is gonna appear on webpage
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $webpage = true;
    /**
     * @var string slug that make unique alias to form page on the current instance
     *
     * @Api\Property()
     * @Assert\Regex(pattern="/^[\w\d-]+$/", message="Booking form URL should contain only digits, letters and hyphen")
     */
    private $webpageSlug;
    /**
     * @var string hash that identifies widget (since it does not have slug)
     *
     * @Api\Property()
     */
    private $widgetHash;
    /**
     * @var boolean flag that indicates if company logo appears in the form
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $showCompanyLogo = false;
    /**
     * @var boolean flag that indicates if review score appears in the form
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $showReviewScore = true;
    /**
     * @var boolean flag that indicates if message field appears in the form
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $showMessage = true;
    /**
     * @var boolean flag that indicates if form name appears in the form
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $showName = true;

    /**
     * @var boolean flag that indicates if form timezone appears in the form
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $showTimezone = true;
    /**
     * @var boolean flag that indicates if phone number should be shown for the booking form
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $showPhoneField = false;
    /**
     * @var boolean flag that indicates if phone number field is required
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $phoneIsRequired = false;

    /**
     * @var EntityCollection<CustomField> custom fields of this form
     *
     * @Api\Property(entity="CustomField")
     * @Api\Collection()
     * @Assert\Valid()
     */
    private $customFields;
    /**
     * @var MeetingType type of this form
     *
     * @Api\Property(entity="MeetingType")
     */
    private $meetingType;
    /**
     * @var EntityCollection<User> users assigned to this form
     *
     * @Api\Property(entity="User")
     * @Api\Collection()
     */
    private $users;

    public function __construct() {
        $this->customFields = new EntityCollection();
        $this->users = new EntityCollection();
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
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * @param string $target
     * @return $this
     */
    public function setTarget($target) {
        $this->target = $target;
        return $this;
    }

    /**
     * @return string
     */
    public function getSchedulingMode() {
        return $this->schedulingMode;
    }

    /**
     * @param string $schedulingMode
     * @return $this
     */
    public function setSchedulingMode($schedulingMode) {
        $this->schedulingMode = $schedulingMode;
        return $this;
    }

    /**
     * @return string
     */
    public function getMeetingTypesLabel() {
        return $this->meetingTypesLabel;
    }

    /**
     * @param string $meetingTypesLabel
     * @return $this
     */
    public function setMeetingTypesLabel($meetingTypesLabel) {
        $this->meetingTypesLabel = $meetingTypesLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageLabel() {
        return $this->messageLabel;
    }

    /**
     * @param string $messageLabel
     * @return $this
     */
    public function setMessageLabel($messageLabel) {
        $this->messageLabel = $messageLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getGuestNameLabel() {
        return $this->guestNameLabel;
    }

    /**
     * @param string $guestNameLabel
     * @return $this
     */
    public function setGuestNameLabel($guestNameLabel) {
        $this->guestNameLabel = $guestNameLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getGuestEmailLabel() {
        return $this->guestEmailLabel;
    }

    /**
     * @param string $guestEmailLabel
     * @return $this
     */
    public function setGuestEmailLabel($guestEmailLabel) {
        $this->guestEmailLabel = $guestEmailLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getGuestPhoneLabel() {
        return $this->guestPhoneLabel;
    }

    /**
     * @param string $guestPhoneLabel
     * @return $this
     */
    public function setGuestPhoneLabel($guestPhoneLabel) {
        $this->guestPhoneLabel = $guestPhoneLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubmitButtonText() {
        return $this->getButtonTextSchedule();
    }

    /**
     * @return string
     */
    public function getButtonTextSchedule() {
        return $this->submitButtonText;
    }

    /**
     * @return string
     */
    public function getButtonTextPlanning() {
        return $this->submitButtonText;
    }

    /**
     * @param string $submitButtonText
     * @return $this
     */
    public function setSubmitButtonText($submitButtonText) {
        $this->submitButtonText = $submitButtonText;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeSeparation() {
        return $this->timeSeparation;
    }

    /**
     * @param int $timeSeparation
     * @return $this
     */
    public function setTimeSeparation($timeSeparation) {
        $this->timeSeparation = $timeSeparation;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isMultipleSuggestions() {
        return $this->multipleSuggestions;
    }

    /**
     * @param boolean $multipleSuggestions
     * @return $this
     */
    public function setMultipleSuggestions($multipleSuggestions) {
        $this->multipleSuggestions = $multipleSuggestions;
        return $this;
    }

    /**
     * @return int
     */
    public function getBookingBlockedBefore() {
        return $this->bookingBlockedBefore;
    }

    /**
     * @param int $bookingBlockedBefore
     * @return $this
     */
    public function setBookingBlockedBefore($bookingBlockedBefore) {
        $this->bookingBlockedBefore = $bookingBlockedBefore;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShowUserList() {
        return $this->showUserList;
    }

    /**
     * @param bool $showUserList
     * @return $this
     */
    public function setShowUserList($showUserList) {
        $this->showUserList = $showUserList;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isWebpage() {
        return $this->webpage;
    }

    /**
     * @param boolean $webpage
     * @return $this
     */
    public function setWebpage($webpage) {
        if ($webpage) {
            if (!$this->getWebpageSlug()) {
                if ($this->isPersonal()) {
                    $defaultSlug = strtolower(preg_replace('/[^\w\d]+/', '', $this->getName()));
                } else {
                    $defaultSlug = strtolower(preg_replace(['/\s+/', '/[^\w\d\s-]+/'], ['-', ''], $this->getName()));
                }
                $this->setWebpageSlug($defaultSlug);
            }
        } else {
            $this->setWebpageSlug(null);
        }

        $this->webpage = $webpage;
        return $this;
    }

    /**
     * @return string
     */
    public function getWebpageSlug() {
        return $this->isWebpage() ? $this->webpageSlug : '';
    }

    /**
     * @param string $webpageSlug
     * @return $this
     */
    public function setWebpageSlug($webpageSlug) {
        $this->webpageSlug = $webpageSlug;
        return $this;
    }

    /**
     * @return string
     */
    public function getWidgetHash() {
        return $this->widgetHash;
    }

    /**
     * @param string $widgetHash
     * @return $this
     */
    public function setWidgetHash($widgetHash) {
        $this->widgetHash = $widgetHash;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowCompanyLogo() {
        return $this->showCompanyLogo;
    }

    /**
     * @param boolean $showCompanyLogo
     * @return $this
     */
    public function setShowCompanyLogo($showCompanyLogo) {
        $this->showCompanyLogo = $showCompanyLogo;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowReviewScore() {
        return $this->showReviewScore;
    }

    /**
     * @param boolean $showReviewScore
     * @return $this
     */
    public function setShowReviewScore($showReviewScore) {
        $this->showReviewScore = $showReviewScore;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowMessage() {
        return $this->showMessage;
    }

    /**
     * @param boolean $showMessage
     * @return $this
     */
    public function setShowMessage($showMessage) {
        $this->showMessage = $showMessage;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowName() {
        return $this->showName;
    }

    /**
     * @param boolean $showName
     * @return $this
     */
    public function setShowName($showName) {
        $this->showName = $showName;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isShowTimezone() {
        return $this->showTimezone;
    }

    /**
     * @param boolean $showTimezone
     * @return $this
     */
    public function setShowTimezone($showTimezone) {
        $this->showTimezone = $showTimezone;
        return $this;
    }

    /**
     * @return EntityCollection
     */
    public function getCustomFields() {
        return $this->customFields;
    }

    /**
     * @param EntityCollection $customFields
     * @return $this
     */
    public function setCustomFields($customFields) {
        $this->customFields = $customFields;
        return $this;
    }

    /**
     * @param CustomField $customField
     * @return $this
     */
    public function addCustomField(CustomField $customField) {
        if (!$this->customFields->contains($customField)) {
            $this->customFields->add($customField);
        }
        return $this;
    }

    /**
     * @param CustomField $customField
     * @return $this
     */
    public function removeCustomField(CustomField $customField) {
        if ($this->customFields->contains($customField)) {
            $this->customFields->removeElement($customField);
        }
        return $this;
    }

    /**
     * @return MeetingType
     */
    public function getMeetingType() {
        return $this->meetingType;
    }

    /**
     * @param MeetingType $meetingType
     * @return $this
     */
    public function setMeetingType(MeetingType $meetingType) {
        $this->meetingType = $meetingType;
        return $this;
    }

    /**
     * @return EntityCollection<User>
     */
    public function getUsers() {
        return $this->users;
    }

    /**
     * @param EntityCollection<User> $users
     * @return $this
     */
    public function setUsers($users) {
        $this->users = $users;
        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function addUser(User $user) {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function removeUser(User $user) {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }
        return $this;
    }

    /**
     * @return EntityCollection<Group>
     */
    public function getGroups() {
        return new EntityCollection();
    }

    /**
     * @return bool
     */
    public function isShowPhoneField() {
        return $this->showPhoneField;
    }

    /**
     * @param bool $showPhoneField
     */
    public function setShowPhoneField($showPhoneField) {
        $this->showPhoneField = $showPhoneField;
    }

    /**
     * @return bool
     */
    public function isPhoneRequired() {
        return $this->phoneIsRequired;
    }

    /**
     * @return bool
     */
    public function isPhoneIsRequired() {
        return $this->phoneIsRequired;
    }

    /**
     * @return bool
     */
    public function hasPhoneIsRequired() {
        return !is_null($this->phoneIsRequired);
    }

    /**
     * @return bool
     */
    public function getPhoneIsRequired() {
        return $this->phoneIsRequired;
    }

    /**
     * @param bool $phoneIsRequired
     */
    public function setPhoneIsRequired($phoneIsRequired) {
        $this->phoneIsRequired = $phoneIsRequired;
    }

    /**
     * @return bool
     */
    public function isPersonal() {
        return $this->getUsers()->count() == 1 && $this->getGroups()->count() == 0;
    }

    /**
     * @return User|null
     */
    public function getPerson() {
        return $this->isPersonal() ? $this->getUsers()->first() : null;
    }

    /**
     * @return bool
     */
    public function isCompanyForm() {
        return $this->getTarget() == 'all';
    }

    public function hasBookingMode() {
        return in_array($this->getSchedulingMode(), self::getSchedulingModesWithBooking());
    }

    public function hasLiveNowMode() {
        return in_array($this->getSchedulingMode(), self::getSchedulingModesWithLiveNow());
    }

    public function isAccessible() {
        return $this->getMeetingType() && !$this->getMeetingType()->isInaccessible();
    }

    static public function getSchedulingModesWithBooking() {
        return ['date', 'any'];
    }

    static public function getSchedulingModesWithLiveNow() {
        return ['live_now', 'any'];
    }
}
