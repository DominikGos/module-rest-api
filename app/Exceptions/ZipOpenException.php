<?php 

namespace App\Exceptions;

use Exception;

class ZipOpenException extends Exception
{
    public function __construct($message = "Failed to open ZIP file", $code = 500)
    {
        parent::__construct($message, $code);
    }
}