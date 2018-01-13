<?php

namespace Ozone {

    use Stringy\StaticStringy as Stringy;

    class Validate
    {

        public static $errors = [];
        protected static $instance = null;

        /*
          |--------------------------------------------
          | Validate File
          |--------------------------------------------
          |
          |
         */

        public static function file($files, $fieldName, $rules = '', $customMessage = '')
        {
            $rules = explode('|', $rules);
            if (is_array($files)) {

                foreach ($files as $file) {

                    $file_name = $file->getClientFilename();
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $file_size = $file->getSize();

                    //CHECK FILE SIZE
                    if (in_array('required', $rules)) {

                        if ($file_size <= 0) {
                            self::setError($fieldName, self::readableMessage($fieldName) . " is required.", $customMessage);

                        }
                    }
                    if ($file_size > 0) {

                        foreach ($rules as $value) {

                            $res = explode(':', $value);

                            if (preg_match("/^mimes:([a-zA-Z,]*)$/", $value)) {

                                $format = explode(',', $res[1]);

                                if (!in_array($ext, $format)) {
                                    self::setError($fieldName, 'Only ' . implode(" , ", $format) . " are allowed", $customMessage);
                                    //CHECK FILE SIZE
                                }
                            } elseif (preg_match("/^size:([0-9]*)$/", $value)) {

                                if ($file_size >= 1048576 * intval($res[1])) {

                                    self::setError($fieldName, self::readableMessage($fieldName) . " size is greater than {$res[1]} Mb", $customMessage);
                                }
                            }
                        }
                    }
                }//FOREACH ENDS
            } else {

                $file_name = $files->getClientFilename();
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $file_size = $files->getSize();

                //CHECK FILE SIZE
                if (in_array('required', $rules)) {

                    if ($file_size <= 0) {

                        self::setError($fieldName, self::readableMessage($fieldName) . " is required.", $customMessage);

                    }
                }

                if ($file_size > 0) {

                    foreach ($rules as $key => $value) {

                        $res = explode(':', $value);

                        if (preg_match("/^mimes:([a-zA-Z,]*)$/", $value)) {

                            $format = explode(',', $res[1]);

                            if (!in_array($ext, $format)) {
                                self::setError($fieldName, 'Only ' . implode(" , ", $format) . " are allowed", $customMessage);
                            }

                        } elseif (preg_match("/^size:([0-9]*)$/", $value)) {

                            if ($file_size >= 1048576 * intval($res[1])) {

                                self::setError($fieldName, self::readableMessage($fieldName) . " size is greater than {$res[1]} Mb", $customMessage);
                            }
                        }
                    }

                }
            }//END ELSE
        }

        /*
          |--------------------------------------------
          | Validate String
          |--------------------------------------------
         */

        private static function setError($elementName, $message, $customMessage = '')
        {
            self::$errors[$elementName] = ($customMessage != '') ? $customMessage : ucfirst($message);
        }

        /*
          |--------------------------------------------
          | Validate Password
          |--------------------------------------------
         */

        public static function readableMessage($message)
        {
            $word = Stringy::underscored($message);
            return ucfirst(preg_replace('/_/', ' ', $word));
        }

        /*
          |--------------------------------------------
          | Validate Password Match
          |--------------------------------------------
         */

        public static function str($postVal, $fieldName, $rules = '', $customMessage = '')
        {
            $rules = explode('|', $rules);
            $postVal = trim($postVal);

            if (in_array('required', $rules)) {

                if (strlen($postVal) < 1 or empty($postVal)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is required.", $customMessage);

                }
            }

            if (!empty($postVal)) {

                self::checkLength($postVal, $fieldName, $rules, $customMessage);

            }// If Value Exists
        }

        /*
          |--------------------------------------------
          | Validate Checkbox
          |--------------------------------------------
         */

        private static function checkLength($postVal, $fieldName, $rules, $customMessage = '')
        {
            $value = [];
            foreach ($rules as $value) {

                $res = explode(':', $value);

                if (preg_match("/^min:([0-9]*)$/", $value)) {

                    if (strlen($postVal) < intval($res[1])) {

                        self::setError($fieldName, self::readableMessage($fieldName) . " must be at least {$res[1]} character long.", $customMessage);
                    }

                } elseif (preg_match("/^max:([0-9]*)$/", $value)) {

                    if (strlen($postVal) > intval($res[1])) {

                        self::setError($fieldName, self::readableMessage($fieldName) . " must be less than {$res[1]} character long.", $customMessage);
                    }
                }

            }
        }

        /*
          |--------------------------------------------
          | Validate Radio
          |--------------------------------------------
         */

        public static function pass($passVal, $passName = "Password")
        {
            $passVal = trim($passVal);

            if (empty($passVal)) {

                self::setError($passName, $passName . " is required.");

            } elseif (strlen($passVal) < 6) {

                self::setError($passName, $passName . " must be at least 6 character long.");

            } elseif (!preg_match('#[0-9]+#', $passVal)) {

                self::setError($passName, $passName . " must have at least one Number.");

            } elseif (!preg_match('#[a-z]+#', $passVal)) {

                self::setError($passName, $passName . " must have at least one Lowercase Letter.");

            } elseif (!preg_match('#[A-Z]+#', $passVal)) {

                self::setError($passName, $passName . " must have at least one Uppercase Letter.");

            } elseif (!preg_match('/[!\-_\+=\)\(\*&\^%$#@!\}\{\[\]|\.\:;|\,<>\?]+/', $passVal)) {

                self::setError($passName, $passName . " must have at least one special character.");

            } elseif (strlen($passVal) > 20) {

                self::setError($passName, $passName . " must not be greater than 20 character long.");

            }
        }

        /*
          |--------------------------------------------
          | Validate Email
          |--------------------------------------------
         */

        public static function match($passRetype, $password, $fieldName = "retype_password")
        {
            $passRetype = trim($passRetype);
            $password = trim($password);

            if (empty($passRetype) or strlen($passRetype) < 1) {

                self::setError($fieldName, self::readableMessage($fieldName) . " is required.");

            } elseif ($passRetype != $password) {

                self::setError($fieldName, self::readableMessage($fieldName) . " didn't Matched!.");

            }
        }

        /*
          |--------------------------------------------
          | Validate Bool
          |--------------------------------------------
         */

        public static function checkBox($cbVal, $fieldName, $rules = '', $customMessage = '')
        {
            $rules = explode('|', $rules);
            $postVal = trim($cbVal);

            if (in_array('required', $rules)) {

                if (count($cbVal) <= 0) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not checked", $customMessage);

                }
            }

            if (!empty($postVal)) {

                self::checkLength($postVal, $fieldName, $rules, $customMessage);

            }// If Value Exists
        }

        /*
          |--------------------------------------------
          | Validate Float
          |--------------------------------------------
         */

        public static function radio($rbVal, $fieldName, $customMessage = '')
        {
            $rbVal = trim($rbVal);

            if (count($rbVal) <= 0) {

                self::setError($fieldName, self::readableMessage($fieldName) . " is not Checked.", $customMessage);
            }
        }

        /*
          |--------------------------------------------
          | Validate IP
          |--------------------------------------------
         */

        public static function email($postVal, $fieldName = "Email", $rules = '')
        {
            $rules = explode('|', $rules);
            $postVal = trim($postVal);

            if (in_array('required', $rules)) {

                if ($postVal == "") {

                    self::setError($fieldName, $fieldName . " is required.");

                }
            }

            if (!empty($postVal)) {

                $domain = substr($postVal, strpos($postVal, '@') + 1);

                if (!preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $postVal) AND !filter_var($postVal, FILTER_VALIDATE_EMAIL)) {

                    self::setError($fieldName, $fieldName . " is not a valid email.");

                } elseif (checkdnsrr($domain) == FALSE) {

                    self::setError($fieldName, $fieldName . " domain is not Valid");
                }

            }// If Value Exists
        }

        /*
          |--------------------------------------------
          | Validate Url
          |--------------------------------------------
         */

        public static function bool($boolVal, $fieldName, $rules = '', $customMessage = '')
        {
            $rules = explode('|', $rules);
            $postVal = trim($boolVal);

            if (in_array('required', $rules)) {

                if ($postVal == "") {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is required.", $customMessage);

                }
            }

            if (!empty($postVal)) {

                if (!filter_var($postVal, FILTER_VALIDATE_BOOLEAN)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a valid boolean.", $customMessage);

                }

            }// If Value Exists
        }

        /*
          |--------------------------------------------
          | Validate Alphanumeric
          |--------------------------------------------
         */

        public static function float($floatVal, $fieldName, $rules = '', $customMessage = '')
        {
            $rules = explode('|', $rules);
            $postVal = trim($floatVal);

            if (in_array('required', $rules)) {

                if (strlen($postVal) < 1 or empty($postVal)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is required.", $customMessage);

                }
            }

            if (!empty($postVal)) {

                if (!filter_var($floatVal, FILTER_VALIDATE_FLOAT)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a valid floating number.", $customMessage);

                } else {

                    self::checkLength($postVal, $fieldName, $rules, $customMessage);

                }

            }// If Value Exists
        }

        /*
          |--------------------------------------------
          | Validate Number
          |--------------------------------------------
         */

        public static function ip($ipVal, $fieldName, $rules = '')
        {
            $rules = explode('|', $rules);
            $postVal = trim($ipVal);

            if (in_array('required', $rules)) {

                if (strlen($postVal) < 1 or empty($postVal)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is required.");

                }
            }

            if (!empty($postVal)) {

                if (!filter_var($postVal, FILTER_VALIDATE_IP)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a valid IP address.");

                }

            }// If Value Exists
        }

        /*
          |--------------------------------------------
          | Validate Character
          |--------------------------------------------
         */

        public static function url($urlVal, $fieldName, $rules = '')
        {
            $rules = explode('|', $rules);
            $postVal = trim($urlVal);

            if (in_array('required', $rules)) {

                if (strlen($postVal) < 1 or empty($postVal)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is required.");

                }
            }

            if (!empty($postVal)) {

                if (!filter_var($urlVal, FILTER_VALIDATE_URL)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a valid URL address.");

                }

            }// If Value Exists
        }

        /*
         |--------------------------------------------
         | Check Length
         |--------------------------------------------
        */

        public static function alphaNum($anVal, $fieldName, $rules = '', $customMessage = '')
        {
            $rules = explode('|', $rules);
            $postVal = trim($anVal);

            if (in_array('required', $rules)) {

                if (strlen($postVal) < 1 or empty($postVal)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is required.", $customMessage);

                }
            }

            if (!empty($postVal)) {

                if (!preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]$/i', $postVal)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a valid Alphanumeric value.", $customMessage);

                } else {

                    self::checkLength($postVal, $fieldName, $rules, $customMessage);

                }

            }// If Value Exists
        }


        /*
          |--------------------------------------------
          | Set Error
          |--------------------------------------------
         */

        public static function num($numVal, $fieldName, $rules = '', $customMessage = '')
        {
            $rules = explode('|', $rules);
            $postVal = trim($numVal);

            if (in_array('required', $rules)) {

                if (strlen($postVal) < 1 or empty($postVal)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is required.", $customMessage);

                }
            }

            if (!empty($postVal)) {

                if (!is_numeric($postVal)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a number.", $customMessage);

                } else {

                    self::checkLength($postVal, $fieldName, $rules, $customMessage);

                }

            }// If Value Exists
        }

        /*
         |--------------------------------------------
         | Get Errors
         |--------------------------------------------
        */

        public static function char($charVal, $fieldName, $rules = '', $customMessage = '')
        {
            $rules = explode('|', $rules);
            $postVal = trim($charVal);

            if (in_array('required', $rules)) {

                if (strlen($postVal) < 1 or empty($postVal)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is required.", $customMessage);

                }
            }

            if (!empty($postVal)) {

                if (preg_match('/[^a-zA-Z ]+/', $postVal)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a character.", $customMessage);

                } else {

                    self::checkLength($postVal, $fieldName, $rules, $customMessage);

                }

            }// If Value Exists

        }

        /*
          |--------------------------------------------
          | Repopulate Data
          |--------------------------------------------
         */

        public static function getError($elementName)
        {
            if (isset(self::$errors[$elementName])) {
                return self::$errors[$elementName];
            } else {
                return '';
            }
        }

        /*
          |--------------------------------------------
          | Repopulate input Data
          |--------------------------------------------
         */

        public static function rePopulate($value)
        {

            return (count(self::$errors) < 1) ? '' : self::setValue($value);
        }

        /*
          |--------------------------------------------
          | Get Error List
          |--------------------------------------------
         */

        public static function setValue($fieldName)
        {
            return isset($_REQUEST[$fieldName]) ? $_REQUEST[$fieldName] : '';
        }

        /*
          |--------------------------------------------
          | Display Error in Number
          |--------------------------------------------
         */

        public static function errorList()
        {
            $errorsList = "<ol class=\"text-danger\">\n";
            foreach (self::$errors as $value) {
                $errorsList .= "<li>" . $value . "</li>\n";
            }
            $errorsList .= "</ol>\n";
            return $errorsList;
        }

        public static function errorCount()
        {
            $message = "";
            if (count(self::$errors) > 1) {
                $message = "There were " . count(self::$errors) . " errors sending your data!\n";
            } elseif (count(self::$errors) == 1) {
                $message = "There was an error sending your data!\n";
            }
            return $message;
        }

        public static function isFine()
        {
            if (count(self::$errors) > 0) {
                return false;
            }
            return true;
        }


    }// Class

}// Namespace
