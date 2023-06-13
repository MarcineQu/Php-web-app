<?php
include_once "classes/Page.php";
include_once "classes/Db.php";
include_once "login.php";
Page::display_header("Add message");

if (isset($_SESSION['last_time_active']) && $_SESSION['last_time_active'] > time()) {
    if (isset($_SESSION['login']) && !empty($_SESSION['login'])) {
        $_SESSION['last_time_active'] = time() + 5 * 60;
        echo "Zalogowano jako: " . $_SESSION['login'];

        // Check for "add message" privilege
        $privileges = $_SESSION['privileges'] ?? [];
        $hasAddMessagePrivilege = false;

        if (is_array($privileges)) {
            foreach ($privileges as $privilegeArray) {
                if (isset($privilegeArray['name']) && ($privilegeArray['name'] === "add_message" || $privilegeArray['name'] === "manage_messages")) {
                    $hasAddMessagePrivilege = true;
                    break;
                }
            }
        }
        if ($hasAddMessagePrivilege) {
            // User has the privilege to add a message
            ?><hr>
            <P> Add message</P>
            <form method="post" action="messages.php">
                <table>
                    <tr>
                        <td>Name</td>
                        <td>
                            <label for="name"></label>
                            <input required type="text" name="name" id="name" size="56"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Type</td>
                        <td>
                            <label for="type"></label>
                            <select name="type" id="type">
                                <option value="public">Public</option>
                                <option value="private">Private</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Message content</td>
                        <td>
                            <label for="content"></label>
                            <textarea required type="text" name="content" id="content" rows="10" cols="40"></textarea>
                        </td>
                    </tr>
                </table>
                <input type="submit" id= "submit" value="Add message" name="add_message">
            </form>
            <hr>
            <form method="post" action="index.php">
                <input type="submit" id= "submit" value="logout" name="logout">
            </form>
            <!--------------------------------------------------------------------->
            <hr>
            <P>Navigation</P>
            <?php
            Page::display_navigation();
        } else {
            // User doesn't have the privilege to add a message
            echo "<BR>You do not have the privilege to add a message.";
            echo "<hr>";
            echo "<form method='post' action='index.php'>";
            echo "<input type='submit' id='submit' value='logout' name='logout'>";
            echo "</form>";
            echo "<P>Navigation</P>";
            Page::display_navigation();
        }
    }
} else {
    session_unset();
    session_destroy();
    header("Location: index.php");
    echo "NIEZALOGOWANY";
}
//session
?>



