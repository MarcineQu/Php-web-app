<?php
include_once "classes/Page.php";
include_once "classes/Db.php";
include_once "classes/Filter.php";
include_once "login.php";
include_once "classes/Pdo_.php";

Page::display_header("Messages");
$db = new Db();
$filtr = new Filter;
$Pdo = new Pdo_();
if (isset($_SESSION['last_time_active'])) {
    if ($_SESSION['last_time_active'] > time()) {
        if (isset($_SESSION['login']) && !empty($_SESSION['login'])) {
            $_SESSION['last_time_active'] = time() + 5 * 60;
            echo "Zalogowano jako: " . $_SESSION['login'];
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
?>
<!--------------------------------------------------------------------->
    <hr>
    <P> Messages</P>
    <ol>
        <?php
        $login = $_SESSION['login'];
        $userId = $Pdo->getUserId($login);
        $messages_arr = $db->get_messages_arr();
        $id_arr = $db->get_id_arr();
       if (count($id_arr) == count($messages_arr)) {
    $i = 0;
    $n = count($id_arr);
    while ($i < $n) {
        // Sprawdź, czy wiadomość należy do zalogowanego użytkownika
        if ($db->isUserMessage($id_arr[$i], $userId)) {
            echo "<li>";
            echo $messages_arr[$i];

            // Check for "edit message" privilege
            $privileges = $_SESSION['privileges'] ?? [];

            $hasEditMessagePrivilege = false;

            if (is_array($privileges)) {
                foreach ($privileges as $privilegeArray) {
                    if (isset($privilegeArray['name']) && ($privilegeArray['name'] === "edit_message" || $privilegeArray['name'] === "manage_messages")) {
                        $hasEditMessagePrivilege = true;
                        break;
                    }
                }
            }
            $isMessageCreator = $db->isMessageCreator($id_arr[$i], $userId);

            if ($hasEditMessagePrivilege || $isMessageCreator) {
                echo " <a href='messages_edit.php?id=" . $id_arr[$i] . "'><input type='submit' value='Edit message' name='edit_message'></a>";
            }
            
            echo "</li>";
        }

        $i++;
    }
}
 ?>
    </ol>
    <hr>
    <form method="post" action="index.php">
        <input type="submit" id="submit" value="logout" name="logout">
    </form>
    <!--------------------------------------------------------------------->
    <hr>
    <P>Navigation</P>
    <?php
    Page::display_navigation();       