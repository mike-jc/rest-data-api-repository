<?php

namespace AppBundle\Entity;

use JsonSerializable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Entity\File\Photo;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Helper\RandomizationTrait;
use AppBundle\Validator\Constraints as AppAssert;
use AuthBundle\Helper\AclTrait;
use AuthBundle\OAuth\AuthorizedUserInterface;

/**
 * A User (formerly known as expert)
 *
 * @Api\Entity(endPoint="users")
 * @AppAssert\UserConstraint(
 *     groups={"Account", "Registration"}
 * )
 * @AppAssert\UniqueProperties(
 *     properties={"name"},
 *     message="User with this %property% already exists",
 *     groups={"Account", "Registration"}
 * )
 */
class User extends BaseEntity implements AuthorizedUserInterface, UserInterface, JsonSerializable {
    use AclTrait;
    use RandomizationTrait;

    /**
     * @var int
     * 
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var string user name
     * 
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank(groups={"Account"})
     * @AppAssert\HumanName
     */
    private $name;
    /**
     * @var string slug that make unique alias to the user page on the current instance
     *
     * @Api\Property()
     * @Assert\Regex(pattern="/^[\w\d-]+$/", message="User URL should contain only digits, letters and hyphens")
     */
    private $webpageSlug;
    /**
     * @var string user name to display
     */
    private $displayedName;
    /**
     * @var string user email (from base class)
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank(groups={"Registration"})
     * @AppAssert\Email
     */
    private $email;

    /**
     * @var string user email (from base class)
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Timezone()
     */
    private $timezone;

    /**
     * @var boolean flag that indicates if user is blocked
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $blocked = false;
    /**
     * @var bool
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $enabled = true;
    /**
     * @var boolean flag that indicates if user is online now
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $online = false;
    /**
     * @var \DateTime date when user account has been created
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $created;
    /**
     * @var string Plain password. Used for model validation
     *
     * @Api\Property()
     * @Assert\NotBlank(groups={"Registration"})
     * @Assert\Length(min="8", minMessage="Password should have at least eight symbols.")
     * @Assert\Regex(pattern="/\d[^\d]*\d/", message="Password should contain at least two digits.")
     * @Assert\Regex(pattern="/[^a-zA-Z0-9]+/", message="Password should contain at least one special symbol.")
     */
    private $plainPassword;
    /**
     * @var \DateTime
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $lastLogin;
    /**
     * @var string
     * @Api\Property(type="string")
     */
    protected $sessionId;
    /**
     * @var string
     * @Api\Property(type="string")
     */
    protected $requestUpdateEmail;
    /**
     * @var string
     * @Api\Property(type="string")
     */
    protected $requestUpdateEmailToken;
    /**
     * @var bool if user complete his registration (entered password)
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    protected $completed = false;
    /**
     * @var string id of message that will be shown to user right after he logged in next time
     * @Api\Property(type="string")
     */
    protected $afterLoginMessageId;
    /**
     * @var boolean flag that indicates if user is anonymized
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    protected $isAnonymized = false;
    /**
     * @var string
     * @Api\Property(type="string")
     */
    protected $phone;
    /**
     * @var string
     * @Api\Property(type="string")
     */
    protected $jobTitle;
    /**
     * @var string
     * @Api\Property(type="string")
     */
    protected $longitude;
    /**
     * @var string
     * @Api\Property(type="string")
     */
    protected $latitude;
    /**
     * @var string
     * @Api\Property(type="string")
     */
    protected $locationName;
    /**
     * @var string
     * @Api\Property(type="string")
     */
    protected $address;
    /**
     * @var string
     * @Api\Property(type="string")
     */
    protected $postcode;
    /**
     * @var string
     * @Api\Property(type="string")
     */
    protected $city;
    /**
     * @var string
     * @Api\Property(type="string")
     */
    protected $state;
    /**
     * @var string
     * @Api\Property(type="string")
     */
    protected $country;

    /**
     * @var Photo user photo
     *
     * @Api\Property(entity="AppBundle\Entity\File\Photo")
     * @Assert\Valid()
     * @Assert\Type(type="AppBundle\Entity\File\Photo")
     */
    protected $photo;
    /**
     * @var EntityCollection<Group> groups assigned to this user
     *
     * @Api\Property(entity="Group")
     * @Api\Collection()
     */
    private $groups;
    /**
     * @var EntityCollection<UserGroupRole> roles assigned to this user (and to his groups if needed)
     *
     * @Api\Property(entity="UserGroupRole")
     * @Api\Collection()
     */
    private $userGroupRoles;
    /**
     * @var EntityCollection<UserExternalCredential> credentials assigned to this user
     *
     * @Api\Property(entity="UserExternalCredential")
     * @Api\Collection()
     */
    protected $externalCredentials;
    /**
     * @var EntityCollection<NotificationMethod> notification methods assigned to this user
     *
     * @Api\Property(entity="NotificationMethod")
     * @Api\Collection()
     */
    protected $notificationMethods;

    public function __construct() {
        $this->groups = new EntityCollection();
        $this->userGroupRoles = new EntityCollection();
        $this->externalCredentials = new EntityCollection();
        $this->notificationMethods = new EntityCollection();
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
    public function getDisplayedName() {
        return $this->displayedName ?: $this->name;
    }

    /**
     * @param string $displayedName
     * @return $this
     */
    public function setDisplayedName($displayedName) {
        $this->displayedName = $displayedName;
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
     * Stub for UserInterface
     */
    public function getUsername() {
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
     * @return bool
     */
    public function isBlocked() {
        return $this->blocked;
    }

    /**
     * @param bool $blocked
     * @return $this
     */
    public function setBlocked($blocked) {
        $this->blocked = $blocked;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     * @return $this
     */
    public function setEnabled($enabled) {
        $this->enabled = $enabled;
        return $this;
    }

    public function isAccessible() {
        return $this->isEnabled() && !$this->isBlocked();
    }

    /**
     * @return bool
     */
    public function isOnline() {
        return $this->online;
    }

    /**
     * @param bool $online
     * @return $this
     */
    public function setOnline($online) {
        $this->online = $online;
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
     * @return string
     */
    public function getPlainPassword() {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     * @return $this
     */
    public function setPlainPassword($plainPassword) {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * Stub for UserInterface
     */
    public function getPassword() {
    }

    /**
     * Stub for UserInterface
     */
    public function getSalt() {
    }

    /**
     * @return \DateTime
     */
    public function getLastLogin() {
        return $this->lastLogin;
    }

    /**
     * @param \DateTime $lastLogin
     * @return $this
     */
    public function setLastLogin($lastLogin) {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return bool
     */
    public function isCompleted() {
        return $this->completed;
    }

    /**
     * @param bool $completed
     * @return $this
     */
    public function setCompleted($completed) {
        $this->completed = $completed;
        return $this;
    }

    /**
     * @return string
     */
    public function getAfterLoginMessageId() {
        return $this->afterLoginMessageId;
    }

    /**
     * @param string $afterLoginMessageId
     * @return $this
     */
    public function setAfterLoginMessageId($afterLoginMessageId) {
        $this->afterLoginMessageId = $afterLoginMessageId;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIsAnonymized() {
        return $this->blocked;
    }

    /**
     * @param bool $isAnonymized
     * @return $this
     */
    public function setIsAnonymized($isAnonymized) {
        $this->isAnonymized = $isAnonymized;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone) {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getJobTitle() {
        return $this->jobTitle;
    }

    /**
     * @param string $jobTitle
     * @return $this
     */
    public function setJobTitle($jobTitle) {
        $this->jobTitle = $jobTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getLongitude() {
        return $this->longitude;
    }

    /**
     * @param string $longitude
     * @return $this
     */
    public function setLongitude($longitude) {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return string
     */
    public function getLatitude() {
        return $this->latitude;
    }

    /**
     * @param string $latitude
     * @return $this
     */
    public function setLatitude($latitude) {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocationName() {
        return $this->locationName;
    }

    /**
     * @param string $locationName
     * @return $this
     */
    public function setLocationName($locationName) {
        $this->locationName = $locationName;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * @param string $address
     * @return $this
     */
    public function setAddress($address) {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostcode() {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode) {
        $this->postcode = $postcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * @param string $city
     * @return $this
     */
    public function setCity($city) {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getState() {
        return $this->state;
    }

    /**
     * @param string $state
     * @return $this
     */
    public function setState($state) {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * @param string $country
     * @return $this
     */
    public function setCountry($country) {
        $this->country = $country;
        return $this;
    }

    /**
     * @return Photo
     */
    public function getPhoto() {
        return $this->photo;
    }

    /**
     * @return File\Thumbnail|null
     */
    public function getDefaultThumbnail() {
        return $this->getPhoto() ? $this->getPhoto()->getThumbnails()->first() : null;
    }

    /**
     * @param Photo|null $photo
     * @return $this
     */
    public function setPhoto($photo = null) {
        if ($this->photo) {
            $this->photo->setUser(null);
        }
        $this->photo = $photo;
        if ($photo && (!$photo->getUser() || $photo->getUser()->getId() != $this->getId())) {
            $photo->setUser($this);
        }
        return $this;
    }

    /**
     * @return EntityCollection
     */
    public function getGroups() {
        return $this->groups->filter(function (Group $group) {
            return !$group->isDeleted();
        });
    }

    /**
     * @param EntityCollection $groups
     * @return $this
     */
    public function setGroups($groups) {
        $this->groups = $groups;
        $this->updateAclGroups();
        return $this;
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function addGroup(Group $group) {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $this->updateAclGroups();
            if ($group instanceof Group) {
                $group->addUser($this);
            }
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
            $this->updateAclGroups();
            if ($group instanceof Group) {
                $group->removeUser($this);
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getGroupIds() {
        return $this->getGroups()->getKeys();
    }

    /**
     * @return EntityCollection
     */
    public function getUserGroupRoles() {
        return $this->userGroupRoles;
    }

    /**
     * @return array
     */
    public function getRoles() {
        $roles = [];
        /** @var UserGroupRole $role */
        foreach ($this->userGroupRoles as $role) {
            if ($role->getRole()) {
                $roles[$role->getRole()->getId()] = $role->getRole();
            }
        }
        return $roles;
    }

    /**
     * @param EntityCollection $userGroupRoles
     * @return $this
     */
    public function setUserGroupRoles($userGroupRoles) {
        $this->userGroupRoles = $userGroupRoles;
        $this->updateAclRoles();
        return $this;
    }

    /**
     * @param UserGroupRole $userGroupRole
     * @return $this
     */
    public function addUserGroupRole(UserGroupRole $userGroupRole) {
        if (!$this->userGroupRoles->contains($userGroupRole)) {
            $this->userGroupRoles->add($userGroupRole);
            $this->updateAclRoles();
        }
        return $this;
    }

    /**
     * Add role for every group if role depends on groups
     * @param Role $role
     * @return $this
     */
    public function addRole(Role $role) {

        if (self::roleDependsOnGroups($role)) {
            /** @var Group $group */
            foreach ($this->getGroups() as $group) {
                $groupRole = (new UserGroupRole())->setRole($role)->setGroup($group);
                $this->addUserGroupRole($groupRole);
            }
        } else {
            $groupRole = (new UserGroupRole())->setRole($role);
            $this->addUserGroupRole($groupRole);
        }

        $this->updateAclRoles();
        return $this;
    }

    /**
     * @param UserGroupRole $userGroupRole
     * @return $this
     */
    public function removeUserGroupRole(UserGroupRole $userGroupRole) {
        if ($this->userGroupRoles->contains($userGroupRole)) {
            $this->userGroupRoles->removeElement($userGroupRole);
            $this->updateAclRoles();
        }
        return $this;
    }

    /**
     * Remove all UserGroupRole relations with this role (from all groups)
     * @param Role $role
     * @return $this
     */
    public function removeRole(Role $role) {
        /** @var UserGroupRole $userGroupRole */
        foreach ($this->userGroupRoles as $userGroupRole) {
            if ($userGroupRole->getRole() && $userGroupRole->getRole()->getId() == $role->getId()) {
                $this->userGroupRoles->removeElement($userGroupRole);
            }
        }
        $this->updateAclRoles();
        return $this;
    }

    /**
     * @return EntityCollection
     */
    public function getExternalCredentials() {
        return $this->externalCredentials;
    }

    /**
     * @param EntityCollection $externalCredentials
     * @return $this
     */
    public function setExternalCredentials($externalCredentials) {
        $this->externalCredentials = $externalCredentials;
        return $this;
    }

    /**
     * @param UserExternalCredential $externalCredential
     * @return $this
     */
    public function addExternalCredential(UserExternalCredential $externalCredential) {
        if (!$this->externalCredentials->contains($externalCredential)) {
            $this->externalCredentials->add($externalCredential);
            $externalCredential->setUser($this);
        }
        return $this;
    }

    /**
     * @param UserExternalCredential $externalCredential
     * @return $this
     */
    public function removeExternalCredential(UserExternalCredential $externalCredential) {
        if ($this->externalCredentials->contains($externalCredential)) {
            $this->externalCredentials->removeElement($externalCredential);
            $externalCredential->setUser(null);
        }
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function removeExternalCredentialsByType($type) {
        $toRemove = array_filter($this->externalCredentials->toArray(), function (UserExternalCredential $credential) use ($type) {
            return $credential->getType() == $type;
        });

        /** @var UserExternalCredential $credential */
        foreach ($toRemove as $credential) {
            $this->removeExternalCredential($credential);
        }
        return $this;
    }

    /**
     * @param $type
     * @param bool $getData
     * @return UserExternalCredential|array|object|string|null
     */
    public function getExternalCredentialByType($type, $getData = false) {
        $credentials = array_filter($this->externalCredentials->toArray(), function (UserExternalCredential $credential) use ($type) {
            return $credential->getType() == $type;
        });
        /** @var UserExternalCredential $credential */
        $credential = current($credentials);

        return $credential ? ($getData ? $credential->getData(false) : $credential) : null;
    }

    /**
     * @return EntityCollection
     */
    public function getNotificationMethods() {
        return $this->notificationMethods;
    }

    /**
     * @param EntityCollection $notificationMethods
     * @return $this
     */
    public function setNotificationMethods($notificationMethods) {
        $this->notificationMethods = $notificationMethods;
        return $this;
    }

    /**
     * @param string $name
     * @return NotificationMethod
     */
    public function getNotificationMethodByName($name) {
        $methods = array_filter($this->notificationMethods->toArray(), function (NotificationMethod $method) use ($name) {
            return $method->getName() == $name;
        });
        if ($methods) {
            return reset($methods); // get first (should be the only one)
        }

        $method = new NotificationMethod();
        $method->setName($name);
        $this->addNotificationMethod($method);

        return $method;
    }

    /**
     * @param NotificationMethod $notificationMethods
     * @return $this
     */
    public function addNotificationMethod(NotificationMethod $notificationMethods) {
        if (!$this->notificationMethods->contains($notificationMethods)) {
            $this->notificationMethods->add($notificationMethods);
            $notificationMethods->setUser($this);
        }
        return $this;
    }

    /**
     * @param NotificationMethod $notificationMethods
     * @return $this
     */
    public function removeNotificationMethod(NotificationMethod $notificationMethods) {
        if ($this->notificationMethods->contains($notificationMethods)) {
            $this->notificationMethods->removeElement($notificationMethods);
            $notificationMethods->setUser(null);
        }
        return $this;
    }

    /**
     * @param bool $sort sort by name
     * @return EntityCollection<MeetingType>
     */
    public function getMeetingTypes($sort = false) {
        $types = [];
        /** @var Group $group */
        foreach ($this->getGroups() as $group) {
            /** @var MeetingType $type */
            foreach ($group->getMeetingTypes() as $type) {
                $types[$type->getId()] = $type;
            }
        }
        if ($sort) {
            uasort($types, function(MeetingType $a, MeetingType $b) {
                return strcmp($a->getName(), $b->getName());
            });
        }
        return new EntityCollection($types);
    }

    /**
     * @param array|EntityCollection $groups
     * @return bool
     */
    public function hasOneOfGroups($groups) {
        $groupIds = [];
        foreach ($groups as $group) {
            $groupIds[] = $group instanceof Group ? $group->getId() : $group;
        }

        return count(array_intersect($groupIds, $this->getGroups()->getKeys())) > 0;
    }

    /**
     * @param null $email
     * @return string
     */
    public function getIntercomId($email = null) {
        return md5($this->getId().':'.(!is_null($email) ? $email : $this->getEmail()));
    }

    /**
     * @return string
     */
    public function getSessionId() {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     */
    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }

    /**
     * @return string
     */
    public function getRequestUpdateEmail() {
        return $this->requestUpdateEmail;
    }

    /**
     * @param string $requestUpdateEmail
     */
    public function setRequestUpdateEmail($requestUpdateEmail) {
        $this->requestUpdateEmail = $requestUpdateEmail;
    }

    /**
     * @return string
     */
    public function getRequestUpdateEmailToken() {
        return $this->requestUpdateEmailToken;
    }

    /**
     * @param string $requestUpdateEmailToken
     */
    public function setRequestUpdateEmailToken($requestUpdateEmailToken) {
        $this->requestUpdateEmailToken = $requestUpdateEmailToken;
    }

    /**
     * @return array
     */
    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'phone' => $this->getPhone(),
            'jobTitle' => $this->getJobTitle(),
            'locationName' => $this->getLocationName(),
            'address' => $this->getAddress(),
            'city' => $this->getCity(),
            'state' => $this->getState(),
            'postcode' => $this->getPostcode(),
            'country' => $this->getCountry()
        ];
    }

    /**
     * Stub for UserInterface
     */
    public function eraseCredentials() {
    }

    /**
     * For AclTrait
     */
    protected function updateAclGroups() {
        $aclGroups = [];
        foreach ($this->groups as $group) {
            /** @var Group $group */
            if ($group->getId() && !$group->isDeleted()) {
                $aclGroups[] = '/groups/'. $group->getId();
            }
        }
        $this->setAclGroups($aclGroups);
    }

    /**
     * For AclTrait
     */
    protected function updateAclRoles() {
        $aclRoles = [];
        /** @var UserGroupRole $role */
        foreach ($this->userGroupRoles as $role) {
            if (!$role->getRole()) {
                continue;
            }
            $roleName = strtolower($role->getRole()->getName());

            if (!isset($aclRoles[$roleName])){
                $aclRoles[$roleName] = [];
            }
            if ($role->getGroup()) {
                $aclRoles[$roleName][] = '/groups/'. $role->getGroup()->getId();
            }
        }
        $this->setAclRoles($aclRoles);
    }

    public function getSlug() {
        return preg_replace('/[^a-z0-9]/', '', strtolower($this->name));
    }

    /**
     * @param Role $role
     * @return bool
     */
    static public function roleDependsOnGroups(Role $role) {

        static $rolesDependingOnGroups = [
            AuthorizedUserInterface::MANAGER,
        ];
        return in_array(strtolower($role->getName()), $rolesDependingOnGroups);
    }

    /**
     * At least 8 symbols. At least one special symbol, two digits.
     * @return string
     */
    static public function makeRandomPassword() {
        $psw = self::makeRandomString(self::$letters . self::$digits . self::$specials, 8);
        $hasTwoDigits = preg_match('/\d[^\d]*\d/', $psw);
        if (!$hasTwoDigits) {
            $psw = $psw . self::makeRandomString(self::$digits, 2);
        }
        $hasSpecialSymbols = preg_match('/[^0-9a-zA-Z]+/', $psw);
        if (!$hasSpecialSymbols) {
            $psw = self::makeRandomString(self::$specials, 2) . $psw;
        }
        return $psw;
    }

    /**
     * @return User
     */
    static public function createTestUser() {
        $testUser = new self();
        $testUser->setId('test');
        $testUser->setName('Anonymous');
        $testUser->setEmail('anonymous@email.test');

        return $testUser;
    }

    /**
     * @param int $length
     * @throws \Exception
     * @return string
     */
    public static function generateApiKey($length = 14)
    {
        if ($length < 6) {
            $length = 6;
        } elseif ($length > 36) {
            $length = 36;
        }
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new \Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $length);
    }
}
