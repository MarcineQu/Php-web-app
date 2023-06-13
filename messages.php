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
//session
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
//session
// adding new message
if (isset($_POST['add_message'])) {
    if (!$filtr->filter_post($_POST['add_message'])) {
        $name = $filtr->filter_string($_POST['name']);
        $content = $filtr->filter_string($_POST['content']);
        $type = $_POST['type'];
        $login = $_SESSION['login'];
        $userId = $Pdo->getUserId($login);
        if ($filtr->filter_type($type)) {
            if (!$db->addMessage($name, $type, $content, $userId)) {
                echo "<BR>Adding new message failed";
            }
        } else {
            echo "<BR>Blad wartosci";
        }
    } else {
        echo "<BR>Błąd requesta z form add";
    }
    $db = new Db();
}
if (isset($_POST['edit_message'])) {
    if (!$filtr->filter_post($_POST['edit_message'])) {
        $id = $_POST['id'];
        $login = $_SESSION['login'];
        $userId = $Pdo->getUserId($login);
        $content = $filtr->filter_string($_POST['content']);
        if ($filtr->filter_int($id)) {
            if (!$db->editMessage($id, $content, $userId)) {
                echo "<BR>Editing message failed";
            }
        } else {
            echo "<BR>Błąd przekazywania danych";
        }
    } else {
        echo "<BR>Błąd requesta z form edit";
    }
    $db = new Db();
}
if (isset($_POST['delete_message'])) {
    if (!$filtr->filter_post($_POST['delete_message'])) {
        $id = $_POST['id'];
        if ($filtr->filter_int($id)) {
            if (!$db->deleteMessage($id)) {
                echo "<BR>Deleting message failed";
            }
        } else {
            echo "<BR>Błąd przekazywania danych";
        }
    } else {
        echo "<BR>Błąd requesta z form delete";
    }
    $db = new Db();
}

$privileges = $_SESSION['privileges'] ?? [];
$hasDisplayPrivatePrivilege = false;

if (is_array($privileges)) {
    foreach ($privileges as $privilegeArray) {
        if (isset($privilegeArray['name']) && ($privilegeArray['name'] === "display_private" || $privilegeArray['name'] === "manage_messages")) {
            $hasDisplayPrivatePrivilege = true;
            break;
        }
    }
}
if ($hasDisplayPrivatePrivilege) {
    ?>
    <!--------------------------------------------------------------------->
    <hr>
    <P> Messages</P>
    <ol>
        <?php
        $messages_arr = $db->get_messages_arr();
        $id_arr = $db->get_id_arr();
        if (count($id_arr) == count($messages_arr)) {
            $i = 0;
            $n = count($id_arr);
            while ($i < $n) {
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

                if ($hasEditMessagePrivilege) {
                    echo " <a href='messages_edit.php?id=" . $id_arr[$i] . "'><input type='submit' value='Edit message' name='edit_message'></a>";
                }

                // Check for "delete message" privilege
                $hasDeleteMessagePrivilege = false;

                if (is_array($privileges)) {
                    foreach ($privileges as $privilegeArray) {
                        if (isset($privilegeArray['name']) && ($privilegeArray['name'] === "delete_message" || $privilegeArray['name'] === "manage_messages")) {
                            $hasDeleteMessagePrivilege = true;
                            break;
                        }
                    }
                }

                if ($hasDeleteMessagePrivilege) {
                    echo " <form method='post' action='messages.php'><input type='hidden' name='id' value='" . $id_arr[$i] . "'><input type='submit' value='Delete message' name='delete_message'></form>";
                }

                echo "</li>";
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
}else {
     echo "<BR>You do not have the privilege to display a messages.";
            echo "<hr>";
            echo "<form method='post' action='index.php'>";
            echo "<input type='submit' id='submit' value='logout' name='logout'>";
            echo "</form>";
            echo "<P>Navigation</P>";
            Page::display_navigation();
}

