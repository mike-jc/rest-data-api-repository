<?php

namespace Tests\AppBundle;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareTestCase extends KernelTestCase {
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setUp() {
        self::bootKernel();
        $this->container = self::$kernel->getContainer();
    }
}