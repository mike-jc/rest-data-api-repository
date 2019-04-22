<?php

namespace AppBundle\Repository\MetaData;

use Doctrine\Common\Annotations\Reader;
use AppBundle\Exception\AnnotationException;

interface LoaderInterface {

    /**
     * @param Reader $annotationReader
     * @param string $entityNamespace
     */
    public function __construct(Reader $annotationReader, $entityNamespace);

    /**
     * @param string|object $className
     * @return array
     * @throws AnnotationException
     */
    public function load($className);
}