<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * Custom field in booking form
 *
 * @Api\Entity(endPoint="custom_fields")
 */
class CustomField extends BaseEntity {
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var string field type
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     * @Assert\Choice({"input", "checkbox", "checkboxes", "radiobuttons", "textarea", "dropdown"})
     */
    private $type = 'input';
    /**
     * @var string field label
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     * @AppAssert\Label()
     */
    private $label;
    /**
     * @var string field value
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @AppAssert\Label()
     */
    private $value;
    /**
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $required = false;
    /**
     * @var int order of this field among other custom fields in the booking form
     *
     * @Api\Property(type="integer")
     * @Assert\Regex("/^[0-9]+$/")
     */
    private $order;

    /**
     * @var BookingForm booking form that has this field
     *
     * @Api\Property(entity="BookingForm", writeOnly=true)
     */
    private $form;
    /**
     * @var EntityCollection<CustomFieldOption> options of this field
     *
     * @Api\Property(entity="CustomFieldOption")
     * @Api\Collection()
     */
    private $options;

    static public $types = [
        'input' => 'Input',
        'checkbox' => 'Checkbox',
        'dropdown' => 'Select',
    ];

    public function __construct() {
        $this->options = new EntityCollection();
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    public function getFormId() {
        return "custom_{$this->id}";
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
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTypeName() {
        return !empty(self::$types[$this->getType()]) ? self::$types[$this->getType()] : ucfirst($this->getType());
    }

    /**
     * @param string $type
     * @return $this;
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label) {
        $this->label = $label;
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
     * @return mixed
     */
    public function getRequired() {
        return $this->required;
    }

    /**
     * @param mixed $required
     * @return $this
     */
    public function setRequired($required) {
        $this->required = $required;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * @param int $order
     * @return $this
     */
    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }

    /**
     * @return BookingForm
     */
    public function getForm() {
        return $this->form;
    }

    /**
     * @param BookingForm $form
     * @return $this
     */
    public function setForm(BookingForm $form = null) {
        $this->form = $form;
        return $this;
    }

    /**
     * @return EntityCollection<CustomFieldOption>
     */
    public function getOptions() {
        return $this->canHaveOptions() ? $this->options : new EntityCollection();
    }

    /**
     * @return array
     */
    public function getChoices() {
        $choices = [];

        /** @var CustomFieldOption $option */
        foreach ($this->getOptions() as $option) {
            $choices[$option->getValue()] = $option->getId();
        }
        return $choices;
    }

    /**
     * @param EntityCollection<CustomFieldOption> $options
     * @return $this
     */
    public function setOptions($options) {
        $this->options = $options;
        return $this;
    }

    /**
     * @param CustomFieldOption $option
     * @return $this
     */
    public function addOption(CustomFieldOption $option) {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
            $option->setField($this);
        }
        return $this;
    }

    /**
     * @param CustomFieldOption $option
     * @return $this
     */
    public function removeOption(CustomFieldOption $option) {
        if ($this->options->contains($option)) {
            $this->options->removeElement($option);
            $option->setField(null);
        }
        return $this;
    }

    public function canHaveOptions() {
        return self::typeCanHaveOptions($this->getType());
    }

    static public function typeCanHaveOptions($type) {
        return in_array($type, ['checkboxes', 'radiobuttons', 'dropdown']);
    }

    static public function getTypes() {
        static $typesData;

        if (is_null($typesData)) {
            $typesData = [];
            foreach (self::$types as $id => $name) {
                $typesData[$id] = [
                    'id' => $id,
                    'name' => $name,
                    'canHaveOptions' => self::typeCanHaveOptions($id),
                ];
            }
        }
        return $typesData;
    }
}
