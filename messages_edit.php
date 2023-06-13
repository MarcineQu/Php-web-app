<?php
include_once "classes/Page.php";
include_once "classes/Db.php";
include_once "login.php";
Page::display_header("Edit message");

//session
if(isset($_SESSION['last_time_active'])){
if($_SESSION['last_time_active'] > time() ){
if(isset($_SESSION['login']) && !empty($_SESSION['login'])){
    $_SESSION['last_time_active'] = time() + 5*60;
    echo "Zalogowano jako: " . $_SESSION['login'];
}}
else
{
    session_unset();
    session_destroy();
    header("Location: index.php");
    echo "NIEZALOGOWANY";
}
}else
{
    header("Location: index.php");
    echo "NIEZALOGOWANY";
}
//session
?>
<hr>
<P> Edit message</P>
<form method="post" action="messages.php">
    <input type="hidden" name="id" id="id" value=<?php echo htmlspecialchars($_GET["id"])?>>
    <table>
        <tr>
            <td>Message content</td>         
            <td>
                <label for="content"></label>
                <textarea required type="text" name="content" id="content" rows="10" cols="40"></textarea>   
            </td>
        </tr>
    </table>
    <input type="submit" id= "submit" value="Edit message" name="edit_message">
</form>
<hr>
<form method="post" action="index.php">
    <input type="submit" id= "submit" value="logout" name="logout">
</form>
<hr>

<P>Navigation</P>
<?php
Page::display_navigation();



