<?php

namespace AppBundle\Entity;

use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * Guest in the meeting
 *
 * @Api\Entity(endPoint="guests")
 */
class Guest extends BaseEntity implements JsonSerializable {
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var string guest name
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     * @AppAssert\HumanName
     */
    private $name;
    /**
     * @var string guest email
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     * @AppAssert\Emails
     */
    private $email;

    /**
     * @var string user phone number
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Phone
     */
    private $phone;

    /**
     * @var string user email (from base class)
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank(message="Please select your timezone from the list", groups={"TimezoneRequired"})
     * @AppAssert\Timezone()
     */
    private $timezone;

    /**
     * @var string user access key to session
     *
     * @Api\Property()
     * @AppAssert\Uid()
     */
    private $accessKey;

    /**
     * @var Meeting meeting of the guest
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
     * @param $defaultName string user name that will be returned if he haven't specified own yet
     * @return string
     */
    public function getName($defaultName = null) {
        //If user name is the same as his email -- show default name instead
        if (!is_null($defaultName) && $this->name == $this->email) {
            return $defaultName;
        }
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
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone() {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     * @return $this
     */
    public function setTimezone($timezone) {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccessKey() {
        return $this->accessKey;
    }

    /**
     * @param string $accessKey
     * @return $this
     */
    public function setAccessKey($accessKey) {
        $this->accessKey = $accessKey;
        return $this;
    }

    /**
     * @return Meeting
     */
    public function getMeeting() {
        return $this->meeting;
    }

    /**
     * @return string
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone) {
        $this->phone = $phone;
    }

    /**
     * @param Meeting $meeting
     * @return $this
     */
    public function setMeeting(Meeting $meeting = null) {
        $this->meeting = $meeting;
        if ($meeting && (!$meeting->getGuest() || $meeting->getGuest()->getId() != $this->getId())) {
            $meeting->setGuest($this);
        }
        return $this;
    }

    private function formatByMask($value, $mask) {
        $result = '';
        $j = 0;
        for ($i = 0; $i < strlen($mask); $i++) {
            if (in_array($mask{$i}, ['X', 'x']) && $j < strlen($value)) {
                $result .= $value{$j};
                $j++;
            } else {
                $result .= $mask{$i};
            }
        }
        return $result;
    }

    function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'accessKey' => $this->formatByMask($this->getAccessKey(), 'XXXX-XXXX-XXXX')
        ];
    }
}
