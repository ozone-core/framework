<?php

namespace Ozone {

    use Slim\Http\UploadedFile;

    class Upload
    {

        protected static $instance = null;

        // Upload Multiple And Single File
        public static function move($directory,$files,$rename=true)
        {
            //CREATE DIRECTORY IF NOT EXISTS
            if (is_dir($directory) == FALSE) {
                $status = mkdir($directory, 0744, TRUE);
                if ($status < 1) {
                    throw New \Exception("Unable to make directory ['" . $directory . "']. Please provide sufficient permission ");
                }
            }

            //MULTIPLE FILE UPLOAD
            if (is_array($files)) {
                $uploadedFiles = $files;
                foreach ($uploadedFiles as $uploadedFile) {
                    if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                        $fileNames[] = self::handleUpload($directory, $uploadedFile,$rename);
                    }
                }

            } else {

                //SINGLE FILE UPLOAD
                $uploadedFile = $files;
                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    $fileNames = self::handleUpload($directory, $uploadedFile,$rename);
                }
            }
            return $fileNames;
        }

        public static function isFileSet(UploadedFile $files)
        {
            $res = false;
            if (is_array($files)) {

                foreach ($files as $file) {

                    $file_size = $file->getSize();

                    if ($file_size > 0) {

                        $res = true;
                    } else {
                        $res = false;
                    }

                }

            } else {

                if ($files->getSize() > 0) {

                    $res = true;
                } else {
                    $res = false;
                }
            }
            return $res;
        }

        // Check If File set or not

        public static function unlink($filePath)
        {

            if (is_file($filePath)) {
                unlink($filePath);
                return true;

            } else {
                return false;
            }

        }

        // Unlink File

        public static function instance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new Upload();
            }

            return self::$instance;
        }

        public static function handleUpload($directory, UploadedFile $uploadedFile,$rename)
        {
            $originalFileName = $uploadedFile->getClientFilename();
            $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
            $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
            $filename = sprintf('%s.%0.8s', $basename, $extension);
            $filename = ($rename)?$filename:$originalFileName;
            $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

            return $filename;
        }

    }
}
