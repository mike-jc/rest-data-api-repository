<?php

namespace AppBundle\Entity\Subscription;

use AppBundle\Exception\SubscriptionException;

class SubscriptionBase {
    const APPSUMO_GENERAL_PLAN = 'app-sumo';
    const STANDARD_GENERAL_PLAN = 'standard';
    const PREMIUM_GENERAL_PLAN = 'premium';

    const APPSUMO_DISCOUNT_PERCENT = 25;
    const APPSUMO_ONETIME_PURCHASE_DESCRIPTION = 'One-time purchase for AppSumo';

    const DEFAULT_PLAN = 'annually:standard';  // in eur
    const DEFAULT_PLAN_APPSUMO = 'annually:app-sumo';

    static public $intervals = [
        'monthly' => ['unit' => 'month', 'inMonths' => 1],
        'annually' => ['unit' => 'year', 'inMonths' => 12],
    ];

    static public $currencies = [
        'eur' => '',
        'usd' => 'usd'
    ];

    /*
     * Array: [ Addon alias => Subscription plan type ]
     * This list describes at which subscription plan a specific addon becomes available.
     */
    static public $upgradableFeatures = [
        'reviews' => 'basic',
        'wrapups' => 'basic',
        'timezone' => 'basic',
        'advieskeuze' => 'basic',
        'streaming' => 'basic', // Audio by Phone
        'recordings' => 'basic',
        'gcalendar' => 'standard',
        'livenow' => 'standard',
        'integrations.zapier' => 'standard',
        'email-customisation' => 'premium',
        'review-redirect' => 'premium', // Custom endpage redirect
        'smsnotifications' => 'premium'
    ];

    /*
     * Blocked/paid feature ids are from update_plan.html.twig
     *
     * Upgradable feature ids are the aliases of our Addons.
     * These lists describe which addons will become available on the higher subscription plans.
     *
     * Warning: if new plan type is added, that should be done here and in SubscriptionBase.php of signup service!
     */
    static public $planTypes = [
        '' => [ // old plan system
            'title' => 'Premium',
            'blockedFeatures' => [
                'branding.24sessions',
            ]
        ],
        'basic' => [
            'title' => 'Basic',
            'blockedFeatures' => [
                'branding.24sessions',
                'meeting-types.booking-forms',
                'meeting-types.live-now',
                'addons.gcalendar',
                'addons.email-customisation',
                'addons.custom-redirect',
                'addons.sms-reminders',
                'integrations.zapier'
            ],
            'upgradableFeatures' => [
                'gcalendar' => 'standard',
                'livenow' => 'standard',
                'email-customisation' => 'premium',
                'review-redirect' => 'premium',
                'smsnotifications' => 'premium'
            ]
        ],
        'app-sumo' => [
            'title' => 'AppSumo',
            'free' => true,
            'notChoosable' => true,
            'blockedFeatures' => [
                'branding.24sessions',
                'addons.recordings',
                'addons.email-customisation',
                'addons.custom-redirect',
                'addons.sms-reminders'
            ],
            'paidFeatures' => [
                'addons.recordings',
            ],
            'upgradableFeatures' => [
                'email-customisation' => 'premium',
                'review-redirect' => 'premium',
                'smsnotifications' => 'premium'
            ],
        ],
        'standard' => [
            'title' => 'Standard',
            'blockedFeatures' => [
                'branding.24sessions',
                'addons.email-customisation',
                'addons.sms-reminders',
            ],
            'upgradableFeatures' => [
                'email-customisation' => 'premium',
                'review-redirect' => 'premium',
                'smsnotifications' => 'premium'
            ]
        ],
        'premium' => [
            'title' => 'Premium',
            'blockedFeatures' => [] // all services are enabled
        ],
    ];

    static public $oneTimePurchases = [
        'app-sumo' => [
            'features' => [
                'branding.24sessions',
                'addons.custom-redirect',
                'addons.email-customisation',
            ],
            'pageRoute' => 'appsumo_one_time_purchase',
            'price' => [
                'amount' => 49,
                'currency' => 'usd',
            ],
        ]
    ];

    static public $planCosts = [
        'eur' => [
            'monthly' => ['' => 36.00, 'basic' => 24.00, 'app-sumo' => 32.00, 'standard' => 32.00, 'premium' => 49.00],
            'annually' => ['' => 29.00, 'basic' => 19.00, 'app-sumo' => 26.00, 'standard' => 26.00, 'premium' => 39.00],
        ],
        'usd' => [
            'monthly' => ['basic' => 26.00, 'app-sumo' => 36.00, 'standard' => 36.00, 'premium' => 55.00],
            'annually' => ['basic' => 21.00, 'app-sumo' => 30.00, 'standard' => 30.00, 'premium' => 45.00],
        ],
    ];

    static public $paymentMethods = [
        'Credit card' => 'card',
        'SEPA Direct Debit' => 'sepa',
    ];

    /**
     * @return array
     * @throws SubscriptionException
     */
    static public function getPlans() {
        static $planList;

        if (is_null($planList)) {
            $planList = [];

            self::iterateOnPlanCosts(function($currency, $interval, $type, $cost) use (&$planList) {
                $id = $interval;
                if ($type) {
                    $id .= ":$type";
                }
                if (self::$currencies[$currency]) {
                    $id .= ":". self::$currencies[$currency];
                }

                $planList[$id] = [
                    'type' => $type,
                    'title' => self::$planTypes[$type]['title'],
                    'interval' => self::$intervals[$interval]['unit'],
                    'durationInMonths' => self::$intervals[$interval]['inMonths'],
                    'currency' => $currency,
                    'cost' => $cost,
                    'totalCost' => $cost * self::$intervals[$interval]['inMonths'],
                    'costTitle' => 'user/mo',
                ];
            });
        }

        return $planList;
    }

    /**
     * @return array
     * @throws SubscriptionException
     */
    static public function getPlansByType() {
        static $byType;

        if (is_null($byType)) {
            $byType = [];

            self::iterateOnPlanCosts(function($currency, $interval, $type, $cost) use (&$byType) {
                if ($type) {
                    if (!isset($byType[$type])) {
                        $byType[$type] = [
                            'title' => self::$planTypes[$type]['title'],
                            'free' => !empty(self::$planTypes[$type]['free']),
                        ];
                    }
                    $byType[$type][$interval][$currency] = $cost;
                }
            });
        }

        return $byType;
    }

    /**
     * Substitute Premium plan cost for appropriate interval by the old plan if it's current
     * (old plan system was in eur only and now is considered as Premium plans)
     *
     * @param array $plansByType
     * @param string $currentOldPlan
     * @return array
     * @throws SubscriptionException
     */
    static public function injectOldPlan(array $plansByType, $currentOldPlan) {

        $currentInterval = self::getPlanIntervalFromType($currentOldPlan);

        if (!isset(self::$intervals[$currentInterval])) {
            throw new SubscriptionException("Unknown old subscription interval $currentOldPlan");
        }
        if (!isset(self::$planCosts['eur'][$currentInterval][''])) {
            throw new SubscriptionException("Unknown cost for old subscription with interval $currentOldPlan");
        }

        $plansByType[self::PREMIUM_GENERAL_PLAN][$currentInterval]['eur'] = self::$planCosts['eur'][$currentInterval][''];

        return $plansByType;
    }

    /**
     * @param string $group
     * @param string $type
     * @return string
     */
    static public function makePlanId($group, $type) {
        return "{$group}:{$type}";
    }

    /**
     * @param string $planId
     * @return string
     */
    static public function getPlanTypeFromId($planId) {
        $idParts = explode(':', $planId);
        array_shift($idParts); // type is all except of the first part of the id (which is its group)
        return trim(implode(':', $idParts));
    }

    /**
     * @param string $planType
     * @return string
     */
    static public function getPlanCurrencyFromType($planType) {
        $typeParts = explode(':', $planType);

        // currency is the last part of plan type but it missed for euro
        $currency = count($typeParts) > 1 ? trim(array_pop($typeParts)) : '';
        return isset(self::$currencies[$currency]) ? self::$currencies[$currency] : 'eur';
    }

    /**
     * @param string $planType
     * @return string
     */
    static public function getPlanIntervalFromType($planType) {
        $typeParts = explode(':', $planType);

        // interval is the first part of plan type
        $interval = trim(array_shift($typeParts));
        return isset(self::$intervals[$interval]) ? $interval : 'annually';
    }

    /**
     * @param string $planType
     * @return string
     */
    static public function getGeneralPlanTypeFromType($planType) {
        $typeParts = explode(':', $planType);

        // plan type has the following structure (see getPlans()):
        // <interval>:<general type>:(<currency>).
        // For old scheme (before AppSumo) type consists of only <interval>
        return isset($typeParts[1]) ? trim($typeParts[1]) : '';
    }

    /**
     * @param string $planType
     * @return string
     */
    static public function getPlanTitleFromType($planType) {
        $generalType = self::getGeneralPlanTypeFromType($planType);
        return !empty(self::$planTypes[$generalType]['title']) ? self::$planTypes[$generalType]['title'] : '';
    }

    /**
     * @param string $type
     * @return mixed
     * @throws SubscriptionException
     */
    static public function getPlanParamsByType($type) {
        $plans = self::getPlans();
        if (empty($plans[$type])) {
            throw new SubscriptionException("Unknown subscription plan");
        }
        $params = $plans[$type];
        $params['id'] = self::makePlanId('basic', $type);
        return $params;
    }

    /**
     * @param callable $processor
     * @throws SubscriptionException
     */
    static private function iterateOnPlanCosts(callable $processor) {

        foreach (self::$planCosts as $currency => $currencyPlans) {
            if (!isset(self::$currencies[$currency])) {
                throw new SubscriptionException("Unknown subscription currency $currency");
            }

            foreach ($currencyPlans as $interval => $intervalPlans) {
                if (!isset(self::$intervals[$interval])) {
                    throw new SubscriptionException("Unknown subscription interval $interval");
                }

                foreach ($intervalPlans as $type => $cost) {
                    if (!isset(self::$planTypes[$type])) {
                        throw new SubscriptionException("Unknown subscription plan type $type");
                    }

                    $processor($currency, $interval, $type, $cost);
                }
            }
        }
    }
}
