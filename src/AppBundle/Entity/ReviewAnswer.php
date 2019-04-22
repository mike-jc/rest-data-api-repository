<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;

/**
 * Review answer in the review
 *
 * @Api\Entity(endPoint="review_answers")
 */
class ReviewAnswer extends BaseEntity implements \JsonSerializable {
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
     * Field contains numerical score for some reason.
     * @todo rename it
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\NotBlank
     */
    private $text;
    /**
     * @var \DateTime date when answer has been created
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $created;

    /**
     * @var ReviewQuestion question that this answer is given to
     *
     * @Api\Property(entity="ReviewQuestion")
     */
    private $question;

    /**
     * @var Review review that has this answer
     *
     * @Api\Property(entity="Review")
     */
    private $review;

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
     * @param string $text text field contains numerical score for some reason.
     * @return $this
     */
    public function setText($text) {
        // the value should be in range 0 <= $value <= 10
        $value = max(0, min((int)$text, 10));

        $this->text = $value;
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
     * @return ReviewQuestion
     */
    public function getQuestion() {
        return $this->question;
    }

    /**
     * @param ReviewQuestion|null $question
     * @return $this
     */
    public function setQuestion(ReviewQuestion $question = null) {
        if ($question && (!$this->question || $this->question->getId() != $this->getId())) {
            $this->question = $question;
        } elseif (!$question && $this->question) {
            $oldQuestion = $this->question;
            $this->question = null;
        }
        return $this;
    }

    /**
     * @return Review
     */
    public function getReview() {
        return $this->review;
    }

    /**
     * @param Review|null $review
     * @return $this
     */
    public function setReview(Review $review = null) {
        if ($review && (!$this->review || $this->review->getId() != $this->getId())) {
            $this->review = $review;
            $review->addAnswer($this);
        } elseif (!$review && $this->review) {
            $oldReview = $this->review;
            $this->review = null;
            $oldReview->removeAnswer($this);
        }
        return $this;
    }

    function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'text' => $this->getText(),
            'created' => $this->getCreated(),
            'question' => $this->getQuestion()
        ];
    }


}
