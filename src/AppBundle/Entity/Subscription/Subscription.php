<?php

namespace AppBundle\Entity\Subscription;

use AppBundle\Exception\SubscriptionException;
use AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Describes basic subscription parameters
 *
 * @AppAssert\SubscriptionConstraint()
 */
class Subscription extends SubscriptionBase {
    /**
     * @var string basic plan type
     *
     * @Assert\NotBlank()
     * @Assert\Choice({
     *     "monthly", "monthly:basic", "monthly:app-sumo", "monthly:standard", "monthly:premium",
     *     "monthly:basic:usd", "monthly:app-sumo:usd", "monthly:standard:usd", "monthly:premium:usd",
     *     "annually", "annually:basic", "annually:app-sumo", "annually:standard", "annually:premium",
     *     "annually:basic:usd", "annually:app-sumo:usd", "annually:standard:usd", "annually:premium:usd"
     * })
     */
    private $planType = self::DEFAULT_PLAN;
    /**
     * @var int number of users in this company
     *
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(0)
     */
    private $users;
    /**
     * @var string email where invoices are sent to
     */
    private $billingEmail;
    /**
     * @var string payment method
     * @Assert\Choice({"card", "sepa"})
     */
    private $paymentMethod = 'card';
    /**
     * @var Source
     *
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $paymentSource;
    /**
     * @var bool whether payment source requires verification (e.g., 3D Secure)
     * Set by subscription.js based on data of Stripe source
     */
    private $paymentSourceVerified = true;
    /**
     * @var Coupon discount coupon on the side of external payment system
     */
    private $coupon;
    /**
     * @var string subscription id on the side of external payment system
     */
    private $externalId;
    /**
     * @var \DateTime date when next charge will be done
     */
    private $nextChargeDate;
    /**
     * @var bool admin has stopped subscription
     */
    private $stopped = false;

    public function __clone() {
        if ($this->paymentSource) {
            $this->paymentSource = clone $this->paymentSource;
        }
        if ($this->coupon) {
            $this->coupon = clone $this->coupon;
        }
        if ($this->nextChargeDate) {
            $this->nextChargeDate = clone $this->nextChargeDate;
        }
    }

    /**
     * @return string
     */
    public function getPlanType() {
        return $this->planType;
    }

    public function getPlanParams() {
        return self::getPlanParamsByType($this->getPlanType());
    }

    /**
     * @param string $planType
     * @return $this
     */
    public function setPlanType($planType) {
        $this->planType = $planType;
        return $this;
    }

    /**
     * @return string
     */
    public function getGeneralType() {
        return self::getGeneralPlanTypeFromType($this->getPlanType());
    }

    /**
     * @return string
     */
    public function getPlanTitle() {
        return self::getPlanTitleFromType($this->getPlanType());
    }

    /**
     * @param string $type
     * @param bool $takeOldPlanAsPremium consider plans from old subscription system as Premium plans
     * @return bool
     */
    public function isGeneralType($type, $takeOldPlanAsPremium = false) {
        $generalPlan = self::getGeneralPlanTypeFromType($this->getPlanType());

        if (!$generalPlan && $takeOldPlanAsPremium) {
            return self::PREMIUM_GENERAL_PLAN == $type;
        } else {
            return $generalPlan == $type;
        }
    }

    /**
     * @return bool
     */
    public function isAppSumo() {
        return $this->isGeneralType(self::APPSUMO_GENERAL_PLAN);
    }

    /**
     * @return int
     */
    public function getUsers() {
        return $this->users;
    }

    /**
     * @param int $users
     * @return $this
     */
    public function setUsers($users) {
        $this->users = $users;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingEmail() {
        return $this->billingEmail;
    }

    /**
     * @param string $billingEmail
     * @return $this
     */
    public function setBillingEmail($billingEmail) {
        $this->billingEmail = $billingEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethod() {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod) {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @return Source|null
     */
    public function getPaymentSource() {
        return $this->paymentSource;
    }

    /**
     * @param Source|null $paymentSource
     * @return $this
     */
    public function setPaymentSource($paymentSource = null) {
        $this->paymentSource = $paymentSource;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPaymentSourceVerified() {
        return $this->paymentSourceVerified;
    }

    /**
     * @param bool $paymentSourceVerified
     * @return $this
     */
    public function setPaymentSourceVerified($paymentSourceVerified) {
        $this->paymentSourceVerified = $paymentSourceVerified;
        return $this;
    }

    /**
     * @return Coupon
     */
    public function getCoupon() {
        return $this->coupon;
    }

    /**
     * @return bool
     */
    public function hasCoupon() {
        return $this->getCoupon() && $this->getCoupon()->getExternalId();
    }

    /**
     * @param Coupon|null $coupon
     * @return $this
     */
    public function setCoupon(Coupon $coupon = null) {
        $this->coupon = $coupon;
        return $this;
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
     * @return \DateTime
     */
    public function getNextChargeDate() {
        return $this->nextChargeDate;
    }

    /**
     * @param \DateTime $nextChargeDate
     * @return $this
     */
    public function setNextChargeDate($nextChargeDate) {
        $this->nextChargeDate = $nextChargeDate;
        return $this;
    }

    /**
     * @param int $t
     */
    public function setNextChargeDateFromTimeStamp($t) {
        $this->setNextChargeDate((new \DateTime())->setTimestamp($t));
    }

    /**
     * float
     */
    public function getChargeAmount() {
        $plans = self::getPlans();
        // for one user
        $unitCost = isset($plans[$this->getPlanType()]) ? $plans[$this->getPlanType()]['totalCost'] : 0;
        // total amount
        return $unitCost * $this->getUsers();
    }

    /**
     * @return bool
     */
    public function isStopped() {
        return $this->stopped;
    }

    /**
     * @param bool $stopped
     * @return $this
     */
    public function setStopped($stopped) {
        $this->stopped = $stopped;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive() {
        return $this->getExternalId() && !$this->isStopped();
    }

    /**
     * Clear all specific information (e.g., before resubscribing)
     */
    public function clear() {
        $this->setPaymentSource(null);
        $this->setPaymentSourceVerified(true);
        $this->setCoupon(null);
    }

    /**
     * @return float
     * @throws SubscriptionException
     */
    public function getFinalAmount() {
        $plans = self::getPlans();

        if (isset($plans[$this->getPlanType()])) {
            $plan = $plans[$this->getPlanType()];

            // if discount coupon is aplied only once it means
            // it's been applied already during previous charged period
            if ($this->getCoupon() && $this->getCoupon()->isFullInfo() && !$this->getCoupon()->isOnce()) {

                // get discount of month's cost and calculate new month's cost for current plan
                if ($this->getCoupon()->getPercent()) {
                    $discount = round($plan['cost'] * ($this->getCoupon()->getPercent() / 100), 2);
                } else {
                    $discount = $plan['cost'] - $this->getCoupon()->getAmount();
                }
                $newMonthAmount = $plan['cost'] - $discount;

                // customer has discount that has end date
                if ($this->getCoupon()->isRepeating()) {
                    $couponEnd = $this->getCoupon()->getEnd()->getTimestamp();
                    $periodEnd = $this->getNextChargeDate()->getTimestamp();

                    if ($periodEnd <= $couponEnd) {
                        $unitCost = $newMonthAmount * $plan['durationInMonths'];

                    } else {
                        $monthInSeconds = 60 * 60 * 24 * 30;
                        $notDiscountMonths = floor(($periodEnd - $couponEnd) / $monthInSeconds);

                        if ($notDiscountMonths < $plan['durationInMonths']) {
                            $discountMonths = $plan['durationInMonths'] - $notDiscountMonths;
                            $unitCost = $newMonthAmount * $discountMonths + $plan['cost'] * $notDiscountMonths;

                        } else {
                            // discount is ended already
                            $unitCost = $plan['totalCost'];
                        }
                    }

                }
                // customer has discount that lasts forever
                else {
                    $unitCost = $newMonthAmount * $plan['durationInMonths'];
                }

                return $unitCost * $this->getUsers();
            }

            // customer should be charged for the total plan's cost if there's no discount now
            return $this->getChargeAmount();
        }

        return 0;
    }

    /**
     * @return string
     */
    public function getCurrency() {
        return self::getPlanCurrencyFromType($this->getPlanType());
    }

    /**
     * @return string
     */
    public function getInterval() {
        return self::getPlanIntervalFromType($this->getPlanType());
    }

    /**
     * @param string $planType
     * @param string $paymentMethod
     * @param string $sourceId
     * @param string $billingEmail
     * @param string $couponId
     * @return Subscription
     */
    static public function make($planType, $paymentMethod, $sourceId, $billingEmail = null, $couponId = null) {
        $subscription = new self;
        $subscription->setPlanType($planType);
        $subscription->setPaymentMethod($paymentMethod);
        $subscription->setBillingEmail($billingEmail);

        $source = null;
        if ($paymentMethod == 'card') {
            $source = new Card();
            $source->setExternalId($sourceId);
        } else {
            $source = new SepaDebit();
            $source->setExternalId($sourceId);
        }
        $subscription->setPaymentSource($source);

        if ($couponId) {
            $coupon = new Coupon();
            $coupon->setExternalId($couponId);
            $subscription->setCoupon($coupon);
        }

        return $subscription;
    }
}
