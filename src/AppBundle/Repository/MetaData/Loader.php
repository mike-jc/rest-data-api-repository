<?php

namespace AppBundle\Repository\MetaData;

use Doctrine\Common\Annotations\Reader;
use Tree\Node\Node as TreeNode;
use AppBundle\Exception\AnnotationException;
use AppBundle\Helper\TreeTrait;
use AppBundle\Repository\Annotation;

class Loader implements LoaderInterface {
    use TreeTrait;

    /**
     * @var Reader
     */
    private $annotationReader;
    /**
     * @var string
     */
    private $entityNamespace;
    /**
     * @var array
     */
    private $cache;
    /**
     * @var array
     */
    private $propCache;

    /**
     * @param Reader $annotationReader
     * @param string $entityNamespace
     */
    public function __construct(Reader $annotationReader, $entityNamespace) {
        $this->annotationReader = $annotationReader;
        $this->entityNamespace = $entityNamespace;
        $this->cache = [];
        $this->propCache = [];
    }

    /**
     * @param mixed $className
     * @return array
     * @throws AnnotationException
     */
    public function load($className) {
        $className = is_object($className) ? get_class($className) : $className;

        if (isset($this->cache[$className])) {
            return $this->cache[$className];
        }

        $class = $this->getReflectionClass($className);
        $trackedClasses = new TreeNode($class->getName());

        $data = ['entity' => $class->getName()];
        $data['endPoint'] = $this->getEndPoint($class);
        $data['extra'] = $this->parseProperties($class, $trackedClasses);

        $this->cache[$className] = $data;
        return $data;
    }

    /**
     * @param \ReflectionClass $class
     * @return string
     * @throws AnnotationException
     */
    protected function getEndPoint(\ReflectionClass $class) {

        /** @var Annotation\Entity $entityAnnotation */
        $entityAnnotation = $this->annotationReader->getClassAnnotation($class, Annotation\Entity::class);
        $endPoint = $entityAnnotation ? $entityAnnotation->getEndPoint() : null;
        if (empty($endPoint)) {
            throw new AnnotationException("End point for entity is not valid: {$class->getName()}");
        }
        return $endPoint;
    }

    /**
     * @param \ReflectionClass $class
     * @param TreeNode $trackedClasses
     * @return array
     */
    protected function parseProperties(\ReflectionClass $class, TreeNode $trackedClasses) {
        $data = [];

        /** @var $property \ReflectionProperty */
        foreach ($class->getProperties() as $property) {
            $mapping = [];
            $mapping['name'] = $property->getName();
            $mapping['public'] = $property->isPublic();

            foreach ($this->annotationReader->getPropertyAnnotations($property) as $annotation) {
                if ($annotation instanceof Annotation\Id) {
                    $data['id'] = $property->getName();
                    $mapping['type'] = 'integer';
                    continue;
                }
                if ($annotation instanceof Annotation\Property) {
                    $mapping['type'] = $annotation->getType();
                    if ($annotation->getEntity()) {
                        $propClassName = $this->fullyQualifiedClassName($annotation->getEntity());
                        $parentPropertyClassName = $trackedClasses->getValue();
                        $cacheKey = "{$parentPropertyClassName}::{$propClassName}";

                        $mapping['type'] = $propClassName;
                        $mapping['class'] = true;

                        if ($this->isTracked($trackedClasses, $propClassName)) {
                            $mapping['isTracked'] = true;
                            if (!empty($this->propCache[$cacheKey]['endPoint'])) {
                                $mapping['endPoint'] = $this->propCache[$cacheKey]['endPoint'];
                            } else {
                                $propClass = $this->getReflectionClass($propClassName);
                                $mapping['endPoint'] = $this->getEndPoint($propClass);
                            }
                            continue;
                        } else {
                            $trackedClasses->addChild($trackedChild = new TreeNode($propClassName));
                            if (!isset($this->propCache[$cacheKey]) ) {
                                $propClass = $this->getReflectionClass($propClassName);
                                $this->propCache[$cacheKey]['parent'] = $parentPropertyClassName;
                                $this->propCache[$cacheKey]['endPoint'] = $this->getEndPoint($propClass);
                                $this->propCache[$cacheKey]['extra'] = $this->parseProperties($propClass, $trackedChild);
                            }
                            $mapping['endPoint'] = $this->propCache[$cacheKey]['endPoint'];
                            $mapping['extra'] = $this->propCache[$cacheKey]['extra'];
                        }
                    }
                }
                if ($annotation instanceof Annotation\Collection) {
                    $mapping['collection'] = true;
                }
            }

            if (!empty($mapping['type'])) {
                $data['properties'][$mapping['name']] = $mapping;
            }
        }

        if (empty($data['id'])) {
            $data['id'] = 'id';
        }

        return $data;
    }

    /**
     * @param $className
     * @return \ReflectionClass
     * @throws AnnotationException
     */
    protected function getReflectionClass($className) {
        try {
            return new \ReflectionClass($className);
        } catch (\Exception $e) {
            throw new AnnotationException($e->getMessage());
        }
    }

    /**
     * @param $className
     * @return string
     */
    protected function fullyQualifiedClassName($className) {
        if (empty($className)) {
            return $className;
        }

        if ($className !== null && strpos($className, '\\') === false && strlen($this->entityNamespace) > 0) {
            $className = $this->entityNamespace . '\\' . $className;
        }
        $className = ltrim($className, '\\');

        return $className;
    }
}