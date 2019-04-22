<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * Option of custom field (e.g., dropdown option)
 *
 * @Api\Entity(endPoint="custom_field_options")
 */
class CustomFieldOption extends BaseEntity {
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var string value of an option
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     * @AppAssert\Label()
     */
    private $value;

    /**
     * @var CustomField field that has this option
     *
     * @Api\Property(entity="CustomField", writeOnly=true)
     */
    private $field;

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
     * @return CustomField
     */
    public function getField() {
        return $this->field;
    }

    /**
     * @param CustomField $field
     * @return $this
     */
    public function setField(CustomField $field = null) {
        $this->field = $field;
        return $this;
    }
}
