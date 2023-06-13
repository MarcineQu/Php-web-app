<?php
include_once "classes/Page.php";
include_once "classes/Pdo_.php";
Page::display_header("Main page");
session_start();
$Pdo = new Pdo_();
// Log user in – the first factor of autentication
if (isset($_REQUEST['log_user_in'])) {

    $password = $_REQUEST['password'];
    $login = $_REQUEST['login'];
    if ($Pdo->get_auth($login)) {
        $result = $Pdo->log_2F_step1($login, $password);
        if ($result['result'] == 'success') {
            echo "Success: " . $login;
            $_SESSION['last_time_active'] = time();
            $_SESSION['login'] = $login;
            $_SESSION['logged'] = 'After first step';
            ?>
            <hr>
            <P> Please check your email account 
                and type here the code you have been mailed.</P>
            <form method="post" action="index.php">
                <table>
                    <tr>
                        <td>CODE</td>
                        <td>
                            <label for="name"></label>
                            <input required type="text" name="code" id="code" size="40" />
                        </td>
                    </tr>
                </table>
                <input type="submit" id= "submit" value="Log in" name="log_user_in2">
            </form>

            <?php
        } else {
            echo 'Incorrect login or password.';
        }
    } else {
        $password = $_REQUEST['password'];
        $login = $_REQUEST['login'];
        $_SESSION['last_time_active'] = time();
        $_SESSION['login'] = $login;
        $_SESSION['logged'] = 'After first step';
        $result = $Pdo->log_user_in($login, $password);
        $userId = $Pdo->getUserId($login);
        //print_r($userId);
        $roles = $Pdo->getRoles();
       // print_r($roles);
        $roles = $Pdo->getUserRolesId($userId);
        //echo '<BR> user roles ';
        //print_r($roles);

        $_SESSION['roles'] = $roles;

// Pobierz uprawnienia użytkownika na podstawie ról
        $privileges = array();
        foreach ($roles as $role) {
            $rolePrivileges = $Pdo->getRolePrivileges($role);
            //echo "<BR>Role przywileje";
            //print_r($rolePrivileges);
            $privileges[] = $rolePrivileges;
        }

       // echo "<BR>z roli  przywileje";
        //print_r($privileges);

// Dodaj dodatkowe przywileje użytkownika nie wynikające z roli
        $extraPrivileges = $Pdo->get_privileges($userId);
        //echo "<BR> extra przywileje";
        //print_r($extraPrivileges);
        $privileges[] = $extraPrivileges;

// Połącz przywileje z tablicy wielowymiarowej w jedną tablicę
        $mergedPrivileges = array();
        foreach ($privileges as $privilege) {
            $mergedPrivileges = array_merge($mergedPrivileges, $privilege['data']);
        }

        $_SESSION['privileges'] = $mergedPrivileges;

        echo "<br>";

       // echo "Przywileje dla zalogowanego użytkownika:<br>";
        //print_r($_SESSION['privileges']);
    }
}




