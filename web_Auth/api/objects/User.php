<?php
class User
{
    public ?int $id = null;
    public string $login;
    public string $password;
    public ?string $password_from_db = null;
    public ?string $password_secure = null;
    public ?string $description = null;
    public ?int $role_id = null;

    public function __construct($login, $password)
    {
        //TODO check user name
        $this->login = strip_tags($login);
        $this->password = strip_tags($password);
    }
}

