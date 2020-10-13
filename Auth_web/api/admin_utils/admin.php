<?php
/** @noinspection PhpUndefinedVariableInspection */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/CodesAndMessages.php';

$error_code = 0;
$message = '';
$data = array();
try {
    $user_data = get_http_data();
    if (isset($user_data['purpose'])) {
        $sec_mng = new SecurityManager();
        $purpose = $user_data['purpose'];
        $can_write = $sec_mng->can_write_resource($permission_level);
        $can_read = $sec_mng->can_read_resource($permission_level);
        if ($purpose == 'delete_usr')
            if ($can_write) {
                include $_SERVER['DOCUMENT_ROOT'] . '/api/admin_utils/users/delete.php';
            } else throw new AccessDeniedException(true, false);
        else if ($purpose == 'create_usr')
            if ($can_write) {
                include $_SERVER['DOCUMENT_ROOT'] . '/api/admin_utils/users/create.php';
            } else throw new AccessDeniedException(true, false);
        else if ($purpose == 'reset_usr_passwd')
            if ($can_write) {
                include $_SERVER['DOCUMENT_ROOT'] . '/api/admin_utils/users/password_reset.php';
            } else throw new AccessDeniedException(true, false);
        else if ($purpose == 'change_user_role')
            if ($can_write) {
                include $_SERVER['DOCUMENT_ROOT'] . '/api/admin_utils/users/update_role.php';
            } else throw new AccessDeniedException(true, false);
        else if ($purpose == 'update_role')
            if ($can_write) {
                include $_SERVER['DOCUMENT_ROOT'] . '/api/admin_utils/roles/update.php';
            } else throw new AccessDeniedException(true, false);
        else if ($purpose == 'delete_role')
            if ($can_write) {
                include $_SERVER['DOCUMENT_ROOT'] . '/api/admin_utils/roles/delete.php';
            } else throw new AccessDeniedException(true, false);
        else if ($purpose == 'create_role')
            if ($can_write) {
                include $_SERVER['DOCUMENT_ROOT'] . '/api/admin_utils/roles/create.php';
            } else throw new AccessDeniedException(true, false);


        else if ($purpose == 'stats')
            if ($can_read) {
                include $_SERVER['DOCUMENT_ROOT'] . '/api/admin_utils/statistics/get_stats.php';
            } else throw new AccessDeniedException(false, true);
        else if ($purpose == 'users')
            if ($can_read) {
                include $_SERVER['DOCUMENT_ROOT'] . '/api/admin_utils/users/get_users.php';
            } else throw new AccessDeniedException(false, true);
        else if ($purpose == 'roles')
            if ($can_read) {
                include $_SERVER['DOCUMENT_ROOT'] . '/api/admin_utils/roles/get_roles.php';
            } else throw new AccessDeniedException(false, true);


        else
            $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;


    } else {
        $error_code = CodesAndMessages::WRONG_REQUEST_PARAMS;
    }
} catch (PDOException $e) {
    $error_code = CodesAndMessages::SMTH_WRONG;
    $message = $e->getMessage();
}
echo get_answer_for_client($error_code, $message, $db->error_msg, $data);