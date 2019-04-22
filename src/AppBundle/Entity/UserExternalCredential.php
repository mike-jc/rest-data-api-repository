<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;

/**
 * User credentials (oauth2 tokens) for external APIs
 *
 * @Api\Entity(endPoint="user_external_credentials")
 */
class UserExternalCredential extends BaseEntity {
    const TYPE_GOOGLE = 'google';

    /**
     * @var int
     *
     * @Api\Property()
     * @Api\Id()
     */
    private $id;
    /**
     * @var string
     *
     * @Api\Property()
     * @Assert\Choice({"google"})
     */
    private $type;
    /**
     * @var string serialized data of variable structure
     * Warning: field should not be directly accessable to user for editing to protect system from XSS attacks
     *
     * @Api\Property()
     */
    private $data;
    /**
     * @var string serialized data of variable structure
     * Warning: field should not be directly accessable to user for editing to protect system from XSS attacks
     *
     * @Api\Property()
     */
    private $options;

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
    public function getType() {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * @param bool $raw
     * @return array|object|string|null
     */
    public function getData($raw = true) {
        if ($raw) {
            return $this->data;
        } else {
            return $this->data ? unserialize($this->data) : null;
        }
    }

    /**
     * @param array|object|string|null $data
     * @return $this
     */
    public function setData($data) {
        $this->data = $data ? (is_array($data) || is_object($data) ? serialize($data) : $data) : null;
        return $this;
    }

    /**
     * @param bool $raw
     * @return array|object|string|null
     */
    public function getOptions($raw = true) {
        if ($raw) {
            return $this->options;
        } else {
            return $this->options ? unserialize($this->options) : null;
        }
    }

    /**
     * @param array|object|string|null $options
     * @return $this
     */
    public function setOptions($options) {
        $this->options = $options ? (is_array($options) || is_object($options) ? serialize($options) : $options) : null;
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