<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Helper\DatetimeTrait;
use AppBundle\Helper\GoogleCalendarTrait;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * Timeslot that is blocked in availability of the user or group
 *
 * @Api\Entity(endPoint="blocked_timeslots")
 */
class BlockedTimeslot extends BaseEntity {
    use DatetimeTrait;
    use GoogleCalendarTrait;

    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var \DateTime start date and time of blockage
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime()
     * @Assert\NotBlank
     */
    protected $fromDate;
    /**
     * @var \DateTime end date and time of blockage
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime()
     * @Assert\NotBlank
     */
    protected $toDate;
    /**
     * @var string how often to repeat this blockage
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\Choice({"daily", "weekly", "monthly", "yearly"})
     */
    protected $repeat;
    /**
     * @var int interval between repeating (e.g. every 2 weeks, every 3 days)
     *
     * @Api\Property(type="integer")
     * @Assert\Type(type="int")
     * @Assert\Range(min="1", max="30")
     */
    protected $interval;
    /**
     * @var array list of number of week days (from 1 to 7) for repeat = daily
     *      (e.g., every Monday, Wednesday and Friday), maximum 7 days in list
     *
     * @Api\Property(type="array")
     * @Assert\Type(type="array")
     * @Assert\Count(min="0", max="7")
     */
    protected $byDay;
    /**
     * @var int how many times this slot is repeated
     *
     * @Api\Property(type="integer")
     * @Assert\Type(type="int")
     * @Assert\GreaterThanOrEqual(0)
     */
    protected $count;
    /**
     * @var \DateTime date on what repeating should be stopped
     *
     * @Api\Property(type="date")
     * @Assert\Date()
     */
    protected $until;
    /**
     * @var string
     *
     * @Api\Property()
     * @AppAssert\Uid()
     */
    protected $externalId;
    /**
     * @var string
     *
     * @Api\Property()
     * @AppAssert\Uid()
     */
    protected $externalRecurringId;
    /**
     * @var string
     *
     * @Api\Property()
     * @AppAssert\Label()
     */
    protected $title;
    /**
     * @var bool
     */
    protected $lowerLevel = false;

    /**
     * @var Availability availability that has this blockage
     *
     * @Api\Property(entity="Availability", writeOnly=true)
     */
    protected $availability;

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
    public function getFromDate() {
        return $this->fromDate;
    }

    /**
     * @param \DateTime $fromDate
     * @return $this
     */
    public function setFromDate($fromDate) {
        $this->fromDate = $fromDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getToDate() {
        return $this->toDate;
    }

    /**
     * @param \DateTime $toDate
     * @return $this
     */
    public function setToDate($toDate) {
        $this->toDate = $toDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromTime() {
        return $this->fromDate->format('H:i');
    }

    /**
     * @return string
     */
    public function getToTime() {
        return $this->toDate->format('H:i');
    }

    /**
     * @return \DateTime
     */
    public function getDate() {
        $date = clone $this->fromDate;
        return $date->setTime(0, 0, 0);
    }

    /**
     * @return string
     */
    public function getRepeat() {
        return $this->repeat;
    }

    public function getRepeatName() {
        return self::isValidRepeat($this->repeat) ? ucfirst($this->repeat) : 'No repeat';
    }

    /**
     * @param string $repeat
     * @return $this
     */
    public function setRepeat($repeat) {
        $this->repeat = self::isValidRepeat($repeat) ? $repeat : null;
        return $this;
    }

    /**
     * @return int
     */
    public function getInterval() {
        return $this->interval;
    }

    /**
     * @param int $interval
     * @return $this
     */
    public function setInterval($interval) {
        $this->interval = $interval;
        return $this;
    }

    /**
     * @return array
     */
    public function getByDay() {
        return $this->byDay;
    }

    /**
     * @param array|null $byDay
     * @return $this
     */
    public function setByDay(array $byDay = null) {
        $this->byDay = $byDay;
        return $this;
    }

    /**
     * @return int
     */
    public function getCount() {
        return $this->count;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function setCount($count) {
        $this->count = $count;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUntil() {
        return $this->until;
    }

    /**
     * @param \DateTime $until
     * @return $this
     */
    public function setUntil(\DateTime $until) {
        $this->until = $until;
        return $this;
    }

    /**
     * @return string
     */
    public function getExternalId() {
        return $this->externalId;
    }

    /**
     * @param string $externalId
     * @return $this
     */
    public function setExternalId($externalId) {
        $this->externalId = $externalId;
        return $this;
    }

    /**
     * @return string
     */
    public function getExternalRecurringId() {
        return $this->externalRecurringId;
    }

    /**
     * @param string $externalRecurringId
     * @return $this
     */
    public function setExternalRecurringId($externalRecurringId) {
        $this->externalRecurringId = $externalRecurringId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLowerLevel() {
        return $this->lowerLevel;
    }

    /**
     * @param bool $lowerLevel
     * @return $this
     */
    public function setLowerLevel($lowerLevel) {
        $this->lowerLevel = $lowerLevel;
        return $this;
    }

    /**
     * @return Availability
     */
    public function getAvailability() {
        return $this->availability;
    }

    /**
     * @param Availability $availability
     * @return $this
     */
    public function setAvailability(Availability $availability = null) {
        $this->availability = $availability;
        return $this;
    }

    /**
     * @param string|\DateTimeZone $timeZone
     * @param string $direction
     * @return BlockedTimeslot
     */
    public function modifyTimeZone($timeZone, $direction) {
        $timeZone = $this->getDateTimeZone($timeZone);

        // User can operate blocked time slots that are during one day.
        // But after applying user time zone slot can be out of one day limits
        if ($direction == 'toUTC') {
            $offset = -1 * $this->getTimezoneOffset($timeZone);
            $this->fromDate->modify("$offset minutes");
            $this->toDate->modify("$offset minutes");
            if ($this->until) {
                $this->until->modify("$offset minutes");
            }
        } else {
            $this->fromDate->setTimezone($timeZone);
            $this->toDate->setTimezone($timeZone);
            if ($this->until) {
                $this->until->setTimezone($timeZone);
            }
        }

        return $this;
    }

    /**
     * @param string|\DateTimeZone $timeZone
     * @return BlockedTimeslot|bool
     */
    public function toUTC($timeZone) {
        return $this->modifyTimeZone($timeZone, 'toUTC');
    }

    /**
     * @param string|\DateTimeZone $timeZone
     * @return BlockedTimeslot|bool
     */
    public function fromUTC($timeZone) {
        return $this->modifyTimeZone($timeZone, 'fromUTC');
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return bool
     */
    public function intersectRange(\DateTime $start, \DateTime $end) {
        $from = $this->getFromDate()->getTimestamp();
        $to = $this->getToDate()->getTimestamp();
        return ($start->getTimestamp() <= $to && $end->getTimestamp() > $from); // intersection
    }

    /**
     * @param array|null $rules
     * @return $this
     */
    public function parseRecurrenceRules(array $rules = null) {
        if (!$rules) {
            return $this;
        }
        foreach ($rules as $rule) {
            if (preg_match('/FREQ=(DAILY|WEEKLY|MONTHLY|YEARLY)/i', $rule, $m)) {
                $this->setRepeat(strtolower($m[1]));
            }
            if (preg_match('/INTERVAL=(\d+)/i', $rule, $m)) {
                $this->setInterval((int)$m[1]);
            }
            if (preg_match('/COUNT=(\d+)/i', $rule, $m)) {
                $this->setCount((int)$m[1]);
            }
            if (preg_match('/BYDAY=(((MO|TU|WE|TH|FR|SA|SU)([,\s])*)+)/i', $rule, $m)) {
                $days = [];
                foreach (explode(',', $m[1]) as $dayName) {
                    $dayName = strtoupper(trim($dayName));
                    if (isset(self::$shortDaysOfWeek[$dayName])) {
                        $days[] = self::$shortDaysOfWeek[$dayName];
                    }
                }
                if ($days) {
                    $this->setByDay($days);
                    $this->setRepeat('daily');
                }
            } elseif (preg_match('/BYDAY=((\d+)(MO|TU|WE|TH|FR|SA|SU))/i', $rule, $m)) {
                $dayName = strtoupper(trim($m[3]));
                if (isset(self::$shortDaysOfWeek[$dayName])) {
                    $this->setByDay([
                        'day' => self::$shortDaysOfWeek[$dayName],
                        'ordinal' => (int)$m[2],
                    ]);
                    $this->setRepeat('monthly');
                }
            }
            if (preg_match('/UNTIL=(\d{8}T\d{6}Z)/i', $rule, $m)) {
                $until = new \DateTime($m[1]);
                $until->setTimezone(new \DateTimeZone('UTC'))->setTime(0, 0, 0);
                $this->setUntil($until);
            }
        }
        return $this;
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param \Closure $processor
     */
    public function callForBlockageInstances(\DateTime $start, \DateTime $end, \Closure $processor) {

        if ($interval = $this->getRepeatInterval($this->getInterval())) {

            $i = 0;
            $slot = self::getFlatClone($this);
            while (true) {
                $date = $slot->getDate();
                $byDay = $slot->getByDay() ?: [];

                if (isset($byDay['day']) && isset($byDay['ordinal'])) {
                    $dayName = !empty(self::$numDaysOfWeek[$byDay['day']]) ? self::$numDaysOfWeek[$byDay['day']] : null;
                    $ordinal = !empty(self::$ordinal[$byDay['ordinal']]) ? self::$ordinal[$byDay['ordinal']] : null;

                    if ($dayName && $ordinal) {
                        $date->modify("$ordinal $dayName");
                        $newYear = $date->format('Y');
                        $newMonth = $date->format('m');
                        $newDay = $date->format('d');

                        $slot->getFromDate()->setDate($newYear, $newMonth, $newDay);
                        $slot->getToDate()->setDate($newYear, $newMonth, $newDay);

                        $byDay = [];
                    }
                }

                if ($date->getTimestamp() >= $end->getTimestamp()) {
                    break;
                }
                if ($slot->getUntil() && $date->getTimestamp() > $slot->getUntil()->getTimestamp()) {
                    break;
                }
                if ($slot->getCount() && $i >= $slot->getCount()) {
                    break;
                }

                $canBeProcessed = true;
                $canBeCounted = true;
                if ($byDay && !in_array((int)$date->format('N'), $byDay)) {
                    $canBeProcessed = false;
                    $canBeCounted = false;
                }
                if ($date->getTimestamp() < $start->getTimestamp()) {
                    $canBeProcessed = false;
                }

                if ($canBeProcessed) {
                    $processor(self::getFlatClone($slot));
                }
                if ($canBeCounted) {
                    $i++;
                }

                $slot->getFromDate()->add($interval);
                $slot->getToDate()->add($interval);
            }

        } elseif ($this->intersectRange($start, $end)) {

            $processor(self::getFlatClone($this));
        }
    }

    /**
     * @return bool
     */
    public function isAllDay() {
        //if ($this->isLowerLevel()) {
        //    return false;
        //}
        return $this->getFromDate() && $this->getFromTime() == '00:00'
                && $this->getToDate() && $this->getToTime() == '23:59';
    }

    /**
     * @return bool
     */
    public function isEditable() {
        if ($this->isLowerLevel()) {
            return false;
        }
        if ($this->getExternalId()) {
            return !empty($this->getMeetingIdFromEventId($this->getExternalId()));
        }
        return true;
    }

    /**
     * @param \DateTimeZone|null $timezone
     * @return string
     */
    public function getDerivativeKey(\DateTimeZone $timezone = null) {
        if ($this->getExternalRecurringId()) {
            $start = clone $this->getFromDate();
            if ($timezone) {
                $start->setTimezone($timezone);
            }
            return $this->getExternalRecurringId() .'-'. $start->format('Y-m-d');
        }
        return '';
    }

    /**
     * @param \DateTimeZone|null $timezone
     * @return string
     */
    public function getRecurrentKey(\DateTimeZone $timezone = null) {
        if ($this->getExternalId()) {
            $start = clone $this->getFromDate();
            if ($timezone) {
                $start->setTimezone($timezone);
            }
            return $this->getExternalId() .'-'. $start->format('Y-m-d');
        }
        return '';
    }

    /**
     * Get clone without availability property
     * @param self $slot
     * @return self
     */
    static public function getFlatClone($slot) {
        $clone = clone $slot;
        $clone->setFromDate(clone $slot->getFromDate());
        $clone->setToDate(clone $slot->getToDate());
        if ($slot->getUntil()) {
            $clone->setUntil(clone $slot->getUntil());
        }
        return $clone;
    }
}
