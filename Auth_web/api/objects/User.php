<?php
class User
{
    public $id;
    public $login;
    public $password;
    public $password_from_db;
    public $password_secure;
    public $description;

    public function __construct($login, $password)
    {
        //TODO check user name
        $this->login = strip_tags($login);
        $this->password = strip_tags($password);
    }
}

