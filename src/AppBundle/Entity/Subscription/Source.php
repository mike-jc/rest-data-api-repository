<?php

namespace AppBundle\Entity\Subscription;

use Symfony\Component\Validator\Constraints as Assert;

class Source {
    /**
     * @var string external id (on payment system's side)
     *
     * @Assert\NotBlank()
     */
    private $externalId;
    /**
     * @var string Two-letter ISO code of source's country
     */
    private $countryCode;
    /**
     * @var string Three-letter ISO code of currency
     */
    private $currencyCode;
    /**
     * @var string last for digits for source number (card number, bank account etc.)
     */
    private $lastFourDigits;
    /**
     * @var \DateTime expiration date
     */
    private $expiry;

    static private $countryCurrencies = [
        'AS' => 'usd',
        'IO' => 'usd',
        'GU' => 'usd',
        'MH' => 'usd',
        'FM' => 'usd',
        'MP' => 'usd',
        'PW' => 'usd',
        'PR' => 'usd',
        'TC' => 'usd',
        'US' => 'usd',
        'UM' => 'usd',
        'VI' => 'usd',
        'AD' => 'eur',
        'AT' => 'eur',
        'BE' => 'eur',
        'CY' => 'eur',
        'EE' => 'eur',
        'FI' => 'eur',
        'FR' => 'eur',
        'GF' => 'eur',
        'TF' => 'eur',
        'DE' => 'eur',
        'GR' => 'eur',
        'GP' => 'eur',
        'IE' => 'eur',
        'IT' => 'eur',
        'LV' => 'eur',
        'LT' => 'eur',
        'LU' => 'eur',
        'MT' => 'eur',
        'MQ' => 'eur',
        'YT' => 'eur',
        'MC' => 'eur',
        'ME' => 'eur',
        'NL' => 'eur',
        'PT' => 'eur',
        'RE' => 'eur',
        'PM' => 'eur',
        'SM' => 'eur',
        'SK' => 'eur',
        'SI' => 'eur',
        'ES' => 'eur',
    ];

    public function __clone() {
        if ($this->expiry) {
            $this->expiry = clone $this->expiry;
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
     * @return mixed
     */
    public function getCountryCode() {
        return $this->countryCode;
    }

    /**
     * @param mixed $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode) {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrencyCode() {
        return $this->currencyCode;
    }

    /**
     * @param mixed $currencyCode
     * @return $this
     */
    public function setCurrencyCode($currencyCode) {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastFourDigits() {
        return $this->lastFourDigits;
    }

    /**
     * @param string $lastFourDigits
     * @return $this
     */
    public function setLastFourDigits($lastFourDigits) {
        $this->lastFourDigits = $lastFourDigits;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiry() {
        return $this->expiry;
    }

    /**
     * @param \DateTime $expiry
     * @return $this
     */
    public function setExpiry($expiry) {
        $this->expiry = $expiry;
        return $this;
    }

    /**
     * @param int $month
     * @param int $year
     * @return $this
     */
    public function setExpiryByMonthAndYear($month, $year) {
        $expiry = new \DateTime();
        $expiry->setDate($year, $month, 1);
        $expiry->setTime(0, 0, 0);

        // expiration happens AFTER this month
        $expiry->modify('+1 month');

        $this->expiry = $expiry;
        return $this;
    }

    public function normalizeCurrencyCodeIfNecessary() {

        if (is_null($this->getCurrencyCode())) {
            $countryCode = strtoupper($this->getCountryCode());

            if (isset(self::$countryCurrencies[$countryCode])) {
                $this->setCurrencyCode(self::$countryCurrencies[$this->getCountryCode()]);
            } else {
                $this->setCurrencyCode('');
            }
        }
    }
}
