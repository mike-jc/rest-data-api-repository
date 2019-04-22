<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;

/**
 *
 * @Api\Entity(endPoint="public_form_logins")
 */
class PublicFormLogin extends BaseEntity {

    const FORM_JOIN = 1;
    const FORM_BOOKING = 2;

    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var string
     *
     * @Api\Property(type="string")
     * @Assert\NotBlank()
     */
    private $ip;
    /**
     * @var integer
     *
     * @Api\Property(type="integer")
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     */
    private $formId;
    /**
     * @var \DateTime date when login has been created
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $created;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param integer $id
     * @return $this
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getIp() {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return $this
     */
    public function setIp($ip) {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return int
     */
    public function getFormId() {
        return $this->formId;
    }

    /**
     * @param int $formId
     * @return $this
     * @throws \Exception
     */
    public function setFormId($formId) {
        if (!in_array($formId, [self::FORM_BOOKING, self::FORM_JOIN])) {
            throw new \Exception('Invalid form id');
        }
        
        $this->formId = $formId;
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
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }
}
