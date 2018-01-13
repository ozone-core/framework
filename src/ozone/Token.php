<?php

namespace Ozone {

    class Token
    {

        const FIELD_NAME = '_token';
        const DO_NOT_CLEAR = FALSE;

        public static function csrf()
        {
            Session::clear('formToken');
            $token = self::generateToken();
            echo "<input name='" . self::FIELD_NAME . "' value='{$token}' type='hidden' />";
        }

        protected static function generateToken()
        {
            $time = time();
            $secret = sha1(mt_rand(0, 1000000));
            $token = Encryption::encode($secret);
            $_SESSION['formToken'] = $token;
            $_SESSION['formTokenExpires'] = $time;
            return $token;
        }

        public static function isValid()
        {
            $valid = false;
            $postedToken = isset($_REQUEST[self::FIELD_NAME]) ? $_REQUEST[self::FIELD_NAME] : '';

            if (!empty($postedToken) && isset($_SESSION['formToken'])) {

                $postedToken = Encryption::decode($postedToken);
                $formToken = Encryption::decode($_SESSION['formToken']);

                // 10 Minute == 300 Seconds
                if (isset($postedToken) && $formToken == $postedToken && $_SESSION['formTokenExpires'] >= time() - 300) {
                    $valid = true;
                    unset($_SESSION['formToken']);
                    unset($_SESSION['formTokenExpires']);
                }

            }

            return $valid;
        }

    }
}
