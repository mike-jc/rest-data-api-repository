<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Validator\Constraints as AppAssert;
use Sessions24\AppBundle\Sdk\Keeper\Model\Addon as SdkAddon;

/**
 * Addon
 *
 * @Api\Entity(endPoint="addons")
 */
class Addon extends BaseEntity {
    public const BOOKING_FORMS = 'booking-forms';
    public const GOOGLE_CALENDAR = 'gcalendar';
    public const RECORDINGS = 'recordings';
    public const SAML = 'saml';

    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var string addon name
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     * @AppAssert\Name()
     */
    private $name;
    /**
     * @var string addon alias - using to identify addon bundle
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     * @AppAssert\Uid()
     */
    private $alias;
    /**
     * @var string short description
     * Warning: use filtered_symbols option if open this field for user via TextareaType to prevent XSS attacks
     *
     * @Api\Property()
     */
    private $intro;
    /**
     * @var string long description
     * Warning: use filtered_symbols option if open this field for user via TextareaType to prevent XSS attacks
     *
     * @Api\Property()
     */
    private $description;
    /**
     * @var boolean flag that indicates if addon is enabled
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $enabled;
    /**
     * @var boolean flag that indicates if user must to request addon enabling
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $requestEnable;
    /**
     * @var string addon parameters (e.g., subscription id on external payment system)
     *
     * @Api\Property(type="text")
     */
    private $parameters;

    /**
     * Error message that is not null when add-on can't be activated because another add-on which it conficts with is active
     */
    private $blockedMessage = null;

    /**
     * json map for lazy loading json decoded params
     * @see $this->getJsonDecodedParams()
     * @var array
     */
    private $jsonDecodedParamsMap = [];

    /**
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
    public function getAlias() {
    	return $this->alias;
    }

    /**
     * @param string $alias
     * @return $this
     */
    public function setAlias($alias) {
    	$this->alias = $alias;
    	return $this;
    }

    /**
     * @return string
     */
    public function getIntro() {
        return $this->intro;
    }

    /**
     * @param string $intro
     * @return $this
     */
    public function setIntro($intro) {
        $this->intro = $intro;
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

    /**
     * @return boolean
     */
    public function isRequestEnable() {
        return $this->requestEnable;
    }

    /**
     * @param boolean $requestEnable
     */
    public function setRequestEnable($requestEnable) {
        $this->requestEnable = $requestEnable;
        return $this;
    }

    /**
     * @param bool $raw
     * @return array|object|string|null
     */
    public function getParameters($raw = true) {
        if ($raw) {
            return $this->parameters;
        } else {
            return $this->parameters ? unserialize($this->parameters) : null;
        }
    }

    /**
     * @param array|object|string|null $parameters
     * @return $this
     */
    public function setParameters($parameters) {
        $this->parameters = $parameters ? (is_array($parameters) || is_object($parameters) ? json_encode($parameters) : $parameters) : null;
        return $this;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function addParameters($parameters) {
        $this->setParameters(array_merge($this->getParameters(false), $parameters));
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addParameter($name, $value) {
        $this->setParameters([$name => $value]);
        return $this;
    }

    public function getParam(string $name, $default = null)
    {
        $params = $this->getJsonDecodedParams();
        return array_key_exists($name, $params) ? $params[$name] : $default;
    }

    /**
     * @return string|null
     */
    public function getSubscriptionId() {
        $parameters = $this->getParameters(false);
        return !empty($parameters['subscriptionId']) ? $parameters['subscriptionId'] : null;
    }

    /**
     * @return string
     */
    public function getBaseUrl() {

        switch ($this->getAlias()) {
            case 'review-redirect':
                return 'custom-endpage-redirect';
            default:
                return $this->getAlias();
        }
    }

    /**
     * @return mixed
     */
    public function getBlockedMessage() {
        return $this->blockedMessage;
    }

    /**
     * @param string $blockedMessage
     */
    public function setBlocked($blockedMessage) {
        $this->blockedMessage = $blockedMessage;
    }

    public function isBlocked() {
        return !is_null($this->blockedMessage);
    }

    /**
     * Factory for SDK Models (from KeeperService).
     *
     * @param SdkAddon $sdkAddon
     * @return Addon
     */
    public static function createFromSdk(SdkAddon $sdkAddon): Addon
    {
        return (new static())
            ->setId((int)$sdkAddon->id)
            ->setName($sdkAddon->name)
            ->setIntro($sdkAddon->intro)
            ->setDescription($sdkAddon->description)
            ->setEnabled((bool)$sdkAddon->enabled)
            ->setAlias($sdkAddon->alias)
            ->setRequestEnable((bool)$sdkAddon->requestEnable)
            ->setParameters($sdkAddon->parameters);
    }

    protected function getJsonDecodedParams(): array
    {
        if (!\is_string($this->parameters)) {
            return \is_array($this->parameters) ? $this->parameters : [];
        }

        // if the params aren't changed from last time, return previous encoded value
        if (isset($this->jsonDecodedParamsMap[$this->parameters])) {
            return $this->jsonDecodedParamsMap[$this->parameters];
        }

        $result = json_decode($this->parameters, true);
        $this->jsonDecodedParamsMap = [$this->parameters => $result];
        return $result;
    }
}
