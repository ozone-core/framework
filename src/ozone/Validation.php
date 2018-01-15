<?php

namespace Ozone {

    use Psr\Http\Message\ServerRequestInterface as Request;
    use Stringy\Stringy;

    class Validation
    {
        public static $errors = [];

        public static function getError($elementName)
        {
            if (isset(self::$errors[$elementName])) {
                return self::$errors[$elementName];
            } else {
                return '';
            }
        }

        public static function rePopulate($value)
        {
            return (count(self::$errors) < 1) ? '' : self::setValue($value);
        }

        public static function setValue($fieldName)
        {
            return isset($_REQUEST[$fieldName]) ? $_REQUEST[$fieldName] : '';
        }


        public static function errors()
        {
            $errors = [];
            if (count(self::$errors) > 1) {

                foreach (self::$errors as $key => $val) {
                    $errors[] = [
                        'field' => $key,
                        'message' => $val,
                    ];
                }

                return $errors;
            }

            return $errors;
        }

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

        public static function isValid(Request $request, $rules, $message = [])
        {

            self::validate($request, $rules, $message);

            if (count(self::$errors) > 0) {
                return false;
            }
            return true;
        }

        private static function validate(Request $request, $rules, $message = [])
        {
            $input = $request->getParsedBody();
            $postVal = '';
            foreach ($rules as $fieldName => $rule) {

                $condition = explode('|', $rule);

                if (in_array('file', $condition)) {
                    self::checkFile($request, $fieldName, $rule);
                } else {
                    $postVal = isset($input[$fieldName]) ? $input[$fieldName] : '';
                    if (in_array('required', $condition)) {
                        if ($postVal == null) {
                            self::setError($fieldName, self::readableMessage($fieldName) . " is required.");
                        }
                    }
                }


                if (in_array('numeric', $condition)) {
                    self::checkNumeric($postVal, $fieldName);
                }

                if (in_array('boolean', $condition)) {
                    self::checkBoolean($postVal, $fieldName);
                }

                if (in_array('float', $condition)) {
                    self::checkFloat($postVal, $fieldName);
                }

                if (in_array('ip', $condition)) {
                    self::checkIp($postVal, $fieldName);
                }

                if (in_array('url', $condition)) {
                    self::checkUrl($postVal, $fieldName);
                }

                if (in_array('alpha_num', $condition)) {
                    self::checkAlphaNum($postVal, $fieldName);
                }

                if (in_array('password', $condition)) {
                    self::checkPassword($postVal, $fieldName);
                }

                if (in_array('confirm_password', $condition)) {
                    self::checkRetypePassword($postVal, $fieldName);
                }

                if (in_array('email', $condition)) {
                    self::checkEmail($postVal, $fieldName);
                }

                if (in_array('char', $condition)) {
                    self::checkCharacter($postVal, $fieldName);
                }

                if (!empty($postVal)) {
                    self::checkLength($postVal, $fieldName, $condition);
                }
            }
        }

        public static function checkFile(Request $request, $fieldName, $rules = '')
        {
            $uploadedFiles = $request->getUploadedFiles();
            $rules = explode('|', $rules);
            $files = $uploadedFiles[$fieldName];

            if (is_array($files)) {
                foreach ($files as $file) {

                    $file_name = $file->getClientFilename();
                    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $file_size = $file->getSize();

                    //CHECK FILE SIZE
                    if (in_array('required', $rules)) {

                        if ($file_size <= 0) {
                            self::setError($fieldName, self::readableMessage($fieldName) . " is required.");

                        }
                    }
                    if ($file_size > 0) {

                        foreach ($rules as $value) {

                            $res = explode(':', $value);

                            if (preg_match("/^mimes:([a-zA-Z,]*)$/", $value)) {

                                $format = explode(',', $res[1]);

                                if (!in_array($ext, $format)) {
                                    self::setError($fieldName, 'Only ' . implode(" , ", $format) . " are allowed");
                                    //CHECK FILE SIZE
                                }
                            } elseif (preg_match("/^size:([0-9]*)$/", $value)) {

                                if ($file_size >= 1048576 * intval($res[1])) {

                                    self::setError($fieldName, self::readableMessage($fieldName) . " size is greater than {$res[1]} Mb");
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

                        self::setError($fieldName, self::readableMessage($fieldName) . " is required.");

                    }
                }

                if ($file_size > 0) {

                    foreach ($rules as $key => $value) {

                        $res = explode(':', $value);

                        if (preg_match("/^mimes:([a-zA-Z,]*)$/", $value)) {

                            $format = explode(',', $res[1]);

                            if (!in_array($ext, $format)) {
                                self::setError($fieldName, 'Only ' . implode(" , ", $format) . " are allowed");
                            }

                        } elseif (preg_match("/^size:([0-9]*)$/", $value)) {

                            if ($file_size >= 1048576 * intval($res[1])) {

                                self::setError($fieldName, self::readableMessage($fieldName) . " size is greater than {$res[1]} Mb");
                            }
                        }
                    }

                }
            }//END ELSE
        }

        private static function setError($elementName, $message, $customMessage = '')
        {
            self::$errors[$elementName] = ($customMessage != '') ? $customMessage : ucfirst($message);
        }

        public static function readableMessage($message)
        {
            $string = new Stringy($message);
            $word = $string->underscored();
            return ucfirst(preg_replace('/_/', ' ', $word));
        }

        private static function checkNumeric($postVal, $fieldName)
        {
            if (!empty($postVal)) {

                if (!is_numeric($postVal)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a number.");

                }

            }
        }

        private static function checkBoolean($postVal, $fieldName)
        {
            if (!empty($postVal)) {

                if (!filter_var($postVal, FILTER_VALIDATE_BOOLEAN)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a valid boolean.");

                }

            }
        }

        private static function checkFloat($postVal, $fieldName)
        {
            if (!empty($postVal)) {

                if (!filter_var($postVal, FILTER_VALIDATE_FLOAT)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a valid floating number");

                }

            }

        }

        private static function checkIp($postVal, $fieldName)
        {
            if (!empty($postVal)) {

                if (!filter_var($postVal, FILTER_VALIDATE_IP)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a valid IP address.");

                }

            }

        }

        private static function checkUrl($postVal, $fieldName)
        {
            if (!empty($postVal)) {

                if (!filter_var($postVal, FILTER_VALIDATE_URL)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a valid URL address.");

                }

            }

        }

        private static function checkAlphaNum($postVal, $fieldName)
        {
            if (!empty($postVal)) {

                if (!preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]$/i', $postVal)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a valid Alphanumeric value.");

                }

            }

        }

        private static function checkPassword($postVal, $fieldName)
        {
            $postVal = trim($postVal);

            if (strlen($postVal) < 6) {

                self::setError($fieldName, $fieldName . " must be at least 6 character long.");

            } elseif (!preg_match('#[0-9]+#', $postVal)) {

                self::setError($fieldName, $fieldName . " must have at least one Number.");

            } elseif (!preg_match('#[a-z]+#', $postVal)) {

                self::setError($fieldName, $fieldName . " must have at least one Lowercase Letter.");

            } elseif (!preg_match('#[A-Z]+#', $postVal)) {

                self::setError($fieldName, $fieldName . " must have at least one Uppercase Letter.");

            } elseif (!preg_match('/[!\-_\+=\)\(\*&\^%$#@!\}\{\[\]|\.\:;|\,<>\?]+/', $postVal)) {

                self::setError($fieldName, $fieldName . " must have at least one special character.");

            } elseif (strlen($postVal) > 20) {

                self::setError($fieldName, $fieldName . " must not be greater than 20 character long.");

            }

        }

        private static function checkRetypePassword($postVal, $fieldName)
        {
            $passRetype = trim($postVal);
            $password = trim(isset($_REQUEST['password']));

            if ($passRetype != $password) {

                self::setError($fieldName, self::readableMessage($fieldName) . " didn't Matched!.");

            }

        }

        private static function checkEmail($postVal, $fieldName)
        {

            if (!empty($postVal)) {

                $domain = substr($postVal, strpos($postVal, '@') + 1);

                if (!preg_match('/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$/', $postVal) AND !filter_var($postVal, FILTER_VALIDATE_EMAIL)) {

                    self::setError($fieldName, $fieldName . " is not a valid email.");

                } elseif (checkdnsrr($domain) == FALSE) {

                    self::setError($fieldName, $fieldName . " domain is not Valid");
                }

            }

        }

        private static function checkCharacter($postVal, $fieldName)
        {

            if (!empty($postVal)) {

                if (preg_match('/[^a-zA-Z ]+/', $postVal)) {

                    self::setError($fieldName, self::readableMessage($fieldName) . " is not a character.");

                }

            }

        }

        private static function checkLength($postVal, $fieldName, $rules)
        {

            foreach ($rules as $rule) {

                $res = explode(':', $rule);
                $postVal = strip_tags(html_entity_decode($postVal));

                if (preg_match("/^min:([0-9]*)$/", $rule)) {

                    if (strlen($postVal) < intval($res[1])) {

                        self::setError($fieldName, self::readableMessage($fieldName) . " must be at least {$res[1]} character long.");
                    }

                } elseif (preg_match("/^max:([0-9]*)$/", $rule)) {

                    if (strlen($postVal) > intval($res[1])) {

                        self::setError($fieldName, self::readableMessage($fieldName) . " must be less than {$res[1]} character long.");
                    }
                }
            }

        }

    }
}
