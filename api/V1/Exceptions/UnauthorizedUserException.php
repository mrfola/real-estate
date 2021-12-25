<?php
namespace ApI\V1\Exceptions;

use \Exception;

class UnauthorizedUserException extends Exception
{
    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
        parent::__construct($details);

    }
}