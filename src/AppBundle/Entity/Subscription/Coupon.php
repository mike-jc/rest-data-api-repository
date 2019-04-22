<?php

namespace AppBundle\Entity\Subscription;

use AppBundle\Helper\DatetimeTrait;

class Coupon {
    use DatetimeTrait;

    /**
     * @var string coupon id on the side of external payment system
     */
    private $externalId;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var float fixed amount of coupon
     */
    private $amount;
    /**
     * @var float amount of coupon in percent
     */
    private $percent;
    /**
     * @var string once|repeating|forever
     */
    private $durationType;
    /**
     * @var int duration in months if duration type is `repeating`
     */
    private $durationInMonths;
    /**
     * @var \DateTime date when discount end if duration type is `repeating`
     */
    private $end;
    /**
     * @var int maximum number of times this coupon can be redeemed
     */
    private $maxRedemptions;
    /**
     * @var int Unix timestamp after which the coupon can no longer be redeemed
     */
    private $redeemBy;
    /**
     * @var array meta data
     */
    private $metaData;
    /**
     * @var bool object info is fully filled from the Stripe data (not only id)
     */
    private $fullInfo;

    static public $durationTypes = ['once', 'repeating', 'forever'];

    public function __clone() {
        if ($this->end) {
            $this->end = clone $this->end;
        }
    }

    /**
     * @return string
     */
    public function getExternalId() {
        return $this->externalId;
    }

    /**
     * @param string $externalId
     * @return $this
     */
    public function setExternalId($externalId) {
        $this->externalId = $externalId;
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
     * @return float
     */
    public function getPercent() {
        return $this->percent;
    }

    /**
     * @param float $percent
     * @return $this
     */
    public function setPercent($percent) {
        $this->percent = $percent;
        return $this;
    }

    /**
     * @return string
     */
    public function getDurationType() {
        return $this->durationType;
    }

    /**
     * @param string $durationType
     * @return $this
     */
    public function setDurationType($durationType) {
        $this->durationType = $durationType;
        return $this;
    }

    /**
     * @return int
     */
    public function getDurationInMonths() {
        return $this->durationInMonths;
    }

    /**
     * @param int $durationInMonths
     * @return $this
     */
    public function setDurationInMonths($durationInMonths) {
        $this->durationInMonths = $durationInMonths;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd() {
        return $this->end;
    }

    /**
     * @param \DateTime|string|int|null $end
     * @return $this
     * @throws \AppBundle\Exception\Exception
     */
    public function setEnd($end) {
        $this->end = $this->makeDateTime($end);
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxRedemptions() {
        return $this->maxRedemptions;
    }

    /**
     * @param int $maxRedemptions
     * @return $this
     */
    public function setMaxRedemptions($maxRedemptions) {
        $this->maxRedemptions = $maxRedemptions;
        return $this;
    }

    /**
     * @return int
     */
    public function getRedeemBy() {
        return $this->redeemBy;
    }

    /**
     * @param int $redeemBy
     * @return $this
     */
    public function setRedeemBy($redeemBy) {
        $this->redeemBy = $redeemBy;
        return $this;
    }

    /**
     * @return array
     */
    public function getMetaData() {
        return $this->metaData;
    }

    /**
     * @param array $metaData
     * @return $this
     */
    public function setMetaData(array $metaData) {
        $this->metaData = $metaData;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFullInfo() {
        return $this->fullInfo;
    }

    /**
     * @param bool $fullInfo
     * @return $this
     */
    public function setFullInfo($fullInfo) {
        $this->fullInfo = $fullInfo;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOnce() {
        return $this->getDurationType() == 'once';
    }

    /**
     * @return bool
     */
    public function isRepeating() {
        return $this->getDurationType() == 'repeating';
    }

    /**
     * @return bool
     */
    public function isForever() {
        return $this->getDurationType() == 'forever';
    }

    /**
     * @return string
     */
    public function getValueText() {
        if ($this->getPercent()) {
            return $this->getPercent() ."%";
        } else {
            return number_format($this->getAmount(), 2) ." ". $this->getCurrency();
        }
    }
}