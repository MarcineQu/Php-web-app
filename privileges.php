<?php
include_once "classes/Page.php";
include_once "classes/Pdo_.php";
include_once "login.php";
Page::display_header("Privileges");
$Pdo = new Pdo_(array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

if (isset($_SESSION['last_time_active'])) {
    if ($_SESSION['last_time_active'] > time()) {
        if (isset($_SESSION['login']) && !empty($_SESSION['login'])) {
            $_SESSION['last_time_active'] = time() + 5 * 60;
            echo "Zalogowano jako: " . $_SESSION['login'];
            $login = $_SESSION['login'];
        }
    } else {
        session_unset();
        session_destroy();
        header("Location: index.php");
        echo "NIEZALOGOWANY";
    }
} else {
    header("Location: index.php");
    echo "NIEZALOGOWANY";
}

if (isset($_REQUEST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
}

// Sprawdzenie, czy formularz dodawania/usuwanie uprawnień został wysłany
if (isset($_POST['submit_add_permission'])) {
    $userId = $_POST['user_id']; // Pobierz identyfikator użytkownika z formularza
    $permissionId = $_POST['permission_id'];
    $Pdo->addUserPermission($userId, $permissionId);
}

if (isset($_POST['submit_remove_permission'])) {
    $userId = $_POST['user_id']; // Pobierz identyfikator użytkownika z formularza
    $permissionId = $_POST['permission_id'];
    $Pdo->removeUserPermission($userId, $permissionId);
}

if (isset($_POST['submit_add_permission_role'])) {
    $roleId = $_POST['role_id']; // Pobierz identyfikator roli z formularza
    $permissionId = $_POST['permission_id'];
    $Pdo->addRolePermission($roleId, $permissionId);
}

if (isset($_POST['submit_remove_permission_role'])) {
    $roleId = $_POST['role_id']; // Pobierz identyfikator roli z formularza
    $permissionId = $_POST['permission_id'];
    $Pdo->removeRolePermission($roleId, $permissionId);
}

if (isset($_POST['submit_add_role'])) {
    $roleName = $_POST['role_name']; // Pobierz nazwę roli z formularza
    $description = $_POST['description']; // Pobierz opis roli z formularza
    $Pdo->addRole($roleName, $description);
}
if (isset($_POST['submit_remove_role'])) {
    $roleId = $_POST['role_id']; // Pobierz identyfikator roli z formularza
    $Pdo->removeRole($roleId);
}
if (isset($_POST['submit_add_role_user'])) {
    $userId = $_POST['user_id'];
    $roleId = $_POST['role_id']; // Pobierz identyfikator roli z formularza
    $Pdo->addUserRole($userId, $roleId);
}
if (isset($_POST['submit_remove_role_user'])) {
    $userId = $_POST['user_id'];
    $roleId = $_POST['role_id']; // Pobierz identyfikator roli z formularza
    $Pdo->removeUserRole($userId, $roleId);
}
?>

<h2>Privileges</h2>
<hr>
<h3>Lista uprawnień w systemie</h3>
<ol>
    <?php
    $Pdo->displayPermissionList();
    ?>
</ol>
<hr>
<!-- Wyświetlenie uprawnień wszystkich użytkowników -->
<h3>Uprawnienia użytkowników</h3>

<?php
$users = $Pdo->getAllUsers(); // Pobranie wszystkich użytkowników z bazy danych
foreach ($users as $user) {
    echo "<h4>Uprawnienia użytkownika: " . $user['login'] . "</h4>";
    $privileges = $Pdo->get_privileges($user['id']);
    if ($privileges !== null && is_array($privileges) && $privileges['status'] === 'success') {
        echo "<ol>";
        foreach ($privileges['data'] as $row) {
            echo "<li>Permission ID: " . $row['id'] . "</li>";
            echo "<li>Permission Name: " . $row['name'] . "</li><br>";
        }
        echo "</ol>";
    } else {
        echo "Brak uprawnień.<BR>";
    }
    $roles = $Pdo->getUserRoles($user['id']);

    if (!empty($roles)) {
        echo "<ol>";
        foreach ($roles as $role) {
            echo "<li>Role: " . $role . "</li>";
        }
        echo "</ol>";
    } else {
        echo "Brak ról dla użytkownika.<BR>";
    }

    // Formularz dodawania roli dla użytkownika
    echo "<form action='privileges.php' method='POST'>";
    echo "<input type='hidden' name='user_id' value='" . $user['id'] . "'>";
    echo "<label for='role_id'>Wybierz rolę:</label>";
    echo "<select name='role_id' id='role_id' required>"; // Dodane pole typu select
    $roles = $Pdo->getRoles(); // Pobranie wszystkich ról z bazy danych
    foreach ($roles as $role) {
        echo "<option value='" . $role['id'] . "'>" . $role['role_name'] . "</option>";
    }
    echo "</select>";
    echo "<input type='submit' name='submit_add_role_user' value='Dodaj rolę'>";
    echo "<input type='submit' name='submit_remove_role_user' value='Usuń rolę'>";
    echo "</form>";
    echo "<hr>";
}
?>

<h3>Dodaj uprawnienie</h3>
<form action="privileges.php" method="POST">
    <label for="user_id">Wybierz użytkownika:</label>
    <select name="user_id" id="user_id" required>
        <?php
        $users = $Pdo->getAllUsers(); // Pobranie wszystkich użytkowników z bazy danych
        foreach ($users as $user) {
            echo "<option value='" . $user['id'] . "'>" . $user['login'] . "</option>";
        }
        ?>
    </select>
    <br>
    <label for="permission_id">Uprawnienie:</label>
    <select name="permission_id" id="permission_id" required>
        <?php
        $privileges = $Pdo->getAllPrivileges(); // Pobranie wszystkich użytkowników z bazy danych
        foreach ($privileges as $privilege) {
            echo "<option value='" . $privilege['id'] . "'>" . $privilege['name'] . "</option>";
        }
        ?>
    </select>
    <input type="submit" name="submit_add_permission" value="Dodaj">
</form>
<hr>
<!-- Formularz usuwania uprawnień -->
<form action="privileges.php" method="POST">
    <h3>Usuń uprawnienie</h3>
    <label for="user_id">Wybierz użytkownika:</label>
    <select name="user_id" id="user_id" required>
        <?php
        $users = $Pdo->getAllUsers(); // Pobranie wszystkich użytkowników z bazy danych
        foreach ($users as $user) {
            echo "<option value='" . $user['id'] . "'>" . $user['login'] . "</option>";
        }
        ?>
    </select>
    <br>
    <label for="permission_id">Uprawnienie:</label>
    <select name="permission_id" id="permission_id" required>
        <?php
        $privileges = $Pdo->getAllPrivileges(); // Pobranie wszystkich użytkowników z bazy danych
        foreach ($privileges as $privilege) {
            echo "<option value='" . $privilege['id'] . "'>" . $privilege['name'] . "</option>";
        }
        ?>
    </select>
    <input type="submit" name="submit_remove_permission" value="Usuń">
</form>

<!-- Wyświetlenie ról w systemie -->
<!-- Wyświetlenie ról w systemie -->
<h2>List of roles</h2>
<ol>
    <?php
    $roles = $Pdo->getRoles(); // Pobranie wszystkich ról z bazy danych
    foreach ($roles as $role) {
        echo "<li>Role ID: " . $role['id'] . "</li>";
        echo "<li>Role Name: " . $role['role_name'] . "</li>";
        echo "<li>Description: " . $role['description'] . "</li>";

        // Pobranie przywilejów dla danej roli
        $privileges = $Pdo->getRolePrivileges($role['id']);
        if ($privileges !== null && is_array($privileges) && $privileges['status'] === 'success') {
            echo "<ul>";
            foreach ($privileges['data'] as $row) {
                echo "<li>Permission ID: " . $row['id'] . "</li>";
                echo "<li>Permission Name: " . $row['name'] . "</li>";
            }
            echo "</ul>";
        } else {
            echo "Brak przywilejów.";
        }

        echo "<form action='privileges.php' method='POST'>";
        echo "<input type='hidden' name='role_id' value='" . $role['id'] . "'>";
        echo "<label for='permission_id'>ID uprawnienia:</label>";
        echo "<select name='permission_id' id='permission_id' required>"; // Zmiana na pole typu select
        $permissions = $Pdo->getAllPrivileges(); // Pobranie wszystkich uprawnień z bazy danych
        foreach ($permissions as $permission) {
            echo "<option value='" . $permission['id'] . "'>" . $permission['name'] . "</option>";
        }
        echo "</select>";
        echo "<input type='submit' name='submit_add_permission_role' value='Dodaj przywilej'>";
        echo "<input type='submit' name='submit_remove_permission_role' value='Usuń przywilej'>";
        echo "</form><br>";
    }
    ?>
</ol>

<h3>Dodaj rolę</h3>
<form action="privileges.php" method="POST">
    <label for="role_name">Nazwa roli:</label>
    <input type="text" name="role_name" id="role_name" required>
    <br>
    <label for="description">Opis:</label>
    <input type="text" name="description" id="description" required>
    <input type="submit" name="submit_add_role" value="Dodaj">
</form>
<form action="privileges.php" method="POST">
    <h3>Usuń rolę</h3>
    <label for="role_id">Wybierz rolę:</label>
    <select name="role_id" id="role_id" required>
        <?php
        $roles = $Pdo->getRoles(); // Pobranie wszystkich ról z bazy danych
        foreach ($roles as $role) {
            echo "<option value='" . $role['id'] . "'>" . $role['role_name'] . "</option>";
        }
        ?>
    </select>
    <input type="submit" name="submit_remove_role" value="Usuń">
</form>
<hr>
<form method="post" action="index.php">
    <input type="submit" id="submit" value="logout" name="logout">
</form>
<!--------------------------------------------------------------------->
<hr>
<p>Nawigacja</p>
<?php
Page::display_navigation();
?>
