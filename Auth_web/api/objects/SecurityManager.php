<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';

class SecurityManager
{
    private int $RANDOM_PASSWORD_LENGTH = 5;
    private int $TOKEN_LENGTH = 8;
    private int $TOKEN_LIFETIME = 604800; //week
    const COOKIE_DOMAIN = 'carbon-dv.ru'; //$_SERVER['HTTP_HOST']
    private bool $COOKIE_SECURE_FLAG = false; //TODO !!!!!!!11
    private bool $COOKIE_HTTPONLY = true;   //TODO
    private string $COOKIE_SAMESITE = 'Strict';
    private string $COOKIE_TOKEN_NAME = 'access_token';
    private string $COOKIE_USER_META_NAME = 'user_meta';
    private string $SESSION_COOKIE_NAME = 'SUID';
    const SESSION_LIFETIME = 600;
    const CANT_WRITE = 1;
    const CANT_READ = 2;
    const NOT_RIGHT_PATH = 3;
    const USER_ROLE_ID = 0;
    const ADMIN_ROLE_ID = 2;
    const SUPER_ADMIN_ROLE_ID = 1;
    const DEFAULT_USER_ROLE_ID = self::USER_ROLE_ID;
    const DEFAULTS_ROLES = array(self::ADMIN_ROLE_ID, self::USER_ROLE_ID, self::SUPER_ADMIN_ROLE_ID);


    function isset_token()
    {
        return isset($_COOKIE[$this->COOKIE_TOKEN_NAME]);
    }

    function isset_user_meta()
    {
        return isset($_COOKIE[$this->COOKIE_USER_META_NAME]);
    }

    function get_domain()
    {
        return $this::COOKIE_DOMAIN;
    }

    function get_token()
    {
        return $_COOKIE[$this->COOKIE_TOKEN_NAME];
    }

    function get_user_meta()
    {
        return $_COOKIE[$this->COOKIE_USER_META_NAME];
    }

    private function decode_token($token)
    {
        return json_decode(base64_decode($token), true);
    }

    private function get_token_payload($token)
    {
        return $this->decode_token($token)['pld'];
    }

    function get_user_id_by_token($token)
    {
        return $this->get_token_payload($token)['usid'];
    }

    function generate_token($user, $permissions)
    {
        $token = $this->generate_random_str($this->TOKEN_LENGTH);
        $full_token = array(
            'tn' => $token,
            'pld' => array(
                'usid' => $user->id,
                'pm' => $this->prepare_permissions($permissions),
                'rlid' => $user->role_id
            )
        );
        return base64_encode(json_encode($full_token));
    }

    private function prepare_permissions($permissions)
    {
        // look likes array of arrays - [[],[],[],...]
        // each array consist of four numbers - path, url_id, read_permission, write_permission
        // For example - "pm":[['syncdata/', 5, 2-(1+1)],[6,2-(1+1)],[7,2-(1+1)],[8,2-(1+0)],[9,2-(0+0)]]
        // So there is three level of security 0 (r and w), 1 (only r) and 2 (not r and not w).
        $result_arr = array();
        foreach ($permissions as $p) {
            $result_arr[] = array(substr($p['path'], 1), intval($p['url_id']), (2 - (intval($p['r']) + intval($p['w']))));
        }
        return $result_arr;
    }

    private function generate_random_str($length)
    {
        return bin2hex(openssl_random_pseudo_bytes($length / 2));

    }

    function get_token_expires_time()
    {
        return time() + $this->TOKEN_LIFETIME;
    }

    function get_session_expires_time()
    {
        return time() + $this::SESSION_LIFETIME;
    }

    function save_token_on_client($token, $username)
    {
        $cookie_options = $this->get_cookie_options();
        setcookie($this->COOKIE_TOKEN_NAME, $token, $cookie_options);

        $cookie_options['httponly'] = false;
        $user_meta = array('name' => $username, 'isAdmin'=>$this->is_admin($token));
        setcookie($this->COOKIE_USER_META_NAME, json_encode($user_meta), $cookie_options);


        $this->set_headers();
    }

    function save_session($session_id)
    {
        $cookie_options = $this->get_session_cookie_options();
        setcookie($this->SESSION_COOKIE_NAME, $session_id, $cookie_options);
        $this->set_headers();
    }

    function is_session_exists()
    {
        return isset($_COOKIE[$this->SESSION_COOKIE_NAME]);
    }

    function get_session_id()
    {
        return intval($_COOKIE[$this->SESSION_COOKIE_NAME]);
    }

    function is_auth_user()
    {
        return !is_null($this->get_token());
    }

    function clear_token_on_client()
    {
        $cookie_options = $this->get_old_cookie_options();
        setcookie($this->COOKIE_TOKEN_NAME, '', $cookie_options);

        $cookie_options['httponly'] = false;
        setcookie($this->COOKIE_USER_META_NAME, '', $cookie_options);

        $session_cookie_option = $this->get_old_session_cookie_options();
        setcookie($this->SESSION_COOKIE_NAME, '', $session_cookie_option);
    }

    function set_headers()
    {
        header("Content-Security-Policy: default-src, 'self'/");
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");
    }

    function check_password($password, $password_hash)
    {
        return password_verify($password, $password_hash);
    }


    private function get_cookie_options()
    {
        return array(
            'expires' => $this->get_token_expires_time(),
            'path' => '/',
            'domain' => $this::COOKIE_DOMAIN,
            'secure' => $this->COOKIE_SECURE_FLAG,
            'httponly' => $this->COOKIE_HTTPONLY,
            'samesite' => $this->COOKIE_SAMESITE
        );
    }

    private function get_session_cookie_options()
    {
        $options = $this->get_cookie_options();
        $options['expires'] = $this->get_session_expires_time();
        return $options;
    }

    private function get_old_session_cookie_options()
    {
        $options = $this->get_session_cookie_options();
        $options['expires'] = time() - 3600;
        return $options;
    }

    private function get_old_cookie_options()
    {
        return array(
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => $this::COOKIE_DOMAIN,
            'secure' => $this->COOKIE_SECURE_FLAG,
            'httponly' => $this->COOKIE_HTTPONLY,
            'samesite' => $this->COOKIE_SAMESITE
        );
    }

    function get_secure_password($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }



    function generate_password()
    {
        return $this->generate_random_str($this->RANDOM_PASSWORD_LENGTH);
    }

    function get_permission_level_for_request($request, $token, $refer = null)
    {
        $request = parse_path($request);
        $is_for_read = substr($request, -1) === '/';
        $refer = parse_current_path($refer);
        $permissions = $this->get_permissions_from_token($token);
        foreach ($permissions as $p) {
            if (('/' . $p[0]) === $request || (!$is_for_read && !is_null($refer) && ('/' . $p[0] === $refer))) {
                return $p[2];
            }
        }
        return -1;
    }

    function can_read_resource($permission_level)
    {
        return $permission_level >= 0 && $permission_level <= 1;
    }

    function can_write_resource($permission_level)
    {
        return $permission_level == 0;
    }

    function is_admin($token)
    {
        $permissions = $this->get_permissions_from_token($token);
        foreach ($permissions as $p) {
            if ('admin' === $p[0])
                return $this->can_read_resource($p[2]);

        }
        return false;
    }


    function get_permissions_from_token($token)
    {
        return $this->get_token_payload($token)['pm'];
    }

    function is_user_admin(){
        return intval($this->get_token_payload($this->get_token())['rlid']) === $this::ADMIN_ROLE_ID;
    }
    function is_user_super_admin(){
        return intval($this->get_token_payload($this->get_token())['rlid']) === $this::SUPER_ADMIN_ROLE_ID;
    }
}
