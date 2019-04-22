<?php

namespace AppBundle\Entity\File;

use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Repository\Annotation as Api;

/**
 * @Api\Entity(endPoint="thumbnails")
 */
class Thumbnail extends Image {
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @Assert\GreaterThan(0)
     */
    protected $width;
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @Assert\GreaterThan(0)
     */
    protected $height;
    /**
     * @var Image image associated with this thumbnail
     *
     * @Api\Property(entity="AppBundle\Entity\File\Image", writeOnly=true)
     */
    protected $image;

    /**
     * @return Image
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * @param Image $image
     * @return $this
     */
    public function setImage($image) {
        $this->image = $image;
        return $this;
    }

    /**
     * @param int $width
     * @param int $height
     * @return self
     */
    static public function createEmpty($width, $height) {
        $thumbnail = new self;
        $thumbnail->setWidth($width);
        $thumbnail->setHeight($height);
        $thumbnail->sendOnlyProperties(['width', 'height']);
        return $thumbnail;
    }
}