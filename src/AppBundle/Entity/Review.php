<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Repository\Annotation as Api;

/**
 * Review in the meeting
 *
 * @Api\Entity(endPoint="reviews")
 */
class Review extends BaseEntity implements \JsonSerializable {
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id()
     */
    private $id;
    /**
     * @var string review text
     * Warning: use filtered_symbols option if open this field for user via TextareaType to prevent XSS attacks
     *
     * @Api\Property()
     * @Assert\Type(type="string")
     * @Assert\Length(max = 2000)
     */
    private $text;
    /**
     * @var \DateTime date when review has been created
     *
     * @Api\Property(type="datetime")
     * @Assert\DateTime
     */
    private $created;
    /**
     * @var boolean flag that indicates if review is anonymous
     *
     * @Api\Property(type="boolean")
     * @Assert\Choice({"0", "1"})
     */
    private $anonymous = false;

    /**
     * @var Guest guest assigned to this review
     *
     * @Api\Property(entity="Guest")
     */
    private $guest;
    /**
     * @var Meeting meeting assigned to this review
     *
     * @Api\Property(entity="Meeting")
     */
    private $meeting;

    /**
     * @var EntityCollection<ReviewAnswer> answers of this review
     *
     * @Api\Property(entity="ReviewAnswer")
     * @Api\Collection()
     */
    private $answers;

    public function __construct() {
        $this->created = new \DateTime();
        $this->answers = new EntityCollection();
    }

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
     * @return boolean
     */
    public function isAnonymous() {
        return $this->anonymous;
    }

    /**
     * @param boolean $anonymous
     * @return $this
     */
    public function setAnonymous($anonymous) {
        $this->anonymous = $anonymous;
        return $this;
    }

    /**
     * @return Guest
     */
    public function getGuest() {
        return $this->guest;
    }

    /**
     * @param Guest $guest
     * @return $this
     */
    public function setGuest(Guest $guest = null) {
        $this->guest = $guest;
        return $this;
    }

    /**
     * @return Meeting
     */
    public function getMeeting() {
        return $this->meeting;
    }

    /**
     * @param Meeting $meeting
     * @return $this
     */
    public function setMeeting(Meeting $meeting = null) {
        $this->meeting = $meeting;
        return $this;
    }

    /**
     * @return EntityCollection
     */
    public function getAnswers() {
        return $this->answers;
    }

    /**
     * @param EntityCollection $answers
     */
    public function setAnswers($answers) {
        $this->answers = $answers;
    }

    /**
     * @param ReviewAnswer $answer
     * @return $this
     */
    public function addAnswer(ReviewAnswer $answer) {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setReview($this);
        }
        return $this;
    }

    /**
     * @param ReviewAnswer $answer
     * @return $this
     */
    public function removeAnswer(ReviewAnswer $answer) {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            $answer->setReview(null);
        }
        return $this;
    }

    function jsonSerialize() {
        $answers = [];
        if (count($this->getAnswers()) > 0) {
            /** @var SuggestedDate $date */
            foreach ($this->getAnswers() as $answer) {
                $answers[] = $answer->jsonSerialize();
            }
        }
        return [
            'id' => $this->getId(),
            'text' => $this->getText(),
            'created' => $this->getCreated(),
            'answers' => $answers
        ];
    }

    /**
     * @param Meeting $meeting
     * @param string $text
     * @return Review
     */
    public static function createForMeeting(Meeting $meeting, $text = '') {
        $review = new Review();
        $review->setGuest($meeting->getGuest());
        $review->setMeeting($meeting);
        $review->setText($text);
        return $review;
    }

    /**
     * @return float
     */
    public function getReviewAverageScore() {
        return self::getAverageScore(new EntityCollection([$this]));
    }

    /**
     * @param EntityCollection $reviews
     * @return float
     */
    public static function getAverageScore(EntityCollection $reviews) {
        $scoreSum = 0;
        $scoreCount = 0;

        /** @var Review $r */
        foreach ($reviews as $r) {
            /** @var ReviewAnswer $a */
            foreach ($r->getAnswers() as $a) {
                $scoreSum += (int)$a->getText();
                $scoreCount++;
            }
        }
        return round($scoreCount ? $scoreSum / $scoreCount : 0, 1);
    }

    /**
     * Check if review has specific question answered
     * @param ReviewQuestion $question
     * @return ReviewAnswer
     */
    public function getAnswerTo(ReviewQuestion $question) {
        /** @var ReviewAnswer $answer */
        foreach ($this->getAnswers() as $answer) {
            if ($answer->getQuestion()->getId() == $question->getId()) {
                return $answer;
            }
        }
    }
}
