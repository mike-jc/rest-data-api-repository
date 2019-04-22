<?php

namespace AppBundle\Entity;

use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * A meeting.
 *
 * @Api\Entity(endPoint="meetings")
 * @AppAssert\MeetingConstraint(groups={"Default", "Meeting", "TimezoneRequired"})
 */
class Meeting extends BaseEntity implements JsonSerializable
{
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_LIVE      = 'live';

    /**
     * @var int
     * 
     * @Api\Property(type="integer")
     * @Api\Id
     */
    private $id;
    /**
     * @var string Exact date and time or time slots choosing by datetime picker
     * @Assert\NotBlank()
     * @Assert\Choice({"exact", "slots"})
     */
    private $when = 'slots';
    /**
     * @var string Mode of datetimepicker:
     *      - pick only one time slot
     *      - propose some time slots (if one slot the meeting is scheduled otherwise is planning)
     *      - propose at least 3 time slots (so it has low limit in this case)
     * @Assert\NotBlank()
     * @Assert\Choice({"date", "propose", "propose_at_least"})
     */
    private $mode = 'propose';
    /**
     * @var \DateTime Meeting date/time.
     * 
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $date;
    /**
     * @var string Meeting status
     * 
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\Choice({"planning_guest", "planning_user", "scheduled", "missed_guest", "missed_user",
     *     "missed_both", "canceled_guest", "canceled_user", "live", "failed_guest", "failed_user",
     *     "failed_both", "completed"})
     */
    private $status;
    /**
     * @var \DateTime date when meeting has been created
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $created;
    /**
     * @var \DateTime date when meeting has been finished
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $finished;
    /**
     * @var int meeting duration
     *
     * @Api\Property(type="integer")
     * @Assert\Type(type="integer")
     * @Assert\GreaterThan(0)
     */
    private $duration;
    /**
     * @var string meeting wrapup
     * Warning: use filtered_symbols option if open this field for user via TextareaType to prevent XSS attacks
     *
     * @Assert\Length(max = 2000)
     *
     * @Api\Property()
     */
    private $wrapup;
    /**
     * @var string meeting description
     * Warning: use filtered_symbols option if open this field for user via TextareaType to prevent XSS attacks
     *
     * @Assert\Length(max = 2000)
     *
     * @Api\Property()
     */
    private $description;
    /**
     * @var bool if true means guest created this meeting
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $isCreatedByGuest;
    /**
     * @var string pin to join phone conference
     *
     * @Api\Property()
     */
    private $conferencePin;
    /**
     * @var string reason of cancellation
     *
     * @Api\Property()
     */
    private $cancellationReason;
    /**
     * @var string
     *
     * @Api\Property()
     */
    private $reschedulingReason;

    /**
     * @var User user assigned to this meeting
     *
     * @Api\Property(entity="User")
     * @Assert\Valid()
     */
    private $user;
    /**
     * @var Guest guest assigned to this meeting
     *
     * @Api\Property(entity="Guest")
     * @Assert\Valid()
     */
    private $guest;
    /**
     * @var User user who scheduled this meeting
     *
     * @Api\Property(entity="User")
     * @Assert\Valid()
     */
    private $scheduler;
    /**
     * @var MeetingType type of this meeting
     *
     * @Api\Property(entity="MeetingType")
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $type;
    /**
     * @var UserQualityReview
     *
     * @Api\Property(entity="UserQualityReview")
     */
    private $userQualityReview;
    /**
     * @var MeetingProgress
     *
     * @Api\Property(entity="MeetingProgress")
     */
    private $progress;
    /**
     * @var EntityCollection<SuggestedDate> dates suggested for this meeting
     *
     * @Api\Property(entity="SuggestedDate")
     * @Api\Collection()
     */
    private $suggestedDates;
    /**
     * @var EntityCollection<Review> reviews of this meeting
     *
     * @Api\Property(entity="Review")
     * @Api\Collection()
     */
    private $reviews;
    /**
     * @var EntityCollection<MeetingCustomValue> values of custom fields of booking form
     *
     * @Api\Property(entity="MeetingCustomValue")
     * @Api\Collection()
     */
    private $customValues;

    /**
     * this field just for validation, without mapping
     *
     * @var null|integer
     */
    private $rescheduledMeetingId;
    /**
     * this field for performance, without mapping
     *
     * @var null|string
     */
    private $statusForOrder;

    public static $statuses = [
        "Live"      => ["live"],
        "Scheduled" => ["scheduled"],
        "Planning"  => ["planning_guest", "planning_user"],
        "Expired"   => ["expired"], /* virtual status */
        "Completed" => ["completed"],
        "Missed"    => ["missed_guest", "missed_user", "missed_both"],
        "Failed"    => ["failed_guest", "failed_user", "failed_both"],
        "Canceled"  => ["canceled_guest", "canceled_user"],
    ];

    public static $statusOrder = [
        "live"              => 1,
        //"starting_soon"     => 2, /* virtual status */
        "scheduled"         => 3,
        "planning_guest"    => 4,
        "planning_user"     => 5,
        "expired"           => 6, /* virtual status */
        "completed"         => 7,
        "missed_guest"      => 8,
        "missed_user"       => 9,
        "missed_both"       => 10,
        "failed_guest"      => 11,
        "failed_user"       => 12,
        "failed_both"       => 13,
        "canceled_guest"    => 14,
        "canceled_user"     => 15,
    ];

    public function __construct() {
        $this->suggestedDates = new EntityCollection();
        $this->reviews = new EntityCollection();
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
    public function getWhen() {
        return $this->when;
    }

    /**
     * @param string $when
     * @return $this
     */
    public function setWhen($when) {
        $this->when = $when;
        return $this;
    }

    /**
     * @return string
     */
    public function getMode() {
        return $this->mode;
    }

    /**
     * @param string $mode
     * @return $this
     */
    public function setMode($mode) {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate() {
        $date = clone $this->getDate();
        $date->add(new \DateInterval('PT'. $this->getDuration() .'M'));
        return $date;
    }

    /**
     * @param \DateTime|null $date
     * @return $this
     */
    public function setDate($date) {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getStatusForOrder() {
        if (null !== $this->statusForOrder) {
            return $this->statusForOrder;
        }

        //if (self::isStartingSoon($this)) {
        //    return $this->statusForOrder = 'starting_soon';
        //}

        if (self::isExpired($this)) {
            return $this->statusForOrder = 'expired';
        }

        return $this->statusForOrder = $this->status;
    }

    /**
     * @return int
     */
    public function getStatusWeight() {
        $status = $this->getStatusForOrder();
        if (array_key_exists($status, self::$statusOrder)) {
            return self::$statusOrder[$status];
        }

        return 100;
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
     * @return \DateTime
     */
    public function getFinished() {
        return $this->finished;
    }

    /**
     * @param \DateTime $finished
     * @return $this
     */
    public function setFinished($finished) {
        $this->finished = $finished;
        return $this;
    }

    /**
     * @return int
     */
    public function getDuration() {
        return $this->duration;
    }

    /**
     * @param int $duration
     * @return $this
     */
    public function setDuration($duration) {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return string
     */
    public function getWrapup() {
        return $this->wrapup;
    }

    /**
     * @param string $wrapup
     * @return $this
     */
    public function setWrapup($wrapup) {
        $this->wrapup = $wrapup;
        return $this;
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
        if ($this->guest) {
            $this->guest->setMeeting(null);
        }
        $this->guest = $guest;
        if ($guest && (!$guest->getMeeting() || $guest->getMeeting()->getId() != $this->getId())) {
            $guest->setMeeting($this);
        }
        return $this;
    }

    /**
     * @return User
     */
    public function getScheduler() {
        return $this->scheduler;
    }

    /**
     * @param User $scheduler
     * @return $this
     */
    public function setScheduler(User $scheduler = null) {
        $this->scheduler = $scheduler;
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
        if (!is_null($type)) {
            $this->setDuration($type->getDuration());
        }
        return $this;
    }

    /**
     * @return EntityCollection
     */
    public function getSuggestedDates() {
        return $this->suggestedDates;
    }

    /**
     * @param EntityCollection $suggestedDates
     * @return $this
     */
    public function setSuggestedDates($suggestedDates) {
        $this->suggestedDates = $suggestedDates;
        return $this;
    }

    /**
     * @return $this
     */
    public function clearSuggestedDates() {
        $this->suggestedDates = new EntityCollection();
        return $this;
    }

    /**
     * @param SuggestedDate $suggestedDate
     * @return $this
     */
    public function addSuggestedDate(SuggestedDate $suggestedDate) {
        if (!$this->suggestedDates->contains($suggestedDate)) {
            $this->suggestedDates->add($suggestedDate);
            $suggestedDate->setMeeting($this);
        }
        return $this;
    }

    /**
     * @param SuggestedDate $suggestedDate
     * @return $this
     */
    public function removeSuggestedDate(SuggestedDate $suggestedDate) {
        if ($this->suggestedDates->contains($suggestedDate)) {
            $this->suggestedDates->removeElement($suggestedDate);
            $suggestedDate->setMeeting(null);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getSuggestedTimestamps() {
        $timestamps = [];

        /** @var SuggestedDate $date */
        foreach ($this->getSuggestedDates() as $date) {
            $timestamps[] = $date->getDate()->getTimestamp();
        }
        return $timestamps;
    }

    /**
     * @param \DateTimeZone $tz
     * @return $this
     */
    public function setSuggestedDatesTimezone(\DateTimeZone $tz) {
        /** @var SuggestedDate $date */
        foreach ($this->getSuggestedDates() as $date) {
            $date->getDate()->setTimezone($tz);
        }
        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getPeriodFromSuggestedDates() {
        $dates = [];

        /** @var SuggestedDate $date */
        foreach ($this->getSuggestedDates() as $date) {
            $d = clone $date->getDate();
            $d->setTime(0, 0, 0);
            $dates[$d->getTimestamp()] = $d;
        }
        $dates = array_values($dates);

        usort($dates, function (\DateTime $a, \DateTime $b) {
            $a = $a->getTimestamp();
            $b = $b->getTimestamp();
            return ($a == $b) ? 0 : ($a < $b ? -1 : 1);
        });

        $from = $dates ? $dates[0] : (new \DateTime('now', new \DateTimeZone('UTC')))->setTime(0, 0, 0);
        $to = count($dates) > 1 ? $dates[count($dates) - 1] : clone $from;
        $to->add(new \DateInterval('P1D'));

        return [$from, $to];
    }

    /**
     * @return EntityCollection
     */
    public function getReviews() {
        return $this->reviews;
    }

    public function getReview() {
        if (count($this->reviews) > 0) {
            return $this->reviews[0];
        }
        return null;
    }

    /**
     * @param EntityCollection $reviews
     * @return $this
     */
    public function setReviews($reviews) {
        $this->reviews = $reviews;
        return $this;
    }

    /**
     * @param Review $review
     * @return $this
     */
    public function addReview(Review $review) {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setMeeting($this);
        }
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
            $customValue->setMeeting($this);
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
            $customValue->setMeeting(null);
        }
        return $this;
    }

    /**
     * @return UserQualityReview
     */
    public function getUserQualityReview() {
        return $this->userQualityReview;
    }

    /**
     * @param UserQualityReview $userQualityReview
     * @return $this
     */
    public function setUserQualityReview($userQualityReview) {
        $this->userQualityReview = $userQualityReview;
        return $this;
    }

    /**
     * @param Review $review
     * @return $this
     */
    public function removeReview(Review $review) {
        if ($this->reviews->contains($review)) {
            $this->reviews->removeElement($review);
            $review->setMeeting(null);
        }
        return $this;
    }

    /**
     * @param bool $currentUserIsUser
     * @return $this
     */
    public function setStatusAndDateFromSuggestedDates($currentUserIsUser = true) {
        if ($this->getSuggestedDates()->count() == 1) {
            $this->setDate($this->getSuggestedDates()->first()->getDate());
            $this->setStatus('scheduled');
        } else {
            $this->setStatus($currentUserIsUser ? 'planning_guest' : 'planning_user');
        }
        return $this;
    }

    /**
     * Get meeting id unique for whole platform
     * @param $instanceAlias string alias of instance where meeting is happening
     * @return string
     */
    public function getGlobalId($instanceAlias) {
        return $instanceAlias . '-' . $this->getId() . '-' . $this->getUser()->getId() . '-' . $this->getGuest()->getId();
    }

    /**
     * Accept suggested date
     * @param $suggestedDateId
     * @return bool true if operation was successful, false if date with specified id doesn't exist
     */
    public function acceptSuggestedDate($suggestedDateId) {
        $date = $this->getSuggestedDates()->getById($suggestedDateId);
        if ($date) {
            $this->date = $date->getDate();
            return true;
        }
        return false;
    }

    /**
     * @return MeetingProgress
     */
    public function getProgress() {
        return $this->progress;
    }

    /**
     * @param MeetingProgress $progress
     */
    public function setProgress($progress) {
        $this->progress = $progress;
    }

    public function getProgressAndCreateIfNotExists() {
        if (!$this->progress) {
            $this->progress = new MeetingProgress();
        }
        return $this->progress;
    }

    /**
     * Registers meeting heartbit. Makes sure that it's not registered more often than sent.
     * @return bool registration result
     */
    public function trackHeartbit() {
        if (!$this->progress) {
            $this->progress = $this->getProgressAndCreateIfNotExists();
        } else {
            //Heartbit is sent once per minute thus should not be registered more often
            //In order to avoid an issue when two users are in meeting room and both sending
            //heartbit signal we should check when the last one was registered
            $minuteAgo = new \DateTime();
            $minuteAgo->setTimestamp(strtotime('-'.MeetingProgress::$HEARTBIT_INTERVAL_SECONDS.' seconds'));

            if ($this->progress->getLastDurationUpdate() > $minuteAgo) {
                return false;
            }
        }

        $this->progress->incrementDuration(MeetingProgress::$HEARTBIT_INTERVAL_SECONDS);
        $this->progress->setLastDurationUpdate(new \DateTime());
        return true;
    }

    /**
     * @return int|null
     */
    public function getRescheduledMeetingId() {
        return $this->rescheduledMeetingId;
    }

    /**
     * @param int|null $rescheduledMeetingId
     */
    public function setRescheduledMeetingId($rescheduledMeetingId) {
        $this->rescheduledMeetingId = $rescheduledMeetingId;
    }

    /**
     * @return string
     */
    public function getConferencePin() {
        return $this->conferencePin;
    }

    /**
     * @param string $conferencePin
     */
    public function setConferencePin($conferencePin) {
        $this->conferencePin = $conferencePin;
    }

    /**
     * @return bool
     */
    public function isIsCreatedByGuest()
    {
        return $this->isCreatedByGuest;
    }

    /**
     * @param bool $isCreatedByGuest
     * @return Meeting
     */
    public function setIsCreatedByGuest($isCreatedByGuest)
    {
        $this->isCreatedByGuest = $isCreatedByGuest;
        return $this;
    }

    /**
     * @return string
     */
    public function getCancellationReason() {
        return $this->cancellationReason;
    }

    /**
     * @param string $cancellationReason
     * @return $this
     */
    public function setCancellationReason($cancellationReason) {
        $this->cancellationReason = $cancellationReason;
        return $this;
    }

    public function jsonSerialize() {
        $suggestedDates = [];
        if (count($this->getSuggestedDates()) > 0) {
            /** @var SuggestedDate $date */
            foreach ($this->getSuggestedDates() as $date) {
                $suggestedDates[] = $date->jsonSerialize();
            }
        }
        return [
            'id' => $this->getId(),
            'user' => $this->getUser(),
            'guest' => $this->getGuest(),
            'date' => !is_null($this->getDate()) ? $this->getDate()->format('c') : '',
            'status' => $this->getStatus(),
            'created' => $this->getCreated()->format('c'),
            'duration' => $this->getDuration(),
            'wrapup' => $this->getWrapup(),
            'description' => $this->getDescription(),
            'type' => $this->getType(),
            'suggestedDates' => $suggestedDates,
            'review' => $this->getReview(),
            'reschedulingReason' => $this->getReschedulingReason(),
        ];
    }

    /**
     * @param MeetingRequest $request
     * @return Meeting
     */
    static public function createFromRequest(MeetingRequest $request) {
        $meeting = new self;
        $meeting->setDate(new \DateTime('now', new \DateTimeZone('UTC')));
        $meeting->setStatus('live');
        $meeting->setType($request->getType());
        $meeting->setDescription($request->getDescription());
        $meeting->setGuest($request->getGuest());
        $meeting->setCustomValues($request->getCustomValues());
        return $meeting;
    }


    /**
     * @param Meeting $meeting
     * @return bool
     */
    static public function isStartingSoon(Meeting $meeting) {
        if ($meeting->getStatus() == 'scheduled') {
            $meetingTz = $meeting->getDate()->getTimezone();
            $systemTz = date_default_timezone_get();

            date_default_timezone_set('UTC');
            $meeting->getDate()->setTimezone(new \DateTimeZone('UTC'));

            $isSoon = ($meeting->getDate()->getTimestamp() - time() < 3600) && ($meeting->getDate()->getTimestamp() - time() > -600); // [-10 min, 60 min]

            date_default_timezone_set($systemTz);
            $meeting->getDate()->setTimezone($meetingTz);

            return $isSoon;
        }

        return false;
    }

    /**
     * Check if "planned" meeting is expired.
     * 
     * @param Meeting $meeting
     * @return bool
     */
    static public function isExpired(Meeting $meeting) {
        $isExpired = false;
        $now = new \DateTime('now');

        if (in_array($meeting->getStatus(), ['planning_user', 'planning_guest'], true)) {
            $isExpired = true;
            /** @var $suggestedDate SuggestedDate */
            foreach ($meeting->getSuggestedDates() as $suggestedDate) {
                if ($suggestedDate->getDate() > $now) {
                    $isExpired = false;
                    break;
                }
            }
        } elseif ($meeting->getStatus() == 'scheduled') {
            $isExpired = !self::isStartingSoon($meeting) && $meeting->getDate() < $now;
        }

        return $isExpired;
    }

    /**
     * @return bool
     */
    public function isScheduled(): bool
    {
        return $this->status == static::STATUS_SCHEDULED;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status == static::STATUS_COMPLETED;
    }

    public function isMissed() {
        return in_array($this->getStatus(), ['missed_guest', 'missed_user', 'missed_both']);
    }

    /**
     * @return bool
     */
    public function isLive(): bool
    {
        return $this->status == static::STATUS_LIVE;
    }

    public function isMultiparty() {
        return strpos($this->getGuest()->getEmail(), ',') !== FALSE;
    }

    /**
     * @return string
     */
    public function getReschedulingReason() {
        return $this->reschedulingReason;
    }

    /**
     * @param string $reschedulingReason
     * @return $this
     */
    public function setReschedulingReason($reschedulingReason) {
        $this->reschedulingReason = $reschedulingReason;
        return $this;
    }
}
