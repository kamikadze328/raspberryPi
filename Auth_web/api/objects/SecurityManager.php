<?php
class SecurityManager
{
    private $RANDOM_PASSWORD_LENGTH = 5;
    private $TOKEN_LENGTH = 30;
    private $TOKEN_LIFETIME = 604800; //week
    const COOKIE_DOMAIN = 'carbon-dv.ru'; //$_SERVER['HTTP_HOST']
    private $COOKIE_SECURE_FLAG = false; //TODO !!!!!!!11
    private $COOKIE_HTTPONLY = true;   //TODO
    private $COOKIE_SAMESITE = 'Strict';
    private $COOKIE_TOKEN_NAME = 'access_token';
    private $COOKIE_USER_META_NAME = 'user_meta';
    private $SESSION_COOKIE_NAME = 'SUID';
    const SESSION_LIFETIME = 600;

    function isset_token(){
        return isset($_COOKIE[$this->COOKIE_TOKEN_NAME]);
    }
    function isset_user_meta(){
        return isset($_COOKIE[$this->COOKIE_USER_META_NAME]);
    }
    function get_domain(){
        return $this::COOKIE_DOMAIN;
    }
    function get_token(){
        return $_COOKIE[$this->COOKIE_TOKEN_NAME];
    }
    function get_user_meta(){
        return $_COOKIE[$this->COOKIE_USER_META_NAME];
    }
    private function decode_token($token)
    {
        return json_decode(base64_decode($token), true);
    }
    private function get_token_payload($token){
        return $this->decode_token($token)['payload'];
    }
    function get_user_id_by_token($token){
        return $this->get_token_payload($token)['user_id'];
    }
    function generate_token($user)
    {
        $token = $this->generate_random_str($this->TOKEN_LENGTH);
        $full_token = array(
            'token' => $token,
            'payload' => array(
                'user_id' => $user->id
            )
        );
        return base64_encode(json_encode($full_token));
    }
    private function generate_random_str($length){
        return bin2hex(openssl_random_pseudo_bytes($length / 2));

    }
    function get_token_expires_time()
    {
        return time() + $this->TOKEN_LIFETIME;
    }

    function get_session_expires_time(){
        return time() + $this::SESSION_LIFETIME;
    }

    function save_token_on_client($token, $username)
    {
        $cookie_options = $this->get_cookie_options();
        setcookie($this->COOKIE_TOKEN_NAME, $token, $cookie_options);

        $cookie_options['httponly'] = false;
        $user_meta = array('name' => $username);
        setcookie($this->COOKIE_USER_META_NAME, json_encode($user_meta), $cookie_options);

        $this->set_headers();
    }

    function save_session($session_id){
        $cookie_options = $this->get_session_cookie_options();
        setcookie($this->SESSION_COOKIE_NAME, $session_id, $cookie_options);
        $this->set_headers();
    }

    function is_session_exists(){
        return isset($_COOKIE[$this->SESSION_COOKIE_NAME]);
    }
    function get_session_id(){
        return intval($_COOKIE[$this->SESSION_COOKIE_NAME]);
    }

    function is_auth_user(){
        return !is_null($this->get_token());
    }

    function clear_token_on_client()
    {
        $cookie_options = $this->get_old_cookie_options();
        setcookie($this->COOKIE_TOKEN_NAME, '', $cookie_options);

        $cookie_options['httponly'] = false;
        setcookie($this->COOKIE_USER_META_NAME, '', $cookie_options);
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
        return array(
            'expires' => $this->get_session_expires_time(),
            'path' => '/',
            'domain' => $this::COOKIE_DOMAIN,
            'secure' => $this->COOKIE_SECURE_FLAG,
            'httponly' => $this->COOKIE_HTTPONLY,
            'samesite' => $this->COOKIE_SAMESITE
        );
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

    function get_secure_password($password){
        return password_hash($password, PASSWORD_BCRYPT);
    }

    function set_secure_password($user){
        $user->passowrd_secure = $this->get_secure_password($user->password);
    }

    function generate_password(){
        return $this->generate_random_str($this->RANDOM_PASSWORD_LENGTH);
    }
}
