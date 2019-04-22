<?php

namespace AppBundle\Helper;

trait RandomizationTrait {
    static private $letters = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ";
    static private $digits = "0123456789";
    static private $specials = "~`!@#$%^&*()_+/=-:;.,|";

    /**
     * @param string $alphabet
     * @param int $length
     * @return string
     */
    static public function makeRandomString($alphabet, $length = 8) {
        $str = array();
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, strlen($alphabet) - 1);
            $str[$i] = $alphabet[$n];
        }
        return implode($str);
    }

    /**
     * At least 8 symbols. At least one special symbol, two digits.
     * @return string
     */
    static public function makeRandomPassword() {
        $psw = self::makeRandomString(self::$letters . self::$digits . self::$specials, 8);
        $hasTwoDigits = preg_match('/\d[^\d]*\d/', $psw);
        if (!$hasTwoDigits) {
            $psw = $psw . self::makeRandomString(self::$digits, 2);
        }
        $hasSpecialSymbols = preg_match('/[^0-9a-zA-Z]+/', $psw);
        if (!$hasSpecialSymbols) {
            $psw = self::makeRandomString(self::$specials, 2) . $psw;
        }
        return $psw;
    }
}