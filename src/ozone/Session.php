<?php

namespace Ozone;

class Session extends Encryption
{

    //STARTS SESSION
    function __construct()
    {
        if (!isset($_SESSION))
            session_start();
        ob_start();
    }

    //SET SESSION VARIABLES
    public static function set($sessionData, $default = "")
    {
        if ($sessionData == '') {
            throw New \Exception('Invalid Session data');
        }
        if (is_array($sessionData)) {

            foreach ($sessionData as $key => $val) {

                $_SESSION[$key] = self::encode($val);

            }//END FOREACH

        } else {

            $_SESSION[$sessionData] = self::encode($default);
            $_SESSION['exptime'] = time(); //EXPIRY TIME

        }

    }

    public static function get($keyName)
    {
        if (sizeof($_SESSION) != NULL) {
            return self::decode($_SESSION[$keyName]);
        } else {
            return false;
        }
    }

    public static function clear($sessionData)
    {
        if ($sessionData == '') {
            throw New \Exception('Invalid session data');
        }
        if (is_array($sessionData)) {

            foreach ($sessionData as $val) {

                $_SESSION[$val] = NULL;

            }//END FOREACH

        } else {

            $_SESSION[$sessionData] = NULL;

        }
    }

    public static function all()
    {
        return $_SESSION;
    }

    public static function destroy()
    {
        unset($_SESSION);
        $_SESSION = array();
        session_destroy();

        return true;
    }
}