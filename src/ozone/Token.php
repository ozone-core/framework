<?php

namespace Ozone {

    class Token
    {

        const FIELD_NAME = '_token';
        const DO_NOT_CLEAR = FALSE;

        public static function csrf()
        {
            Session::clear('formToken');
            $token = self::_generateToken();
            echo "<input name='" . self::FIELD_NAME . "' value='{$token}' type='hidden' />";
        }

        public static function isValid($clear = true)
        {
            $valid = false;
            $posted = isset($_REQUEST[self::FIELD_NAME]) ? $_REQUEST[self::FIELD_NAME] : '';
            if (!empty($posted)) {
                $posted = Encryption::decode($posted);
                if (isset($_SESSION['formToken'][$posted])) {
                    if ($_SESSION['formToken'][$posted] >= time() - 7200) {
                        $valid = true;
                    }
                    if ($clear) {
                        unset($_SESSION['formToken'][$posted]);
                    }
                }
            }
            return $valid;
        }

        protected static function _generateToken()
        {
            $time = time();
            $token = Encryption::encode(sha1(mt_rand(0, 1000000)));
            $_SESSION['formToken'][$token] = $time;
            return $token;
        }

    }
}

/*
 USAGE
<form action="process.php" method="post">
    <label>What is your name? <input name="name" /></label>
    <input type="submit" />
    <?php echo Token::csrf() ?>
</form>


if (Token::isValid()) {

}
else {
 die('The form is not valid or has expired.');
}
 */