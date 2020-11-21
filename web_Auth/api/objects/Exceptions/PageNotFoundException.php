<?php


class PageNotFoundException extends Exception
{
    public function __construct($message = 'Page not found', $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}