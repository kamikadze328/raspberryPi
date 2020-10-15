<?php
/** @noinspection SqlResolve */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';


class DBManager
{
    private ?string $host = null;
    private ?string $db_name = null;
    private ?string $username = null;
    private ?string $password = null;
    private ?PDO $conn = null;
    private string $USERS_TABLE = "users";
    private string $USER_TOKENS_TABLE = "user_tokens";
    private string $USER_STATS_TABLE = "user_statistics";
    private string $URLs_TABLE = 'URLs';
    private string $USER_SESSIONS_TABLE = 'user_sessions';
    private string $USER_ROLE_TO_PERMISSION_TABLE = 'user_roleToPermissions';
    private string $URL_PERMISSIONS_TABLE = 'URL_PERMISSIONS';
    private string $USER_ROLES_TABLE = 'user_roles';


    public array $error_msg = array(); //TODO

    public function __construct()
    {
        //TODO костыль с первым в списке.
        $servers = read_config();
        if (count($servers) > 0) {
            $this->host = $servers[0]["host"];
            $this->db_name = $servers[0]["database"];
            $this->username = $servers[0]["user"];
            $this->password = $servers[0]["password"];
        }
    }

    function connect()
    {
        $this->error_msg = array();
        try {
            $dsn = 'mysql:host=' . $this->host
                . ';dbname=' . $this->db_name
                . ';charset=utf8';
            $this->conn = new PDO($dsn, $this->username, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            return true;

        } catch (PDOException $exception) {
            return false;
        }
    }

    function create_user($user)
    {
        try {
            $query = "INSERT INTO {$this->USERS_TABLE} 
                        (login, description, password, role_id)
                    VALUES(:login, :description, :password, :role_id)";

            $stmt = $this->conn->prepare($query);
            if ($stmt->execute([
                'login' => $user->login,
                'description' => $user->description,
                'password' => $user->passowrd_secure,
                'role_id' => $user->role_id
            ])) {

                $user->id = $this->conn->lastInsertId();
                return true;
            } else {
                $this->error_msg = $stmt->errorInfo();
                return false;
            }
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    function user_exists($user)
    {
        try {
            $query = "SELECT id, password, role_id
            FROM {$this->USERS_TABLE}
            WHERE login = ?
            LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user->login);
            $stmt->execute();
            $num = $stmt->rowCount();
            if ($num > 0) {

                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                $user->id = intval($row['id']);
                $user->password_from_db = $row['password'];
                $user->role_id = intval($row['role_id']);
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }
    function user_exists_by_id($user)
    {
        try {
            $query = "SELECT login, password, role_id
            FROM {$this->USERS_TABLE}
            WHERE id = ?
            LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user->id);
            $stmt->execute();
            $num = $stmt->rowCount();
            if ($num > 0) {

                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                $user->login = $row['login'];
                $user->password_from_db = $row['password'];
                $user->role_id = intval($row['role_id']);
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    function save_user_token($user, $token, $expires)
    {
        try {
            $this->conn->beginTransaction();
            if ($this->get_count_tokens_of_user($user->id) > 5) {
                $this->delete_all_tokens_of_user($user->id);
            }

            $query = "INSERT INTO {$this->USER_TOKENS_TABLE}
                                (token, user_id, expires)
                                VALUES(:token, :user_id, :expires);";
            $stmt = $this->conn->prepare($query);
            if ($stmt->execute([
                'token' => $token,
                'user_id' => $user->id,
                'expires' => $expires])) {
                $this->conn->commit();
                return true;
            } else {
                $this->error_msg = $stmt->errorInfo();
                $this->conn->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            $this->conn->rollBack();
            return false;
        }
    }

    function delete_user_token($token, $user_id)
    {
        try {
            $query = "DELETE FROM {$this->USER_TOKENS_TABLE} 
                        WHERE user_id=:user_id and token=:token";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute(['user_id' => $user_id, 'token' => $token]);
            if (!$result) $this->error_msg = $stmt->errorInfo();
            $stmt->closeCursor();
            return $result;
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    private function get_count_tokens_of_user($user_id)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->USER_TOKENS_TABLE} 
                            WHERE user_id = :user_id;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['user_id' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return intval($result['count']);
    }

    private function delete_all_tokens_of_user($user_id)
    {
        $query = "DELETE FROM {$this->USER_TOKENS_TABLE} 
                                WHERE user_id = :user_id;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['user_id' => $user_id]);
        $stmt->closeCursor();
    }

    function is_valid_token($token, $user_id)
    {
        try {
            $query = "SELECT expires FROM {$this->USER_TOKENS_TABLE}
                        WHERE user_id=:user_id AND token=:token";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['user_id' => $user_id, 'token' => $token]);
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return intval($result['expires']) > time();
            } else return false;

        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    function update_user_passwd($user)
    {
        try {
            $query = "UPDATE {$this->USERS_TABLE} 
                        SET password=:password 
                        WHERE id=:user_id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute(['password' => $user->password_secure, 'user_id' => $user->id]);
            if (!$result) $this->error_msg = $stmt->errorInfo();
            $stmt->closeCursor();
            return $result;
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    function delete_user($user_id)
    {
        try {
            $query = "DELETE FROM {$this->USERS_TABLE} 
                        WHERE id=:user_id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute(['user_id' => $user_id]);
            if (!$result) $this->error_msg = $stmt->errorInfo();
            $stmt->closeCursor();
            return $result;
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    private function get_path_id($path)
    {
        try {
            $query = "SELECT id FROM {$this->URLs_TABLE}
                            WHERE path=:path";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['path' => $path]);
            if ($stmt->rowCount() > 0) {
                $path_id = intval($stmt->fetch(PDO::FETCH_ASSOC)['id']);
                $stmt->closeCursor();
                return $path_id;
            } else return false;
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }


    function save_user_stat($path, $user_id, $device, $time, $session_id, $ip)
    {
        try {
            $path_id = $this->get_path_id($path);
            if ($path_id !== false) {
                if (is_null($session_id)) {
                    $session_id = $this->init_user_session($user_id, $device, $ip);
                    return ($session_id === false) ?
                        false :
                        $this->init_user_stat($path_id, $time, $session_id);
                } else {
                    $stat_id = $this->get_id_last_equals_stat($time, $path_id, $session_id);
                    if ($stat_id === false) return false;
                    return is_null($stat_id) ?
                        $this->init_user_stat($path_id, $time, $session_id) :
                        ($this->update_user_stat($stat_id, $time) ? $session_id : false);
                }
            } else return false;
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    private function get_id_last_equals_stat($current_time, $url_id, $session_id)
    {
        try {
            include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';

            $query = "SELECT id FROM {$this->USER_STATS_TABLE} 
                        WHERE end_time > :max_end_time and url_id=:url_id and session_id=:session_id
                        ORDER BY end_time desc
                        LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'url_id' => $url_id,
                'max_end_time' => ($current_time - SecurityManager::SESSION_LIFETIME),
                'session_id' => $session_id]);
            $count = $stmt->rowCount();
            $stat_id = ($count > 0) ? intval($stmt->fetch(PDO::FETCH_ASSOC)['id']) : null;
            $stmt->closeCursor();
            return $stat_id;
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    private function init_user_stat($path_id, $current_time, $session_id)
    {
        try {
            $query = "INSERT INTO {$this->USER_STATS_TABLE} 
                       (start_time, end_time, url_id, session_id)
                        VALUES(:start_time, :end_time, :url_id, :session_id)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'url_id' => $path_id,
                'start_time' => $current_time,
                'end_time' => $current_time,
                'session_id' => $session_id
            ]);
            $stmt->closeCursor();
            return $session_id;
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    private function init_user_session($user_id, $device, $ip)
    {
        try {
            $query = "INSERT INTO {$this->USER_SESSIONS_TABLE} 
                        (user_id, device, ip)
                        VALUES (:user_id, :device, :ip);";
            $stmt = $this->conn->prepare($query);
            $session_id = $stmt->execute([
                'user_id' => $user_id,
                'device' => $device,
                'ip' => $ip
            ]) ?
                intval($this->conn->lastInsertId()) :
                false;
            $stmt->closeCursor();
            return $session_id;
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    private function update_user_stat($stat_row_id, $current_time)
    {
        try {
            $query = "UPDATE {$this->USER_STATS_TABLE} 
                        SET end_time=:end_time
                        WHERE id=:row_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['row_id' => $stat_row_id, 'end_time' => $current_time]);
            $stmt->closeCursor();
            return true;
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    function get_sessions($date_min, $date_max)
    {
        try {
            $query = "SELECT sess.id as session_id,
                                us.start_time as start_time, 
                                us.end_time as end_time,
                                sess.device as device,
                                UL.path as URL_path,
                                UL.name as URL_name,
                                u.login as username,
                                sess.ip as ip
                    FROM {$this->USER_SESSIONS_TABLE} sess 
                    INNER JOIN {$this->USER_STATS_TABLE} us on sess.id = us.session_id 
                    LEFT JOIN {$this->URLs_TABLE} UL on us.url_id = UL.id
                    LEFT JOIN {$this->USERS_TABLE} u on sess.user_id = u.id
                    WHERE us.start_time between :date_min and :date_max
                    ORDER BY session_id, start_time";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':date_min', $date_min, PDO::PARAM_INT);
            $stmt->bindParam(':date_max', $date_max, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $result_set = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                $returned_array = array();
                if (count($result_set) > 0) {
                    $ip = is_null($result_set[0]['ip']) ? null : intval($result_set[0]['ip']);
                    $ip = is_null($ip) ? null : '' . intdiv($ip, 1000) . '.' . ($ip % 1000) . '.0' . '.0';
                    $prev_session = array(
                        'session_id' => $result_set[0]['session_id'],
                        'device' => $result_set[0]['device'],
                        'username' => $result_set[0]['username'],
                        'ip' => $ip
                    );
                    $stats = array();
                    foreach ($result_set as $result) {
                        if ($prev_session['session_id'] != $result['session_id']) {
                            $returned_array[] = array(
                                'device' => intval($prev_session['device']),
                                'username' => $prev_session['username'],
                                'ip' => $prev_session['ip'],
                                'stats' => $stats
                            );
                            $stats = array();
                            $ip = is_null($result['ip']) ? null : intval($result['ip']);
                            $ip = is_null($ip) ? null : '' . intdiv($ip, 1000) . '.' . ($ip % 1000) . '.0' . '.0';
                            $prev_session = array(
                                'session_id' => $result['session_id'],
                                'device' => $result['device'],
                                'username' => $result['username'],
                                'ip' => $ip
                            );
                        }
                        $stats[] = array(
                            'start_time' => intval($result['start_time']),
                            'duration_sec' => (intval($result['end_time']) - intval($result['start_time'])),
                            'url_name' => $result['URL_name'],
                            'url_path' => $result['URL_path'],
                        );
                    }
                    $returned_array[] = array(
                        'device' => intval($prev_session['device']),
                        'username' => $prev_session['username'],
                        'ip' => $prev_session['ip'],
                        'stats' => $stats,
                    );

                    return $returned_array;
                } else return array();
            } else {
                $this->error_msg = $stmt->errorInfo();
                return false;
            }
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }


    function get_users_with_stats($date_min, $date_max)
    {
        try {
            $query = "SELECT u.id as id, 
                            u.login as login,
                            ur.name as role,
                            u.description as description,
                            ifnull(max(start_time), 0) as last_session, 
                            count(us.id) as count_sessions, 
                            ifnull(avg(end_time - start_time), 0) as average_time_session,
                            count(distinct us.ip) as count_distinct_places
                        FROM {$this->USER_STATS_TABLE}
                            JOIN {$this->USER_SESSIONS_TABLE} us ON us.id = {$this->USER_STATS_TABLE}.session_id
                                AND start_time between :date_min and :date_max
                                AND user_id > -1
                            RIGHT JOIN {$this->USERS_TABLE} u on u.id = us.user_id
                            RIGHT JOIN {$this->USER_ROLES_TABLE} ur on ur.id = u.role_id
                        WHERE u.id > -1 GROUP BY u.id ORDER BY u.login";
            $stmt = $this->conn->prepare($query);
            if ($stmt->execute(['date_min' => $date_min, 'date_max' => $date_max])) {
                $result_set = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                return $result_set;
            } else {
                $this->error_msg = $stmt->errorInfo();
                return false;
            }
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    function get_role_permissions_by_id($role_id)
    {
        try {
            $query = "SELECT path, url_id, `read` as r, `write` as w 
                        FROM {$this->USER_ROLE_TO_PERMISSION_TABLE}
                        INNER JOIN {$this->URL_PERMISSIONS_TABLE} UP on UP.id = {$this->USER_ROLE_TO_PERMISSION_TABLE}.permission_id
                        INNER JOIN {$this->URLs_TABLE} UL on UL.id = UP.url_id                        
                            WHERE role_id = :role_id";
            $stmt = $this->conn->prepare($query);
            if ($stmt->execute(['role_id' => $role_id])) {
                $result_set = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                return $result_set;
            } else {
                $this->error_msg = $stmt->errorInfo();
                return false;
            }
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    function get_roles(){
        try {
            $query = "SELECT id, name 
                        FROM {$this->USER_ROLES_TABLE};";
            $stmt = $this->conn->prepare($query);
            if ($stmt->execute()) {
                $result_set = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                return $result_set;
            } else {
                $this->error_msg = $stmt->errorInfo();
                $stmt->closeCursor();
                return false;
            }
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    function get_roles_with_permissions()
    {
        try {
            $query = "SELECT ur.id as id,
                             ur.name as name,
                             ur.description as description,
                             UL.name as URL_name,
                             UL.id as URL_id,
                            `read` as r,
                            `write` as w 
                        FROM {$this->USER_ROLE_TO_PERMISSION_TABLE} as rToP
                        JOIN {$this->USER_ROLES_TABLE} ur on ur.id = rToP.role_id
                        JOIN {$this->URL_PERMISSIONS_TABLE} UP on UP.id = rToP.permission_id
                        JOIN {$this->URLs_TABLE} UL on UL.id = UP.url_id
                            ORDER BY ur.name;";
            $stmt = $this->conn->prepare($query);
            if ($stmt->execute()) {
                $result_set = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                $returned_array = array();


                if (count($result_set) > 0) {
                    $prev_role = array('id' => $result_set[0]['id']);
                    $permissions = array();
                    foreach ($result_set as $result) {
                        if ($prev_role['id'] != $result['id']) {
                            $returned_array[] = array(
                                'id' => intval($result_set[0]['id']),
                                'name' => $result_set[0]['name'],
                                'description' => $result_set[0]['description'],
                                'permissions' => $permissions
                            );
                            $permissions = array();
                            $prev_role = array(
                                'id' => $result_set[0]['id'],
                                'name' => $result_set[0]['name'],
                                'description' => $result_set[0]['description']
                            );
                        }
                        $permissions[] = array(
                            'r' => intval($result['r']),
                            'w' => intval($result['w']),
                            'url_name' => $result['URL_name'],
                            'url_id' => $result['URL_id']
                        );
                    }
                    $returned_array[] = array(
                        'id' => intval($result_set[0]['id']),
                        'name' => $result_set[0]['name'],
                        'description' => $result_set[0]['description'],
                        'permissions' => $permissions
                    );
                    return $returned_array;
                } else return array();
            } else {
                $this->error_msg = $stmt->errorInfo();
                $stmt->closeCursor();
                return false;
            }
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }

    function create_role($name, $description, $permissions)
    {
        try {
            $this->conn->beginTransaction();
            $url_permissions = $this->get_array_url_permission($permissions);
            if ($url_permissions !== false) {
                $role_id = $this->create_role_row($name, $description);
                if ($role_id !== false) {
                    $result = $this->insert_roleToPermissions($role_id, $url_permissions);
                    if ($result) {
                        $this->conn->commit();
                        return $role_id;
                    } else {
                        $this->conn->rollBack();
                        return false;
                    }
                } else {
                    $this->conn->rollBack();
                    return false;
                }
            } else {
                $this->conn->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            $this->conn->rollBack();
            return false;
        }
    }

    private function get_array_url_permission($permission)
    {
        $i = 0;
        $len = count($permission);
        if ($len === 0) return false;
        $str = "";
        foreach ($permission as $p) {
            if (isset($p['url_id']) && is_numeric($p['url_id']) && ((int)$p['url_id'] == $p['url_id'])
                && isset($p['write']) && is_numeric($p['write']) && ((int)$p['write'] == $p['write']) && ((int)$p['write'] === 0 || (int)$p['write'] === 1)
                && isset($p['read']) && is_numeric($p['read']) && ((int)$p['read'] == $p['read']) && ((int)$p['read'] === 0 || (int)$p['read'] === 1)) {
                $url_id = (int)$p['url_id'];
                $w = (int)$p['w'];
                $r = (int)$p['r'];
                $str .= "(url_id={$url_id} and `read`={$r} and `write`={$w})";
                if ($i !== $len - 1)
                    $str .= " or ";
                $i++;
            } else return false;
        }
        $query = "SELECT id FROM {$this->URL_PERMISSIONS_TABLE} 
                            WHERE " . $str . ";";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return count($result) === $len ? $result : false;
        } else {
            $this->error_msg = $stmt->errorInfo();
            $stmt->closeCursor();
            return false;
        }
    }

    private function create_role_row($name, $description)
    {
        $query = "INSERT INTO {$this->USER_ROLES_TABLE}
                                (name, description)
                                VALUE(:name, :description);";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute(['name' => $name, 'user_id' => $description])) {
            $role_id = $this->conn->lastInsertId();
            $stmt->closeCursor();
            return $role_id;
        } else {
            $this->error_msg = $stmt->errorInfo();
            $stmt->closeCursor();
            return false;
        }
    }

    private function insert_roleToPermissions($role_id, $permission_url_id)
    {
        $str = "";
        $i = 0;
        $len = count($permission_url_id);
        foreach ($permission_url_id as $up) {
            $str .= "({$role_id},{$up})";
            if ($i !== $len - 1)
                $str .= ",";
        }
        $query = "INSERT INTO {$this->USER_ROLE_TO_PERMISSION_TABLE}(role_id, permission_id) VALUES " . $str . ";";
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute();
        if (!$result)
            $this->error_msg = $stmt->errorInfo();
        $stmt->closeCursor();
        return $result;
    }

    function update_role($role_id, $permissions)
    {
        try {
            $this->conn->beginTransaction();
            $url_permissions = $this->get_array_url_permission($permissions);
            if ($url_permissions !== false) {
                $result = $this->drop_roleToPermissions($role_id);
                if ($result) {
                    $this->conn->commit();
                    return $role_id;
                } else {
                    $this->conn->rollBack();
                    return false;
                }

            } else {
                $this->conn->rollBack();
                return false;
            }

        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            $this->conn->rollBack();
            return false;
        }
    }

    private function drop_roleToPermissions($role_id)
    {

        $query = "DELETE FROM {$this->USER_ROLE_TO_PERMISSION_TABLE} 
                    WHERE role_id=:role_id";
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute(['role_id' => $role_id]);
        if (!$result)
            $this->error_msg = $stmt->errorInfo();
        $stmt->closeCursor();
        return $result;
    }

    function delete_role($role_id)
    {
        try {
            if ($role_id === $this->DEFAULT_ROLE_ID) {
                $this->error_msg[0] = -1111;
                $this->error_msg[1] = 'Нельзя удалить эту роль.';
                return false;
            } else {
                $this->conn->beginTransaction();
                if ($this->set_default_user_role_instead_role($role_id)) {
                    $query = "DELETE FROM {$this->USER_ROLE_TO_PERMISSION_TABLE} 
                                WHERE id=:role_id";
                    $stmt = $this->conn->prepare($query);
                    if ($stmt->execute(['id' => $role_id])) {
                        $stmt->closeCursor();
                        $this->conn->commit();
                        return true;
                    } else {
                        $this->error_msg = $stmt->errorInfo();
                        $stmt->closeCursor();
                        return false;
                    }
                } else {
                    $this->conn->rollBack();
                    return false;
                }
            }
        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            $this->conn->rollBack();
            return false;
        }
    }

    private function set_default_user_role_instead_role($role_id)
    {
        return $this->update_users_role_by_role_id($role_id, $this->DEFAULT_ROLE_ID);
    }

    private function update_users_role_by_role_id($role_id_old, $role_id_new)
    {
        $query = "UPDATE {$this->USERS_TABLE} 
                    SET role_id=:role_id_new
                    WHERE role_id=:role_id_old";
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute(['role_id_old' => $role_id_old, 'role_id_new' => $role_id_new]);
        if (!$result)
            $this->error_msg = $stmt->errorInfo();
        $stmt->closeCursor();
        return $result;

    }

    function update_user_by_id($user_id, $new_role_id, $new_login)
    {
        try {
            $query = "UPDATE {$this->USERS_TABLE} 
                    SET role_id=:role_id_new, login=:login_new 
                    WHERE id=:user_id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                'user_id' => $user_id,
                'role_id_new' => $new_role_id,
                'login_new' => $new_login
            ]);
            if (!$result)
                $this->error_msg = $stmt->errorInfo();

            $stmt->closeCursor();
            return $result;

        } catch (PDOException $e) {
            $this->error_msg[0] = -1;
            $this->error_msg[1] = $e->getMessage();
            return false;
        }
    }
}
