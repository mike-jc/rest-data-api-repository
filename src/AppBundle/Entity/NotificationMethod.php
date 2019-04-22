<?php

namespace AppBundle\Entity;

use AppBundle\Repository\Annotation as Api;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Notifications methods
 *
 * @Api\Entity(endPoint="notification_methods")
 */
class NotificationMethod extends BaseEntity {
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
     * @Assert\Choice({"browser", "email", "sms"})
     */
    private $name;
    /**
     * @var bool
     *
     * @Api\Property(type="bool")
     * @Assert\Choice({"0", "1"})
     */
    private $enabled = false;

    /**
     * @var User user associated with this credentials
     *
     * @Api\Property(entity="User")
     */
    private $user;

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
     * @return bool
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setEnabled($enabled) {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return $this
     */
    public function setUser(User $user = null) {
        $this->user = $user;
        return $this;
    }
}