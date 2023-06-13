<?php

include_once "classes/Filter.php";

class Db {

    private $db; //Database variable
    private $messages_array = array(); //result
    private $id_array = array();
    private $dbname = "news";
    private $password = "";
    private $host = "localhost";
    private $user = "root";

    public function __construct() {
        try {
            $this->db = new PDO("mysql:host=$this->host;dbname=$this->dbname;charset=utf8", $this->user, $this->password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $statement = $this->db->prepare('SELECT * from message');
            $statement->execute();
            foreach ($statement as $row):
                if (!$row['deleted']) {
                    $this->messages_array[] = $row['message'];
                    $this->id_array[] = $row['id'];
                }
                //echo($row['message']."<br>");
            endforeach;
        } catch (PDOException $e) {
            echo $e->getMessage() . "<br>";
            die();
        }
    }
    
    
    public function isUserMessage($messageId, $userId) {
    $statement = $this->db->prepare("SELECT COUNT(*) FROM message WHERE id = :messageId AND id_user = :userId");
    $statement->bindValue(':messageId', $messageId, PDO::PARAM_INT);
    $statement->bindValue(':userId', $userId, PDO::PARAM_INT);
    $statement->execute();
    
    $count = $statement->fetchColumn();
    
    return $count > 0;
}


    

    function __destruct() {
        $this->db = null;
    }

    public function get_messages_arr() {
        return $this->messages_array;
    }

    public function get_id_arr() {
        return $this->id_array;
    }
    
    public function addMessage($name, $type, $content, $userId) {
        $filtr = new Filter();
        $hasAddMessagePrivilege = false;
        $privileges = $_SESSION['privileges'] ?? [];

        if (is_array($privileges)) {
            foreach ($privileges as $privilegeArray) {
                if (isset($privilegeArray['name']) && ($privilegeArray['name'] === "add_message" || $privilegeArray['name'] === "manage_messages")) {
                    $hasAddMessagePrivilege = true;
                    break;
                }
            }
        }
        if($hasAddMessagePrivilege){
        if ($filtr->filter_type($type)) {
            $statement = $this->db->prepare("INSERT INTO message (name,type,message,deleted,id_user) 
VALUES (:name,:type,:content,0,:userId)");
            $statement->bindValue(':content', $content, PDO::PARAM_STR);
            $statement->bindValue(':type', $type, PDO::PARAM_STR);
            $statement->bindValue(':name', $name, PDO::PARAM_STR);
            $statement->bindValue(':userId', $userId, PDO::PARAM_STR);
            echo "Dodawanie powiodlo się";
            echo "<BR\>";
            return $statement->execute();
        } else {
            echo "Blad Wartosci w add,DB<br>";
        }
        }else{
            echo 'YOU HAVE NO PRIVILEGE TO ADD MESSAGE <BR/>';
            return false;
        }
    }
    
    public function deleteMessage($id) {
        $filtr = new Filter();
        $hasDeleteMessagePrivilege = false;
        $privileges = $_SESSION['privileges'] ?? [];

        if (is_array($privileges)) {
            foreach ($privileges as $privilegeArray) {
                if (isset($privilegeArray['name']) && ($privilegeArray['name'] === "delete_message" || $privilegeArray['name'] === "manage_messages")) {
                    $hasDeleteMessagePrivilege = true;
                    break;
                }
            }
        }
        if ($hasDeleteMessagePrivilege) {
            if ($filtr->filter_int($id)) {
                $statement = $this->db->prepare("DELETE FROM message WHERE id = :id");
                $statement->bindValue(':id', $id, PDO::PARAM_INT);
                echo "<BR>Usunięcie powiodło się";
                echo "<BR\>";
                return $statement->execute();
            } else {
                echo "<BR>Błąd Wartości w deleteMessage, DB<br>";
            }
        } else {
            echo 'YOU HAVE NO PRIVILEGE TO DELETE MESSAGE <BR/>';
            return false;
        }
    }
    public function isMessageCreator($id, $userId) {
    $isMessageCreator = false;
    $statement = $this->db->prepare("SELECT COUNT(*) FROM message WHERE id = :id AND id_user = :userId");
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->bindValue(':userId', $userId, PDO::PARAM_INT);
    $statement->execute();

    $rowCount = $statement->fetchColumn();
    $isMessageCreator = ($rowCount > 0);

    return $isMessageCreator;
}



    public function editMessage($id, $content, $userId) {
    $privileges = $_SESSION['privileges'] ?? [];

    $hasEditMessagePrivilege = false;
    $isMessageCreator = false;

    if (is_array($privileges)) {
        foreach ($privileges as $privilegeArray) {
            if (isset($privilegeArray['name']) && ($privilegeArray['name'] === "edit_message" || $privilegeArray['name'] === "manage_messages")) {
                $hasEditMessagePrivilege = true;
                break;
            }
        }
    }

    $statement = $this->db->prepare("SELECT COUNT(*) FROM message WHERE id = :id AND id_user = :userId");
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->bindValue(':userId', $userId, PDO::PARAM_INT);
    $statement->execute();
    
    $isMessageCreator = ($statement->fetchColumn() > 0);

    if ($hasEditMessagePrivilege || $isMessageCreator) {
        $filtr = new Filter();
        if ($filtr->filter_int($id)) {
            $statement = $this->db->prepare('UPDATE message SET message=:content WHERE id=:id');
            $statement->bindValue(':content', $content, PDO::PARAM_STR);
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            echo "<BR>Edycja powiodła się";
            echo "<BR\>";
            return $statement->execute();
        } else {
            echo "<BR>Błąd Wartości w edit, DB<br>";
        }
    } else {
        echo 'YOU HAVE NO PRIVILEGE TO EDIT MESSAGE <BR/>';
        return false;
    }
}


    /*
      public function getMessage($message_id) {
      foreach ($this->messages_array as $message):
      if ($message->id == $message_id)
      return $message->message;
      endforeach;
      }

     */

    //put your code here
}

?>