<?php

namespace AppBundle\Entity\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\BaseEntity;
use AppBundle\Repository\Annotation as Api;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * @Api\Entity(endPoint="files")
 */
class File extends BaseEntity {
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Api\Id
     */
    protected $id;
    /**
     * @var string Name of file
     * Warning: use filtered_symbols option if open this field for user via TextareaType to prevent XSS attacks
     * Warning: Must not be blank but we save this entity not via form but via asynchronous uploading
     *
     * @Api\Property(type="string")
     */
    protected $fileName;
    /**
     * @var string
     *
     * @Api\Property(type="string")
     * @AppAssert\MimeType()
     */
    protected $mimeType;
    /**
     * @var int
     *
     * @Api\Property(type="integer")
     * @Assert\GreaterThan(0)
     */
    protected $size;
    /**
     * @var string File content (could be binary)
     * Warning: field should not be directly accessable to user for editing to protect system from XSS attacks
     * Warning: data should be checked to be of necessary MIME type (e.g., only images)
     * Warning: Must not be blank but we save this entity not via form but via asynchronous uploading
     *
     * @Api\Property(type="blob", writeOnly=true)
     */
    protected $data;
    /**
     * @var string
     *
     * @Api\Property(type="string", readOnly=true)
     */
    protected $url;

    /**
     * Base constructor
     */
    public function __construct() {
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
    public function getFileName() {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return $this
     */
    public function setFileName($fileName) {
        $filteredName = preg_replace('/[`#\$\*\^\{\}\[\]\(\)\/\|]/i', '', $fileName); // prevent XSS attacks
        $this->fileName = $filteredName;
        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType() {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     * @return $this
     */
    public function setMimeType($mimeType) {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setSize($size) {
        $this->size = $size;
        return $this;
    }

    /**
     * @param bool $encode Output to Data API needs to be in Base64
     * @return string
     */
    public function getData($encode = true) {
        return $encode ? base64_encode($this->data) : $this->data;
    }

    /**
     * @param string $data
     * @return $this
     */
    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }

    /**
     * @param UploadedFile $uFile
     * @return self
     */
    static public function createFromUploadFile(UploadedFile $uFile) {
        $file = null;

        if ($uFile->isValid()) {
            $calledClass = get_called_class();

            /** @var self $file */
            $file = new $calledClass;
            $file->setFileName($uFile->getClientOriginalName());
            $file->setMimeType($uFile->getMimeType());
            $file->setSize($uFile->getSize());
            $file->setData(file_get_contents($uFile->getRealPath()));
        }
        return $file;
    }
}