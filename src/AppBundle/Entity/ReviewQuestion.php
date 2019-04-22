<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;

/**
 * Review question in the review (global on the instance scope)
 *
 * @Api\Entity(endPoint="review_questions")
 */
class ReviewQuestion extends BaseEntity implements \JsonSerializable {

    const DEFAULT_QUESTION = 'How would you score this video meeting?';

    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var string review text
     * Warning: use filtered_symbols option if open this field for user via TextType to prevent XSS attacks
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     */
    private $text;
    /**
     * @var \DateTime date when question has been created
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $created;
    /**
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $deleted = false;

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
    public function getText() {
        return $this->text;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText($text) {
        $this->text = $text;
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
    public function setCreated($created) {
        $this->created = $created;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeleted() {
        return $this->deleted;
    }

    /**
     * @param mixed $deleted
     * @return $this
     */
    public function setDeleted($deleted) {
        $this->deleted = $deleted;
        return $this;
    }

    function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'text' => $this->getText(),
            'created' => $this->getCreated()
        ];
    }
}
