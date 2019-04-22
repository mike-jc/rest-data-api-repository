<?php

namespace AppBundle\Repository;

use GuzzleHttp\Psr7\Response;

class HttpClientWIthDebug extends \GuzzleHttp\Client
{
    /**
     * @var array
     */
    public static $debuger = [];
    /**
     * @var bool Enable it if you want to enable debug.
     */
    public static $debugEnabled = true;
    /**
     * @var bool Enable it if you want to enable back trace.
     */
    public static $traceEnabled = false;

    public function __call($method, $args)
    {
        if (static::$debugEnabled) {
            static::$debuger[] = [
                'method' => $method,
                'args' => $args
            ];
            $time = microtime(true);
        }

        $res = parent::__call($method, $args);

        if (static::$debugEnabled) {
            $ind = count(static::$debuger) - 1;
            static::$debuger[$ind]['time'] = microtime(true) - $time;
            if ($res instanceof Response) {
                static::$debuger[$ind]['responseSize'] = $res->getBody()->getSize();
            }
            if (static::$traceEnabled) {
                static::$debuger[$ind]['trace'] = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 10);
            }
        }

        return $res;
    }

    /**
     * @return void
     */
    public static function showDebugDirty(): void
    {
        $sep = "\n<br>";
        foreach (static::$debuger as $debug) {
            echo ("Method: " . $debug['method'] . $sep);
            var_dump($debug['args']);
            echo ("Time: " . $debug['time'] . $sep);
            echo ("Response size: " . $debug['responseSize'] ?? "0" . $sep);
            if (isset($debug['trace'])) {
                var_dump($debug['trace']);
            }
            echo  $sep . $sep;
        }
    }
}