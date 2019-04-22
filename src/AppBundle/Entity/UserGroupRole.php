<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;

/**
 * Relation between user, group and role
 *
 * @Api\Entity(endPoint="user_group_roles")
 */
class UserGroupRole extends BaseEntity {
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;

    /**
     * @var User user associated with this relation
     *
     * @Api\Property(entity="User", writeOnly=true)
     */
    private $user;
    /**
     * @var Role role associated with this relation
     *
     * @Api\Property(entity="Role")
     */
    private $role;
    /**
     * @var Group group associated with this relation
     *
     * @Api\Property(entity="Group")
     */
    private $group;

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
     * @return Role
     */
    public function getRole() {
        return $this->role;
    }

    /**
     * @param Role $role
     * @return $this
     */
    public function setRole(Role $role = null) {
        $this->role = $role;
        return $this;
    }

    /**
     * @return Group
     */
    public function getGroup() {
        return $this->group;
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function setGroup(Group $group = null) {
        $this->group = $group;
        return $this;
    }
}
