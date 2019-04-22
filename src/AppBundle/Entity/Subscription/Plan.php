<?php

namespace AppBundle\Entity\Subscription;

use AppBundle\Exception\SubscriptionException;
use Symfony\Component\Validator\Constraints as Assert;

class Plan {
    const DEFAULT_GROUP = 'basic';
    const DEFAULT_INTERVAL_COUNT = 1;
    const DEFAULT_CURRENCY = 'eur';
    const DEFAULT_QUANTITY = 1;

    /**
     * @var string
     */
    private $id;
    /**
     * @var string group of plan
     * e.g. 'basic' for basic subscription, 'recordings' for recordings addon
     */
    private $group = self::DEFAULT_GROUP;
    /**
     * @var string interval
     * @Assert\Choice({"day", "week", "month", "year"})
     */
    private $interval;
    /**
     * @var int
     * @Assert\GreaterThan(0)
     */
    private $intervalCount = self::DEFAULT_INTERVAL_COUNT;
    /**
     * @var float
     * @Assert\GreaterThan(0)
     */
    private $amount;
    /**
     * @var string currency (three letters)
     */
    private $currency = self::DEFAULT_CURRENCY;
    /**
     * @var int default plan quantity
     * (the result total for recurrent payment is amount * quantity)
     *
     * @Assert\GreaterThan(0)
     */
    private $quantity = self::DEFAULT_QUANTITY;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $description = '';
    /**
     * @var string
     */
    private $generalType = '';

    static private $intervals = ['day', 'week', 'month', 'year'];

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroup() {
        return $this->group;
    }

    /**
     * @param string $group
     * @return $this
     */
    public function setGroup($group) {
        $this->group = $group;
        return $this;
    }

    /**
     * @return string
     */
    public function getInterval() {
        return $this->interval;
    }

    /**
     * @param string $interval
     * @return $this
     */
    public function setInterval($interval) {
        $this->interval = $interval;
        return $this;
    }

    /**
     * @return bool
     */
    public function checkInterval() {
        return in_array($this->getInterval(), self::$intervals);
    }

    /**
     * @return int
     */
    public function getIntervalCount() {
        return $this->intervalCount;
    }

    /**
     * @param int $intervalCount
     * @return $this
     */
    public function setIntervalCount($intervalCount) {
        $this->intervalCount = $intervalCount;
        return $this;
    }

    /**
     * @return bool
     */
    public function checkIntervalCount() {
        if ($this->getInterval() == 'day' && $this->getIntervalCount() > 365) {
            return false;
        } elseif ($this->getInterval() == 'week' && $this->getIntervalCount() > 52) {
            return false;
        } elseif ($this->getInterval() == 'month' && $this->getIntervalCount() > 12) {
            return false;
        } elseif ($this->getIntervalCount() > 1) {
            return false;
        }
        return true;
    }

    /**
     * @return float
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount) {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency) {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity() {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return $this
     */
    public function setQuantity($quantity) {
        $this->quantity = $quantity;
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
     * @return string
     */
    public function getGeneralType() {
        return $this->generalType;
    }

    /**
     * @param string $generalType
     * @return $this
     */
    public function setGeneralType($generalType) {
        $this->generalType = $generalType;
        return $this;
    }

    /**
     * @param array $params
     * @return self
     * @throws SubscriptionException
     */
    static function make(array $params) {
        if (empty($params['id'])) {
            throw new SubscriptionException("Invalid params for plan: id is absent");
        }
        if (empty($params['interval'])) {
            throw new SubscriptionException("Invalid params for plan: interval is absent");
        }
        if (empty($params['name']) && empty($params['title'])) {
            throw new SubscriptionException("Invalid params for plan: name/title is absent");
        }
        if (empty($params['amount']) && empty($params['totalCost'])) {
            throw new SubscriptionException("Invalid params for plan: amount/totalCost is absent");
        }

        $plan = new self;
        $plan->setId($params['id']);
        $plan->setGroup(!empty($params['group']) ? $params['group'] : self::DEFAULT_GROUP);
        $plan->setName(!empty($params['name']) ? $params['name'] : $params['title']);
        $plan->setAmount(!empty($params['amount']) ? (float)$params['amount'] : (float)$params['totalCost']);
        $plan->setCurrency(!empty($params['currency']) ? $params['currency'] : self::DEFAULT_CURRENCY);
        $plan->setInterval($params['interval']);
        $plan->setIntervalCount(!empty($params['intervalCount']) ? (int)$params['intervalCount'] : self::DEFAULT_INTERVAL_COUNT);
        $plan->setQuantity(!empty($params['quantity']) ? (int)$params['quantity'] : self::DEFAULT_QUANTITY);
        $plan->setGeneralType(!empty($params['type']) ? $params['type'] : '');
        return $plan;
    }
}
