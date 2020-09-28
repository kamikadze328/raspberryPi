<?php


class CodesAndMessages
{
    const WRONG_REQUEST_PARAMS = 400;
    const DB_ERROR = 401;
    const DB_NOT_AVAILABLE = 403;
    const WRONG_PASSWD = 404;
    const USER_NOT_EXISTS = 408;
    const USER_EXISTS = 409;
    const SMTH_WRONG = 410;

    const CODE_TO_MESSAGE = array(
        self::WRONG_REQUEST_PARAMS => 'Неверные параметры запроса.',
        self::DB_ERROR => 'Ошибка базы данных.',
        402 => '',
        self::DB_NOT_AVAILABLE => 'База данных недоступна.',
        self::WRONG_PASSWD => 'Пароль неверный',
        405 => '',
        406 => '',
        407 => '',
        self::USER_NOT_EXISTS => 'Такого пользователя не существует.',
        self::USER_EXISTS => 'Такой пользователь существует.',
        self::SMTH_WRONG => 'Что-то пошло не так.',
        );
}