<?php

namespace AppBundle\Helper;

use Symfony\Component\HttpFoundation\Request;

trait HttpTrait {

    /**
     * @param array $headers
     * @return array
     */
    public function parseHeaders(array $headers) {
        $result = [];

        foreach ($headers as $header) {
            $parts = explode(':', $header);
            $name = array_shift($parts);
            $value = trim(implode(':', $parts));
            if (isset($result[$name])) {
                $result[$name] .= '; '. $value;
            } elseif ($name) {
                $result[$name] = $value;
            }
        }
        return $result;
    }

    /**
     * @param Request $request
     * @return null|string
     */
    public function getClientIp(Request $request) {
        if (!empty($request->server->get('HTTP_X_FORWARDED_FOR'))) {
            $ips = explode(',', $request->server->get('HTTP_X_FORWARDED_FOR'));
            return $ips[0];
        }

        return $request->getClientIp();
    }
}