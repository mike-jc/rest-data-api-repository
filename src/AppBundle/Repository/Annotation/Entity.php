<?php

namespace AppBundle\Repository\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target("CLASS")
 */
class Entity {
    /**
     * @Annotation\Required()
     * @var string
     */
    public $endPoint;

    /**
     * @return string
     */
    public function getEndPoint() {
        return $this->endPoint;
    }

}