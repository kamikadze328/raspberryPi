<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';

if (isset($user_data['login']) && isset($user_data['password'])) {

    $user = new User($user_data['login'], $user_data['password']);

    if ($user_data['purpose'] == 'login')
        if ($database->user_exists($user)) {

            include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
            $sec_mng = new SecurityManager();
            if ($sec_mng->check_password($user->password, $user->password_from_db)) {

                $permissions = $database->get_role_permissions_by_id($user->role_id);
                $token = $sec_mng->generate_token($user, $permissions);
                if ($database->save_user_token($user, $token, $sec_mng->get_token_expires_time())) {
                    $sec_mng->save_token_on_client($token, $user->login);
                    $message = 'Успешно!';
                } else {
                    $error_code = CodesAndMessages::DB_ERROR;
                }
            } else {
                $error_code = CodesAndMessages::WRONG_PASSWD;
            }
        } else {
            $error_code = CodesAndMessages::USER_NOT_EXISTS;
        }

    else {
        $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
    }

} else {
    $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
}


