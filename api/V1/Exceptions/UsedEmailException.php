<?php
namespace Api\V1\Exceptions;

use \Exception;

class UsedEmailException extends Exception
{
    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
        parent::__construct($details);

    }
}