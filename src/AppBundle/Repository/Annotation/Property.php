<?php

namespace AppBundle\Repository\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target("PROPERTY")
 */
class Property {
    /**
     * @Annotation\Required()
     * @var string
     */
    public $type = 'string';
    /**
     * @var string
     */
    public $entity;
    /**
     * @var bool
     */
    public $writeOnly = false;
    /**
     * @var bool
     */
    public $readOnly = false;

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getEntity() {
        return $this->entity;
    }

    /**
     * @return bool
     */
    public function isWriteOnly() {
        return $this->writeOnly;
    }

    /**
     * @return boolean
     */
    public function isReadOnly() {
        return $this->readOnly;
    }
}