<?php

namespace AppBundle\Entity\File;

use AppBundle\Repository\Annotation as Api;
use AppBundle\Entity\User;

/**
 * @Api\Entity(endPoint="photos")
 */
class Photo extends Image {
    /**
     * @var User user associated with this image
     *
     * @Api\Property(entity="AppBundle\Entity\User")
     */
    protected $user;

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
    public function setUser($user) {
        $this->user = $user;
        if ($user && (!$user->getPhoto() || $user->getPhoto()->getId() != $this->getId())) {
            $user->setPhoto($this);
        }
        return $this;
    }
}