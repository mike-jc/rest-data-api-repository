<?php

namespace AppBundle\Entity\Subscription;

use AppBundle\Validator\Constraints as AppAssert;

class SepaDebit extends Source {
    /**
     * @var string bank account number
     */
    private $iban;
    /**
     * @var string name of the bank account owner
     * @AppAssert\HumanName()
     */
    protected $ownerName;
    /**
     * @var string
     */
    protected $bankCode;
    /**
     * @var string
     */
    protected $mandateUrl;

    /**
     * @return string
     */
    public function getIban() {
        return $this->iban;
    }

    /**
     * @param string $iban
     * @return $this
     */
    public function setIban($iban) {
        $this->iban = $iban;
        return $this;
    }

    /**
     * @return string
     */
    public function getOwnerName() {
        return $this->ownerName;
    }

    /**
     * @param string $ownerName
     * @return $this
     */
    public function setOwnerName($ownerName) {
        $this->ownerName = $ownerName;
        return $this;
    }

    /**
     * @return string
     */
    public function getBankCode() {
        return $this->bankCode;
    }

    /**
     * @param string $bankCode
     * @return $this
     */
    public function setBankCode($bankCode) {
        $this->bankCode = $bankCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getMandateUrl() {
        return $this->mandateUrl;
    }

    /**
     * @param string $mandateUrl
     * @return $this
     */
    public function setMandateUrl($mandateUrl) {
        $this->mandateUrl = $mandateUrl;
        return $this;
    }
}
