<?php


class UnauthorizedException extends Exception
{
    public function __construct($message = 'You are not unauthorized', $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}