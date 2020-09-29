<?php
/** @noinspection SqlResolve */
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/config/core.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/objects/SecurityManager.php';


class DBManager
{
    private $host = null;
    private $db_name = null;
    private $username = null;
    private $password = null;
    private $conn = null;
    private $USERS_TABLE = "users";
    private $USER_TOKENS_TABLE = "user_tokens";
    private $USER_STATS_TABLE = "user_statistics";
    private $URLs_TABLE = 'URLs';
    private $USER_SESSIONS_TABLE = 'user_sessions';

    public $error_msg = '';

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
        $this->error_msg = '';
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
                        (login, password)
                    VALUES(:login, :password)";

            $stmt = $this->conn->prepare($query);


            if ($stmt->execute(['login' => $user->login, 'password' => $user->password_secure])) {
                $user->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            $this->error_msg = $e->getMessage();
            return false;
        }
    }

    function user_exists($user)
    {
        try {
            $query = "SELECT id, password
            FROM {$this->USERS_TABLE}
            WHERE login = ?
            LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user->login);
            $stmt->execute();
            $num = $stmt->rowCount();
            if ($num > 0) {

                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                $user->id = $row['id'];
                $user->password_from_db = $row['password'];
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->error_msg = $e->getMessage();
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
                $stmt->closeCursor();
                $this->conn->commit();
                return true;
            } else {
                $this->error_msg = $stmt->errorInfo();
                $this->conn->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->error_msg = $e->getMessage();
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
            $stmt->execute(['user_id' => $user_id, 'token' => $token]);
            $stmt->closeCursor();
            return true;
        } catch (PDOException $e) {
            $this->error_msg = $e->getMessage();
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
            $this->error_msg = $e->getMessage();
            return false;
        }
    }

    function update_user_passwd($user_id, $new_passwd_hash)
    {
        try {
            $query = "UPDATE {$this->USERS_TABLE} 
                        SET password=:password 
                        WHERE id=:user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['password' => $new_passwd_hash, 'user_id' => $user_id]);
            $stmt->closeCursor();
            return true;
        } catch (PDOException $e) {
            $this->error_msg = $e->getMessage();
            return false;
        }
    }

    function delete_user($user_id)
    {
        try {
            $query = "DELETE FROM {$this->USERS_TABLE} 
                        WHERE id=:user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['user_id' => $user_id]);
            $stmt->closeCursor();
            return true;
        } catch (PDOException $e) {
            $this->error_msg = $e->getMessage();
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
            $this->error_msg = $e->getMessage();
            return false;
        }
    }


    function save_user_stat($path, $user_id, $device, $time, $session_id)
    {
        try {
            $path_id = $this->get_path_id($path);
            if ($path_id !== false) {
                if (is_null($session_id)) {
                    $session_id = $this->init_user_session($user_id, $device);
                    return ($session_id === false) ?
                        false :
                        $this->init_user_stat($path_id, $time, $session_id);
                } else {
                    $stat_id = $this->get_id_last_equals_stat($time, $path_id, $session_id);
                    if ($stat_id === false) return false;
                    return (is_null($stat_id)) ?
                        $this->init_user_stat($path_id, $time, $session_id) :
                        $this->update_user_stat($stat_id, $time);
                }
            } else return false;
        } catch (PDOException $e) {
            $this->error_msg = $e->getMessage();
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
            $this->error_msg = $e->getMessage();
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
                'session_id' => $session_id]);
            $stmt->closeCursor();
            return $session_id;
        } catch (PDOException $e) {
            $this->error_msg = $e->getMessage();
            return false;
        }
    }

    private function init_user_session($user_id, $device)
    {
        try {
            $query = "INSERT INTO {$this->USER_SESSIONS_TABLE} 
                        (user_id, device)
                        VALUES (:user_id, :device);";
            $stmt = $this->conn->prepare($query);
            $session_id = $stmt->execute(['user_id' => $user_id, 'device' => $device]) ? intval($this->conn->lastInsertId()) : false;
            $stmt->closeCursor();
            return $session_id;
        } catch (PDOException $e) {
            $this->error_msg = $e->getMessage();
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
            $this->error_msg = $e->getMessage();
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
                                u.login as username 
                    FROM {$this->USER_SESSIONS_TABLE} sess 
                    LEFT JOIN {$this->USER_STATS_TABLE} us on sess.id = us.session_id 
                    LEFT JOIN {$this->URLs_TABLE} UL on us.url_id = UL.id
                    LEFT JOIN {$this->USERS_TABLE} u on sess.user_id = u.id
                    WHERE us.start_time between :date_min and :date_max
                    ORDER BY sess.id";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':date_min', $date_min, PDO::PARAM_INT);
            $stmt->bindParam(':date_max', $date_max, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $result_set = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                $returned_array = array();
                if (count($result_set) > 0) {
                    $prev_session = array(
                        'session_id' => $result_set[0]['session_id'],
                        'device' => $result_set[0]['device'],
                        'username' => $result_set[0]['username']
                    );
                    $stats = array();
                    foreach ($result_set as $result) {
                        if ($prev_session['session_id'] != $result['session_id']) {
                            $returned_array[] = array(
                                'device' => intval($prev_session['device']),
                                'username' => $prev_session['username'],
                                'stats' => $stats
                            );
                            $stats = array();
                            $prev_session = array(
                                'session_id' => $result['session_id'],
                                'device' => $result['device'],
                                'username' => $result['username']
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
                        'stats' => $stats
                    );

                    return $returned_array;
                } else return false;
            } else return false;
        } catch (PDOException $e) {
            $this->error_msg = $e->getMessage();
            return false;
        }
    }
}
