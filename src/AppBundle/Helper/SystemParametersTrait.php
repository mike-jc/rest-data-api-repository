<?php

namespace AppBundle\Helper;

use Symfony\Component\Yaml\Yaml;

trait SystemParametersTrait {

    /**
     * @return array
     */
    static protected function getSystemParameters() {
        $paramFile = getcwd() ."/app/config/parameters.yml";
        return file_exists($paramFile) ? Yaml::parse(file_get_contents($paramFile))['parameters'] : [];
    }
}