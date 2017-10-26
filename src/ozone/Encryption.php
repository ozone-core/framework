<?php

namespace Ozone;


class Encryption
{
    private static $algo = 'sha256';
    private static $encMethod = 'AES-256-CBC';
    private static $secretIv = 'A4o[PH>i=s+1GVPg&>^EYImP=^nLd5';
    private static $secretKey = ',i-t]aNgl4;FTPxco,AIKN(`):S0b6';

    public static function encode($data)
    {
        $output = openssl_encrypt($data, self::$encMethod, self::getKey(), 0, self::getIv());
        $output = base64_encode($output);
        return $output;
    }

    public static function decode($data)
    {
        $output = openssl_decrypt(base64_decode($data), self::$encMethod, self::getKey(), 0, self::getIv());
        return $output;
    }

    public static function getKey()
    {
        return hash(self::$algo, self::setting('key'));
    }

    public static function getIv()
    {
        return $iv = substr(hash(self::$algo, self::setting('iv')), 0, 16);;
    }

    public static function setting($type)
    {
        $data = '';
        $setting = require ROOT . '../config/settings.php';

        if ($type == 'iv') {
            $secretIv = $setting['encryption']['secret_iv'];
            $data = trim($secretIv) != '' ? $secretIv : self::$secretIv;

        } elseif ($type == 'key') {
            $secretKey = $setting['encryption']['secret_key'];
            $data = trim($secretKey) != '' ? $secretKey : self::$secretKey;

        }

        return $data;
    }
}
