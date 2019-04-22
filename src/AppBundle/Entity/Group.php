<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * User group
 *
 * @Api\Entity(endPoint="groups")
 */
class Group extends BaseEntity {
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var string group name (from base class)
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     * @AppAssert\Name()
     */
    private $name;
    /**
     * @var string slug that make unique alias to the group page on the current instance
     *
     * @Api\Property()
     * @Assert\Regex(pattern="/^[\w\d-]+$/", message="Group URL should contain only digits, letters and hyphens")
     */
    private $webpageSlug;
    /**
     * @var bool
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $deleted = false;

    /**
     * @var EntityCollection<User> users that this group consists of
     *
     * @Api\Property(entity="User")
     * @Api\Collection()
     */
    private $users;
    /**
     * @var EntityCollection<MeetingType> meeting types that can be for this group
     *
     * @Api\Property(entity="MeetingType")
     * @Api\Collection()
     */
    private $meetingTypes;

    public function __construct() {
        $this->users = new EntityCollection();
        $this->meetingTypes = new EntityCollection();
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
    public function getWebpageSlug() {
        return $this->webpageSlug;
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
     * @return bool
     */
    public function isDeleted() {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     * @return $this
     */
    public function setDeleted($deleted) {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return EntityCollection
     */
    public function getUsers() {
        return $this->users;
    }

    /**
     * @param EntityCollection $users
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
            $user->addGroup($this);
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
            $user->removeGroup($this);
        }
        return $this;
    }

    /**
     * @return EntityCollection
     */
    public function getMeetingTypes() {
        return $this->meetingTypes;
    }

    /**
     * @param EntityCollection $meetingTypes
     * @return $this
     */
    public function setMeetingTypes($meetingTypes) {
        $this->meetingTypes = $meetingTypes;
        return $this;
    }

    /**
     * @param MeetingType $meetingType
     * @return $this
     */
    public function addMeetingType(MeetingType $meetingType) {
        if (!$this->meetingTypes->contains($meetingType)) {
            $this->meetingTypes->add($meetingType);
        }
        return $this;
    }

    /**
     * @param MeetingType $meetingType
     * @return $this
     */
    public function removeMeetingType(MeetingType $meetingType) {
        if ($this->meetingTypes->contains($meetingType)) {
            $this->meetingTypes->removeElement($meetingType);
        }
        return $this;
    }
}
