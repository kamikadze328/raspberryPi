<?php


class User
{
    private $table_name = "users";

    // свойства объекта
    public $id;
    public $login;
    public $password;
    public $password_from_db;
    public $password_secure;

    public function __construct($login, $password)
    {
        //check user name
        $this->login = strip_tags($login);
        $this->password = strip_tags($password);
    }

}

