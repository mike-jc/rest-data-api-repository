<?php

namespace AppBundle\Entity\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Type\EntityCollection;
use AppBundle\Helper\ImageTrait;
use AppBundle\Repository\Annotation as Api;

/**
 * @Api\Entity(endPoint="images")
 */
class Image extends File {
    use ImageTrait;

    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Assert\Type(type="integer")
     * @Assert\GreaterThan(0)
     */
    protected $width;
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Assert\Type(type="integer")
     * @Assert\GreaterThan(0)
     */
    protected $height;

    /**
     * @var EntityCollection<Thumbnail> Thumbnails of the image
     *
     * @Api\Property(entity="AppBundle\Entity\File\Thumbnail")
     * @Api\Collection()
     */
    protected $thumbnails;

    public function __construct() {
        parent::__construct();
        $this->thumbnails = new EntityCollection();
    }

    /**
     * @return int
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * @param int $width
     * @return $this
     */
    public function setWidth($width) {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight($height) {
        $this->height = $height;
        return $this;
    }

    /**
     * @return EntityCollection<Thumbnail>
     */
    public function getThumbnails() {
        return $this->thumbnails;
    }

    /**
     * @param EntityCollection<Thumbnail> $thumbnails
     * @return $this
     */
    public function setThumbnails($thumbnails) {
        $this->thumbnails = $thumbnails;
        return $this;
    }

    /**
     * @param Thumbnail $thumbnail
     * @return $this
     */
    public function addThumbnail(Thumbnail $thumbnail) {
        if (!$this->thumbnails->contains($thumbnail)) {
            $this->thumbnails->add($thumbnail);
            $thumbnail->setImage($this);
        }
        return $this;
    }

    /**
     * @param Thumbnail $thumbnail
     * @return $this
     */
    public function removeThumbnail(Thumbnail $thumbnail) {
        if ($this->thumbnails->contains($thumbnail)) {
            $this->thumbnails->removeElement($thumbnail);
            $thumbnail->setImage(null);
        }
        return $this;
    }

    /**
     * @param int $width
     * @param int $height
     */
    public function makeThumbnail($width, $height) {
        $data = $this->generateThumbnail($this->getData(false), $this->getMimeType(), $width, $height);

        $thumbnail = $this->getThumbnails()->first(function(Thumbnail $th) use ($width, $height) {
            return $th->getWidth() == $width && $th->getHeight() == $height;
        });
        if (!$thumbnail) {
            $thumbnail = new Thumbnail();
            $thumbnail->setWidth($width);
            $thumbnail->setHeight($height);

            $this->addThumbnail($thumbnail);
        }

        $thumbnail->setData($data);
        $thumbnail->setMimeType($this->getMimeType());
        $thumbnail->setSize(strlen($data));
    }

    /**
     * @param UploadedFile $uFile
     * @return self
     */
    static public function createFromUploadFile(UploadedFile $uFile) {
        /** @var self $image */
        $image = parent::createFromUploadFile($uFile);
        if ($image) {
            $sizeInfo = getimagesize($uFile->getPathname());
            $image->setWidth($sizeInfo[0]);
            $image->setHeight($sizeInfo[1]);
        }
        return $image;
    }
}