<?php


class AccessDeniedException extends Exception
{
    public bool $cantWrite = false;
    public bool $cantRead = false;

    public function __construct($cantWrite, $cantRead, $message = 'Access denied', $code = 0, Exception $previous = null) {
        $this->cantRead = $cantRead;
        $this->cantWrite = $cantWrite;
        parent::__construct($message, $code, $previous);
    }
}