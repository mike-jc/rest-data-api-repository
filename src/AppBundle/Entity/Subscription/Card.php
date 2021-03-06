<?php

namespace AppBundle\Entity\Subscription;

class Card extends Source {
    /**
     * @var string card token generated by Stripe
     */
    private $number;
    /**
     * @var string cvc number
     */
    private $cvc;
    /**
     * @var string card brand (Visa, MasterCard etc.)
     */
    private $brand;

    /**
     * @return string
     */
    public function getNumber() {
        return $this->number;
    }

    /**
     * @param string $number
     * @return $this
     */
    public function setNumber($number) {
        $this->number = $number;
        return $this;
    }

    /**
     * @return string
     */
    public function getCvc() {
        return $this->cvc;
    }

    /**
     * @param string $cvc
     * @return $this
     */
    public function setCvc($cvc) {
        $this->cvc = $cvc;
        return $this;
    }

    /**
     * @return string
     */
    public function getBrand() {
        return $this->brand;
    }

    /**
     * @param string $brand
     * @return $this
     */
    public function setBrand($brand) {
        $this->brand = $brand;
        return $this;
    }
}
