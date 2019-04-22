<?php

namespace AppBundle\Entity;

use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * Meeting type
 *
 * @Api\Entity(endPoint="meeting_types")
 */
class MeetingType extends BaseEntity implements JsonSerializable {
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var string name of meeting type
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Name()
     */
    private $name;
    /**
     * @var int duration in minutes for meetings of this type
     *
     * @Api\Property(type="integer")
     * @Assert\Type(type="integer")
     * @Assert\GreaterThan(0)
     */
    private $duration;
    /**
     * @var bool
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $deleted = false;
    /**
     * @var bool
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $enabled = false;
    /**
     * @var bool
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $published = false;

    /**
     * @var string
     * @Api\Property()
     */
    private $dracoonTargets;

    /**
     * @var BookingForm form for booking a meeting of this type
     *
     * @Api\Property(entity="BookingForm")
     */
    private $bookingForm;

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

    public function getNameWithDuration() {
        return $this->getName() .' ('. $this->getDuration() .' min)';
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
    public function getDuration() {
        return $this->duration;
    }

    /**
     * @param string $duration
     * @return $this
     */
    public function setDuration($duration) {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDeleted() {
        return $this->deleted;
    }

    /**
     * @param boolean $deleted
     * @return $this
     */
    public function setDeleted($deleted) {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return mixed
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * @param bool $published
     */
    public function setPublished($published) {
        $this->published = $published;
    }

    public function isPublished() {
        return $this->published;
    }

    /**
     * @param bool $decoded
     * @param bool $unique
     * @return null|string|array
     */
    public function getDracoonTargets($decoded = false, $unique = false) {
        $this->dracoonTargets = trim(stripslashes($this->dracoonTargets), '"');
        if ($decoded) {
            if ($this->dracoonTargets) {
                $targets = (array)@json_decode($this->dracoonTargets, true);
                if ($unique) {
                    $uniqueTargets = [];
                    foreach ($targets as $target) {
                        if (!empty($target['path'])) {
                            $uniqueTargets[$target['path']] = $target;
                        }
                    }
                    return array_values($uniqueTargets);
                }
                return $targets;
            }
            return [];
        }
        return $this->dracoonTargets;
    }

    /**
     * @return array
     */
    public function getDracoonTargetsDecoded() {
        return [];
    }

    /**
     * @param null|array $dracoonTargets
     * @return $this
     */
    public function setDracoonTargets($dracoonTargets) {
        $this->dracoonTargets = !is_null($dracoonTargets) ? @json_encode($dracoonTargets) : null;
        return $this;
    }

    /**
     * @param mixed $enabled
     * @return $this
     */
    public function setEnabled($enabled) {
        $this->enabled = $enabled;
        return $this;
    }

    public function isInaccessible() {
        return $this->isDeleted() || !$this->isEnabled();
    }

    function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'duration' => $this->getDuration(),
            'deleted' => $this->isDeleted(),
            'enabled' => $this->isEnabled(),
        ];
    }

    /**
     * Factory from array.
     *
     * @param array $data
     * @return MeetingType
     */
    static public function create(array $data = null): MeetingType
    {
        $res = new static();
        if (is_array($data)) {
            $res->setId($data['id'] ?? null)
                ->setName($data['name'] ?? '')
                ->setDuration($data['duration'] ?? 0)
                ->setDeleted($data['deleted'] ?? false)
                ->setEnabled($data['enabled'] ?? false)
                ->setPublished($data['published'] ?? false);
        }
        return $res;
    }

    /**
     * @return BookingForm
     */
    public function getBookingForm() {
        return $this->bookingForm;
    }

    /**
     * @param BookingForm $bookingForm
     * @return $this
     */
    public function setBookingForm(BookingForm $bookingForm = null) {
        $this->bookingForm = $bookingForm;
        return $this;
    }
}
