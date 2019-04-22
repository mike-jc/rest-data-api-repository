<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * Setting of product
 *
 * @Api\Entity(endPoint="settings")
 */
class Setting extends BaseEntity {
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var string setting name
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     * @AppAssert\Uid()
     */
    private $name;
    /**
     * @var string setting value
     * Warning: use filtered_symbols option if open this field for user via TextType to prevent XSS attacks
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     */
    private $value;
    /**
     * @var string setting scope
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Uid()
     */
    private $scope;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
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
    public function getValue() {
        return $this->value;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getScope() {
        return $this->scope;
    }

    /**
     * @param string $scope
     * @return $this
     */
    public function setScope($scope) {
        $this->scope = $scope;
        return $this;
    }
}