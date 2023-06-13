<!DOCTYPE html>

<?php
include_once "classes/Page.php";
include_once "classes/Pdo_.php";
include_once "login.php";
Page::display_header("Main page");
$Pdo = new Pdo_();
//session
if (isset($_SESSION['active'])) {
    if (isset($_SESSION['login']) && !empty($_SESSION['login'])) {
        echo "Zalogowano jako: " . $_SESSION['login'];

        //header("Location: messages.php");
    } else {
        echo "SESJA WYGASŁA ZALOGUJ SIĘ\n";
    }
} else {
    echo "SESJA WYGASŁA ZALOGUJ SIĘ\n";
}
// adding new user
if (isset($_REQUEST['add_user'])) {
    $login = $_REQUEST['login'];
    $email = $_REQUEST['email'];
    $password = $_REQUEST['password'];
    $password2 = $_REQUEST['password2'];
    $auth = $_REQUEST['auth'];
    if ($password == $password2) {
        $Pdo->add_user($login, $email, $password, $auth);
        echo "Dodano użytkownika: $login";
    } else {
        echo 'Passwords doesn\'t match';
    }
}
//log user old
/*
  if (isset($_REQUEST['log_user_in'])) {
  $password = $_REQUEST['password'];
  $login = $_REQUEST['login'];
  $Pdo->log_user_in($login, $password);
  }
 */

// Log user in

if (isset($_REQUEST['log_user_in2'])) {

    $code = $_REQUEST['code'];

    $login = $_SESSION['login'];
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
    
    if ($Pdo->log_2F_step2($login, $code)) {
        echo 'You are logged in as: ' . $_SESSION['login'];
        $_SESSION['logged'] = 'YES';   
    } else
        echo 'BŁĄD';
}
if (isset($_REQUEST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
}

//change password
if (isset($_REQUEST['change_password'])) {
    $password = $_REQUEST['password'];
    $login = $_REQUEST['login'];
    $Pdo->change_password($login, $password);
}
?>

<H2> Main page</H2>
<!---------------------------------------------------------------------->
<hr>
<P> Register new user</P>
<form method="post" action="index.php">
    <table>
        <tr>
            <td>login</td>
            <td>
                <label for="name"></label>
                <input required type="text" name="login" id="login" size="40"/>
            </td>
        </tr>
        <tr>
            <td>email</td>
            <td>
                <label for="name"></label>
                <input required type="text" name="email" id="email" size="40"/>
            </td>
        </tr>
        <tr>
            <td>password</td>
            <td>
                <label for="name"></label>
                <input required type="text" name="password" id="password" size="40"/>
            </td>
        </tr>
        <tr>
            <td>repeat password</td>
            <td>
                <label for="name"></label>
                <input required type="text" name="password2" id="password2" size="40"/>
            </td>

        </tr>
        <tr>
            <td>Auth</td>
            <td>
                <label for="name"></label>
                <input required type="radio" name="auth" id="auth" value="0" size="40"/>1 etapowa
                <input required type="radio" name="auth" id="auth" value="1" size="40"/>2 etapowa
            </td>
        </tr>
    </table>
    <input type="submit" id= "submit" value="Create account" name="add_user">
</form>
<!---------------------------------------------------------------------->
<hr>
<P> Log in</P>
<form method="post" action="index.php">
    <table>
        <tr>
            <td>login</td>
            <td>
                <label for="name"></label>
                <input required type="text" name="login" id="login" size="40"/>
            </td>
        </tr>
        <tr>
            <td>password</td>
            <td>
                <label for="name"></label>
                <input required type="password" name="password" 
                       id="password" size="40"/>
            </td>
        </tr>
    </table>
    <input type="submit" id= "submit" value="Log in" name="log_user_in">
</form>
<form method="post" action="index.php">
    <table>
        <tr>
            <td>login</td>
            <td>
                <label for="name"></label>
                <input required type="text" name="login" id="login" size="40"/>
            </td>
        </tr>
        <tr>
            <td>password</td>
            <td>
                <label for="name"></label>
                <input required type="password" name="password" 
                       id="password" size="40" />
            </td>
        </tr>
    </table>
    <input type="submit" id= "submit" value="Change Password" name="change_password">
</form>
<hr>
<form method="post" action="index.php">
    <input type="submit" id= "submit" value="logout" name="logout">
</form>
<!-- </body>-->

<!--</html>-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>

        </title>
    </head>
    <body>
        <?php
        include_once "classes/Page.php";
        Page::display_header("Index");
        ?>
        <H2></H2>
        <?php
        Page::display_navigation();
        ?>
    </body>
</html>

</body>
</html>