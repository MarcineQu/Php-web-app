<?php

include_once "classes/Aes.php";
include_once "classes/M.php";

class Pdo_ {

//put your code here
    private $db;
    private $pepper = 234234234;
    private $purifier;

    public function __construct() {
        //$this->logged_user=null;
        require './htmlpurifier-4.15.0/library/HTMLPurifier.auto.php';
        $config = HTMLPurifier_config::createDefault();
        $this->purifier = new HTMLPurifier($config);
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=news', 'root', '');
        } catch (PDOException $e) {
// add relevant code
            die();
        }
    }

    public function get_privileges($userId) {
        $userId = $this->purifier->purify($userId);
        try {
            $sql = "SELECT p.id, p.name FROM privilege p"
                    . " INNER JOIN user_privilege up ON p.id = up.id_privilege"
                    . " INNER JOIN user u ON u.id = up.id_user"
                    . " WHERE u.id = :userId";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['userId' => $userId]);
            $data = $stmt->fetchAll();

            $result = [
                'status' => 'success',
                'data' => $data
            ];

            foreach ($data as $row) {
                $privilege = $row['name'];
                $_SESSION[$privilege] = 'YES';
            }

            return $result;
        } catch (Exception $e) {
            print 'Exception' . $e->getMessage();
        }

        return [
            'status' => 'failed'
        ];
    }

    public function getUserRoles($userId) {
        // Zapytanie SQL pobierające role użytkownika
        $sql = "SELECT role.role_name
            FROM role
            INNER JOIN user_role ON role.id = user_role.id_role
            WHERE user_role.id_user = :userId";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        $roles = array();

        // Przetwarzanie wyników zapytania
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $roles[] = $row["role_name"];
        }

        return $roles;
    }

    public function getUserRolesId($userId) {
        // SQL query to retrieve role IDs for a user
        $sql = "SELECT role.id
        FROM role
        INNER JOIN user_role ON role.id = user_role.id_role
        WHERE user_role.id_user = :userId";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        $roles = array();

        // Processing query results
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $roles[] = $row["id"];
        }

        return $roles;
    }

    public function addUserRole($userId, $roleId) {
        // Sprawdzenie, czy użytkownik już posiada dodawaną rolę
        $checkQuery = "SELECT COUNT(*) FROM user_role WHERE id_role = :roleId AND id_user = :userId";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
        $checkStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $checkStmt->execute();
        $rowCount = $checkStmt->fetchColumn();

        if ($rowCount > 0) {
            echo "<BR>Użytkownik już posiada tę rolę.";
            return;
        }

        // Dodanie roli dla użytkownika
        $query = "INSERT INTO user_role (id_role, id_user, issue_time) VALUES (:roleId, :userId, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        echo "<BR>Rola została dodana dla użytkownika o ID: $userId.";
    }

    public function removeUserRole($userId, $roleId) {
        // Sprawdzenie, czy użytkownik ma usuwaną daną rolę
        $checkQuery = "SELECT COUNT(*) FROM user_role WHERE id_role = :roleId AND id_user = :userId";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
        $checkStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $checkStmt->execute();
        $rowCount = $checkStmt->fetchColumn();

        if ($rowCount == 0) {
            echo "<BR>Użytkownik nie ma tej roli do usunięcia.";
            return;
        }

        // Usunięcie roli dla użytkownika
        $query = "DELETE FROM user_role WHERE id_user = :userId AND id_role = :roleId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':roleId', $roleId, PDO::PARAM_INT);
        $stmt->execute();

        echo "<BR>Rola została usunięta dla użytkownika o ID: $userId.";
    }

    public function getUserId($login) {
        $sql = "SELECT id FROM user WHERE login = '$login'";
        $result = $this->db->query($sql);

        if ($result->rowCount() > 0) {
            // Pobranie i zwrócenie ID użytkownika
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $userId = $row["id"];
            return $userId;
        } else {
            // Jeżeli użytkownik o podanym loginie nie istnieje, zwróć null
            return null;
        }
    }

    public function get_email($login) {
        $sql = "SELECT email FROM user WHERE login=:login";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':login' => $login]);
        $user_data = $stmt->fetch();
        return $user_data['email'];
    }

    public function get_code($login) {
        $sql = "SELECT sms_code FROM user WHERE login=:login";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':login' => $login]);
        $user_data = $stmt->fetch();
        return $user_data['sms_code'];
    }

    public function get_auth($login) {
        $sql = "SELECT password_form FROM user WHERE login=:login";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':login' => $login]);
        $user_data = $stmt->fetch();
        return $user_data['password_form'];
    }

    public function displayPermissionList() {
        // Pobierz listę uprawnień
        $query = "SELECT * FROM privilege";
        $result = $this->db->query($query);

        if ($result) {
            foreach ($result as $row) {
                echo "Permission ID: " . $row['id'] . "<br>";
                echo "Permission Name: " . $row['name'] . "<br>";
                echo "Asset URL: " . $row['asset_url'] . "<br><br>";
            }
        }
    }

    public function displayUserPermissions($login) {
        // Pobierz uprawnienia użytkownika
        $query = "SELECT privilege.id, privilege.name FROM privilege
              JOIN user_privilege ON privilege.id = user_privilege.id_privilege
              WHERE user.login = :login AND privilege.active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':login', $login, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            foreach ($result as $row) {
                echo "Permission ID: " . $row['id'] . "<br>";
                echo "Permission Name: " . $row['name'] . "<br><br>";
            }
        }
    }

    public function addRole($roleName, $description) {
        // Sprawdź, czy rola już istnieje
        $checkQuery = "SELECT COUNT(*) FROM role WHERE role_name = ?";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([$roleName]);
        $rowCount = $checkStmt->fetchColumn();

        if ($rowCount > 0) {
            echo '<BR>Taka rola już istnieje';
            return false;
        }

        // Rola nie istnieje, dodaj ją do bazy danych
        $insertQuery = "INSERT INTO role (role_name, description) VALUES (?, ?)";
        $insertStmt = $this->db->prepare($insertQuery);
        $insertStmt->execute([$roleName, $description]);
        echo '<BR>Dodano rolę ' . $roleName . '';
        return $insertStmt->rowCount() > 0;
    }

    public function removeRole($roleId) {
        $deleteQuery = "DELETE FROM role WHERE id = ?";
        $deleteStmt = $this->db->prepare($deleteQuery);
        $deleteStmt->execute([$roleId]);
        echo '<BR>Usunięto rolę';
        return $deleteStmt->rowCount() > 0;
    }

    public function addUserPermission($userId, $privilegeId) {
        // Sprawdzenie czy przywilej już istnieje
        $checkSql = "SELECT id_user FROM user_privilege WHERE id_user = '$userId' AND id_privilege = '$privilegeId'";
        $result = $this->db->query($checkSql);

        if ($result->rowCount() > 0) {
            echo "<br>Przywilej już istnieje.";
        } else {
            $insertSql = "INSERT INTO user_privilege (id_user, id_privilege) VALUES ('$userId', '$privilegeId')";

            // Wykonanie zapytania
            try {
                $this->db->query($insertSql);
                echo "<br>Przywilej został dodany.";
            } catch (PDOException $e) {
                echo "<br>Błąd podczas dodawania przywileju: " . $e->getMessage();
            }
        }
    }

    public function getAllUsers() {
        $query = "SELECT * FROM user";

        // Wykonanie zapytania
        $statement = $this->db->query($query);

        // Pobranie wyników zapytania
        $users = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Zwrócenie pobranych użytkowników
        return $users;
    }

    public function getAllPrivileges() {
        $query = "SELECT * FROM privilege";

        // Wykonanie zapytania
        $statement = $this->db->query($query);

        // Pobranie wyników zapytania
        $privileges = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Zwrócenie pobranych użytkowników
        return $privileges;
    }

    public function addRolePermission($roleId, $privilegeId) {
        // Połączenie z bazą danych (zakładając, że masz już wcześniej ustanowione połączenie)
        $connection = $this->db;

        // Sprawdzenie czy przywilej już istnieje dla danej roli
        $checkSql = "SELECT COUNT(*) FROM role_privilege WHERE id_role = '$roleId' AND id_privilege = '$privilegeId'";
        $result = $connection->query($checkSql);
        $row = $result->fetchColumn();

        if ($row > 0) {
            echo "<BR>Ten przywilej już istnieje dla tej roli.";
            return;
        }

        // Sprawdzenie czy istnieje taki przywilej o podanym id
        $checkPrivilegeSql = "SELECT COUNT(*) FROM privilege WHERE id = '$privilegeId'";
        $privilegeResult = $connection->query($checkPrivilegeSql);
        $privilegeRow = $privilegeResult->fetchColumn();

        if ($privilegeRow == 0) {
            echo "<BR>Taki przywilej o podanym id nie istnieje.";
            return;
        }

        // Zapytanie SQL dodające uprawnienie dla roli
        $insertSql = "INSERT INTO role_privilege (id_role, id_privilege) VALUES ('$roleId', '$privilegeId')";

        // Wykonanie zapytania
        if ($connection->query($insertSql)) {
            echo "<BR>Uprawnienie zostało dodane dla roli.";
        } else {
            echo "<BR>Błąd: " . $connection->errorInfo()[2];
        }
    }

    public function removeRolePermission($roleId, $privilegeId) {
        // Połączenie z bazą danych (zakładając, że masz już wcześniej ustanowione połączenie)
        $connection = $this->db;

        // Sprawdzenie czy przywilej istnieje dla danej roli
        $checkSql = "SELECT COUNT(*) FROM role_privilege WHERE id_role = '$roleId' AND id_privilege = '$privilegeId'";
        $result = $connection->query($checkSql);
        $row = $result->fetchColumn();

        if ($row == 0) {
            echo "<BR>Ten przywilej nie istnieje dla tej roli.";
            return;
        }

        // Zapytanie SQL usuwające uprawnienie dla roli
        $deleteSql = "DELETE FROM role_privilege WHERE id_role = '$roleId' AND id_privilege = '$privilegeId'";

        // Wykonanie zapytania
        if ($connection->query($deleteSql)) {
            echo "<BR>Uprawnienie zostało usunięte dla roli.";
        } else {
            echo "<BR>Błąd: " . $connection->errorInfo()[2];
        }
    }

    public function removeUserPermission($userId, $privilegeId) {
        // Sprawdzenie, czy użytkownik ma wybrany przywilej
        $checkSql = "SELECT * FROM user_privilege WHERE id_user='$userId' AND id_privilege='$privilegeId'";
        $checkStmt = $this->db->query($checkSql);
        $checkResult = $checkStmt->fetch();

        if ($checkResult) {
            // Użytkownik ma wybrany przywilej, więc usuwamy go
            $sql = "DELETE FROM user_privilege WHERE id_user='$userId' AND id_privilege='$privilegeId'";

            // Wykonanie zapytania
            try {
                $this->db->query($sql);
                echo "<br>Przywilej został usunięty.";
            } catch (PDOException $e) {
                echo "<br>Błąd podczas usuwania przywileju: " . $e->getMessage();
            }
        } else {
            // Użytkownik nie ma wybranego przywileju
            echo "<br>Użytkownik nie miał wybranego przywileju.";
        }
    }

    function getRoles() {
        // Zapytanie SQL do pobrania ról
        $query = "SELECT * FROM role";
        $statement = $this->db->query($query);

        // Pobieranie wyników zapytania
        $roles = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $roles;
    }

    public function getRolePrivileges($roleId) {
        try {
            $query = "SELECT p.id, p.name FROM privilege p
                  INNER JOIN role_privilege rp ON p.id = rp.id_privilege
                  WHERE rp.id_role = :role_id";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':role_id', $roleId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                return array('status' => 'success', 'data' => $result);
            } else {
                return array('status' => 'error', 'message' => 'No privileges found for the role.');
            }
        } catch (PDOException $e) {
            return array('status' => 'error', 'message' => 'Error retrieving role privileges: ' . $e->getMessage());
        }
    }

    public function displayRoleList() {
        $query = "SELECT * FROM role";
        $result = $this->db->query($query);

        if ($result) {
            foreach ($result as $row) {
                echo "Role ID: " . $row['id'] . "<br>";
                echo "Role Name: " . $row['role_name'] . "<br>";
                echo "Description: " . $row['description'] . "<br><br>";
            }
        }
    }

    public function add_user($login, $email, $password, $password_form) {


        //generate salt

        $salt = random_bytes(16);
        $login = $this->purifier->purify($login);
        $email = $this->purifier->purify($email);
        try {
            $sql = "INSERT INTO `user`( `login`, `email`, `hash`, `salt`, `id_status`, `password_form`) 
 VALUES (:login,:email,:hash,:salt,:id_status,:password_form)";
//hash password
//$password = hash('sha512', $password);
//hash password with salt
            $password = /* $Aes->encrypt( */hash('sha512', $password . $salt . $this->pepper)/* ) */; // mozna tez dodac pieprz pepper ale nie zaleca się stosowania go

            $data = [
                'login' => $login,
                'email' => $email,
                'hash' => $password,
                'salt' => $salt,
                'id_status' => '1',
                'password_form' => $password_form
            ];
            $this->db->prepare($sql)->execute($data);
        } catch (Exception $e) {
//modify the code here
            print 'Exception' . $e->getMessage();
        }
    }

    public function log_user_in($login, $password) {


        $login = $this->purifier->purify($login);
        try {
            $sql = "SELECT id,hash,login,salt FROM user WHERE login=:login";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['login' => $login]);
            $user_data = $stmt->fetch();
            $password = hash('sha512', $password . $user_data['salt'] . $this->pepper);

            if ($password == $user_data['hash']) {
                echo 'login successfull<BR/>';
                //session
                if (isset($_SESSION['last_time_active'])) {
                    $_SESSION['last_time_active'] = time() + 5 * 60;
                    $_SESSION['active'] = 1;
                }
                //session
                echo 'You are logged in as: ' . $user_data['login'] . '<BR/>';
            } else {
                echo 'login FAILED<BR/>';
            }
        } catch (Exception $e) {
//modify the code here
            print 'Exception' . $e->getMessage();
        }
    }

    public function change_password($login, $password) {



        $login = $this->purifier->purify($login);
//echo "Zmienna login: ".$login;


        $sql = "UPDATE user SET hash=:hash, salt=:salt WHERE login=:login";
        try {


            $salt = random_bytes(16);
            $hash = hash('sha512', $password . $salt . $this->pepper);

            $stmt = $this->db->prepare($sql);
            $data = [
                ':login' => $login,
                ':hash' => $hash,
                ':salt' => $salt
            ];
            $stmt->execute($data);
//$user_data = $stmt->fetch();
        } catch (Exception $e) {
//modify the code here
            print 'Exception' . $e->getMessage();
        }
    }

    public function log_2F_step1($login, $password) {

        $login = $this->purifier->purify($login);
        try {
            $sql = "SELECT id,hash,login,salt,email FROM user WHERE login=:login";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['login' => $login]);
            $user_data = $stmt->fetch();
            $password = hash('sha512', $password . $user_data['salt'] . $this->pepper);
            if ($password == $user_data['hash']) {
                //generate and send OTP
                $otp = random_int(100000, 999999);
                $code_lifetime = date('Y-m-d H:i:s', time() + 300);
                try {
                    $sql = "UPDATE `user` SET `sms_code`=:code,
`code_timelife`=:lifetime WHERE login=:login";
                    $data = [
                        'login' => $login,
                        'code' => $otp,
                        'lifetime' => $code_lifetime
                    ];
                    $this->db->prepare($sql)->execute($data);
                    $m = new \PHPMailer\src\Exception\M();
                    $m->send_email($this->get_email($login), $otp);
                    $result = [
                        'result' => 'success'
                    ];
                    return $result;
                } catch (Exception $e) {
                    print 'Exception' . $e->getMessage();
                    //add necessary code here
                }
            } else {
                echo 'login FAILED<BR/>';
                $result = [
                    'result' => 'failed'
                ];
                return $result;
            }
        } catch (Exception $e) {
            print 'Exception' . $e->getMessage();
            //add necessary code here
        }
    }

   public function log_2F_step2($login, $code) {
    $login = $this->purifier->purify($login);
    $code = $this->purifier->purify($code);
    try {
        $sql = "SELECT id, login, sms_code, code_timelife FROM user WHERE login=:login";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['login' => $login]);
        $user_data = $stmt->fetch();
        if ($code == $user_data['sms_code'] && time() < strtotime($user_data['code_timelife'])) {
            $id_user = $user_data['id']; // Pobranie ID użytkownika z danych użytkownika
            
            // Poniżej wywołaj funkcję register_user_login() z odpowiednimi argumentami
            $this->register_user_login($id_user, $_SERVER['REMOTE_ADDR'], 1, 'undetected');
            
            echo 'Login successful<BR/>';
            
            //session
            if (isset($_SESSION['last_time_active'])) {
                $_SESSION['last_time_active'] = time() + 5 * 60;
                $_SESSION['active'] = 1;
            }
            
            return true;
        } else {
            echo 'Login FAILED<BR/>';
            return false;
        }
    } catch (Exception $e) {
        print 'Exception' . $e->getMessage();
    }
}


    public function register_user_login($id_user, $ip_address, $correct, $computer) {
        //id_user=-1 - no such a user
        $id_user = $this->purifier->purify($id_user);
        $ip_address = $this->purifier->purify($ip_address);
        $correct = $this->purifier->purify($correct);
        $computer = $this->purifier->purify($computer);
        if (filter_var($id_user, FILTER_VALIDATE_INT)) {
            if ($id_user == -1) {
                //no such a user: incorrect login
                //add necessary code here
            } else {
                //Existing user login
                //check if IP address is registered in DB
                try {
                    $sql = "SELECT id FROM ip_address WHERE adres_ip=:adres_ip";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute(['adres_ip' => $ip_address]);
                    $data = $stmt->fetch();
                    if (empty($data['id'])) {
                        //IP address not registered. Register in db
                        $sql = "INSERT INTO `ip_address`(`ok_login_num`, `bad_login_num`,
`last_bad_login_num`, `permanent_lock`, `adres_IP`) "
                                . " VALUES (0,0,0,0,:ip_address)";
                        $this->db->prepare($sql)->execute(['ip_address' => $ip_address]);
                        //check id of inserted record
                        $sql = "SELECT id FROM ip_address WHERE adres_ip=:adres_ip";
                        $stmt = $this->db->prepare($sql);
                        $stmt->execute(['adres_ip' => $ip_address]);
                        $data = $stmt->fetch();
                    }
                    $sql = "INSERT INTO `user_login`( `time`, `correct`, `id_user`, `computer`,
`id_address`)
 VALUES (:time,:correct,:id_user,:computer,:id_address)";
                    $data = [
                        'time' => date('Y-m-d H:i:s', time()),
                        'correct' => 1,
                        'id_user' => $id_user,
                        'computer' => 'undetected',
                        'id_address' => $data['id']
                    ];
                    $this->db->prepare($sql)->execute($data);
                } catch (Exception $e) {
                    print 'Exception' . $e->getMessage();
                }
            }
        }
    }

}
