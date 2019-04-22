<?php

namespace AppBundle\Entity\Subscription;

use Symfony\Component\Validator\Constraints as Assert;

class InvoiceSettings {
    /**
     * @var bool flag indicating if we should send invoices to billing email
     *
     * @Assert\Choice({"0", "1"})
     */
    private $send;
    /**
     * @var string
     */
    private $reference;
    /**
     * @var string email where invoices are sent to
     */
    private $email;

    /**
     * @return bool
     */
    public function isSend() {
        return $this->send;
    }

    /**
     * @param bool $send
     * @return $this
     */
    public function setSend($send) {
        $this->send = $send;
        return $this;
    }

    /**
     * @return string
     */
    public function getReference() {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return $this
     */
    public function setReference($reference) {
        $this->reference = $reference;
        return $this;
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
}
