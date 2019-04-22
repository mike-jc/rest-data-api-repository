<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * Form to book meetings
 *
 * @Api\Entity(endPoint="booking_forms")
 * @AppAssert\UniqueProperties(
 *     properties={"webpageSlug"},
 *     message="Booking form with this webpage URL already exists",
 *     groups={"Creating", "Builder"}
 * )
 * @AppAssert\BookingFormConstraint()
 */
class BookingForm extends BaseEntity {

    const DEFAULT_TARGET = 'users';
    const DEFAULT_SCHEDULING_MODE = 'date';
    const DEFAULT_BUTTON_TEXT_SCHEDULE = 'Schedule meeting';
    const DEFAULT_BUTTON_TEXT_PLANNING = 'Suggest times';
    const DEFAULT_MEETING_TYPES_LABEL = 'Topic';
    const DEFAULT_MESSAGE_LABEL = 'Message';
    const DEFAULT_GUEST_NAME_LABEL = 'Your name';
    const DEFAULT_GUEST_EMAIL_LABEL = 'Your email';
    const DEFAULT_GUEST_PHONE_LABEL = 'Your phone number';
    const DEFAULT_NAME = 'Schedule meeting';
    const DEFAULT_TIME_SEPARATION = 30;

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
    private $target = self::DEFAULT_TARGET;
    /**
     * @var string the way the guest can choose a date and time for meeting
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     * @Assert\Choice({"date", "live_now", "any"})
     */
    private $schedulingMode = self::DEFAULT_SCHEDULING_MODE;
    /**
     * @var string text of label for meeting types list
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Label()
     */
    private $meetingTypesLabel = self::DEFAULT_MEETING_TYPES_LABEL;
    /**
     * @var string text of label for message
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Label()
     */
    private $messageLabel = self::DEFAULT_MESSAGE_LABEL;
    /**
     * @var string text of label for guest name
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Label()
     */
    private $guestNameLabel = self::DEFAULT_GUEST_NAME_LABEL;
    /**
     * @var string text of label for guest email
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Label()
     */
    private $guestEmailLabel = self::DEFAULT_GUEST_EMAIL_LABEL;
    /**
     * @var string text of label for guest phone (for sms reminders)
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Label()
     */
    private $guestPhoneLabel = self::DEFAULT_GUEST_PHONE_LABEL;
    /**
     * @var string text of the submit button
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Label()
     */
    private $submitButtonText = self::DEFAULT_BUTTON_TEXT_SCHEDULE;
    /**
     * @var int timeslot length when guest suggest time for meeting
     *
     * @Api\Property(type="integer")
     * @Assert\Type(type="integer")
     * @Assert\NotBlank
     * @Assert\Choice({15, 30, 45, 60})
     */
    private $timeSeparation = self::DEFAULT_TIME_SEPARATION;
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
     * @var EntityCollection<CustomField> custom fields of this form
     *
     * @Api\Property(entity="CustomField")
     * @Api\Collection()
     * @Assert\Valid()
     */
    private $customFields;
    /**
     * @var User manager of this form
     *
     * @Api\Property(entity="User")
     */
    private $manager;
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
    /**
     * @var EntityCollection<Group> groups assigned to this form
     *
     * @Api\Property(entity="Group")
     * @Api\Collection()
     */
    private $groups;
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

    public function __construct() {
        $this->customFields = new EntityCollection();
        $this->users = new EntityCollection();
        $this->groups = new EntityCollection();
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
        return $this->meetingTypesLabel ?: self::DEFAULT_MEETING_TYPES_LABEL;
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
        return $this->messageLabel ?: self::DEFAULT_MESSAGE_LABEL;
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
        return $this->guestNameLabel ?: self::DEFAULT_GUEST_NAME_LABEL;
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
        return $this->guestEmailLabel ?: self::DEFAULT_GUEST_EMAIL_LABEL;
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
        return $this->guestPhoneLabel ?: self::DEFAULT_GUEST_PHONE_LABEL;
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
        return $this->submitButtonText ?: self::DEFAULT_BUTTON_TEXT_SCHEDULE;
    }

    /**
     * @return string
     */
    public function getButtonTextPlanning() {
        return $this->submitButtonText ?: self::DEFAULT_BUTTON_TEXT_PLANNING;
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
     * @return User
     */
    public function getManager() {
        return $this->manager;
    }

    /**
     * @param User $manager
     * @return $this
     */
    public function setManager(User $manager = null) {
        $this->manager = $manager;
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
        return $this->groups->filter(function (Group $group) {
            return !$group->isDeleted();
        });
    }

    /**
     * @param EntityCollection<Group> $groups
     * @return $this
     */
    public function setGroups($groups) {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function addGroup(Group $group) {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }
        return $this;
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function removeGroup(Group $group) {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }
        return $this;
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

    /**
     * @return array
     */
    static public function getTimeSeparations() {
        static $separations;

        if (is_null($separations)) {
            $separations = [];
            $orig = [
                '15 min' => 15,
                '30 min' => 30,
                '45 min' => 45,
                '60 min' => 60,
            ];
            foreach ($orig as $title => $value) {
                if ($value == self::DEFAULT_TIME_SEPARATION) {
                    $title .= ' (Default)';
                }
                $separations[$title] = $value;
            }
        }
        return $separations;
    }

    /**
     * @return array
     */
    static public function getBookingBlockedOptions() {
        static $bookingBlockedOptions = [
            'Disabled' => 0,
            '1 day' => 24,
            '2 days' => 48,
            '3 days' => 72,
            '4 days' => 96,
            '5 days' => 120,
            '6 days' => 144,
            '7 days' => 168,
        ];
        return $bookingBlockedOptions;
    }

    static public function getSchedulingModesWithBooking() {
        return ['date', 'any'];
    }

    static public function getSchedulingModesWithLiveNow() {
        return ['live_now', 'any'];
    }

    /**
     * Find all forms that a given user assigned to them (using target field)
     *
     * @param User $user
     * @param string $relationName
     * @return array
     */
    static public function getFilterForUser(User $user, $relationName = null) {
        $tableAlias = $relationName ? "{$relationName}." : '';

        // target = 'all' OR (target = 'users' AND userId in (<users>))
        // OR (target = 'groups' AND userGroups IN (<groups>))
        //$filter = [
        //    ["{$tableAlias}target" => 'all'],
        //    ['AND' => [
        //        "{$tableAlias}target" => 'users',
        //        "{$tableAlias}users" => $user->getId(),
        //    ]]
        //];
        //if ($user->getGroups()->count() > 0) {
        //    $filter[] = ['AND' => [
        //        "{$tableAlias}target" => 'groups',
        //        "{$tableAlias}groups" => $user->getGroups()->getKeys(),
        //    ]];
        //}

        // userId in (<users>) OR userGroups IN (<groups>)
        $filter = [
            "{$tableAlias}users" => $user->getId(),
        ];
        if ($user->getGroups()->count() > 0) {
            $filter["{$tableAlias}groups"] = $user->getGroups()->getKeys();
        }
        return ['OR' => $filter];
    }
}
