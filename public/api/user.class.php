<?php 

// Icluimos el archivo de constantes
require_once(__DIR__."/../config/constants.php");

class User {
    public $result;    
    private $db; 

    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * 
     */
    public function login($user,$pass) {
        $this->result = [];

        $query = "SELECT id_user, _uuid FROM user WHERE name_user=:user AND password=MD5(:pass) AND _visible=1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user", $user);
        $stmt->bindParam(":pass", $pass);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = USER_FOUND_MSG;
                $this->result["data"] = $stmt->fetch();
            } else {
                $this->result["code"] = QUERY_SIN_DATOS; 
                $this->result["msg"] = USER_NO_FOUND_MSG;
            }
        } else {
            $this->result["code"] = QUERY_NO_EJECUTADA; 
            $this->result["msg"] = QUERY_NO_EJECUTADA;
        }

        return $this->result;
    }

    public function signup($data) {
        $this->result = [];

        // Si las contraseñas no coinciden, no hacemos nada
        if ($data["pass"] == $data["pass2"]) {
            $query = "INSERT INTO user(name_user, password, signupDate, _uuid) VALUES (:user, MD5(:pass), CURDATE(), :uuid)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user", $data["user"]);
            $stmt->bindParam(":pass", $data["pass"]);
            $stmt->bindParam(":uuid", uniqid("hl-",true));
            if ($stmt->execute()) {
                $this->result["code"] = QUERY_OK;
                $this->result["msg"] = QUERY_OK_MSG;
            } else {
                $this->result["code"] = QUERY_NO_EJECUTADA;
                $this->result["msg"] = QUERY_NO_EJECUTADA_MSG;
            }   
        } else {
            $this->result["code"] = QUERY_NO_EJECUTADA;
            $this->result["msg"] = PWD_NO_EQUAL_MSG;
        }

        return $this->result;
    }

    public function getUser($id) {
        $this->result = [];

        $query = "SELECT name_user, mail, signupDate FROM library.user WHERE id_user=:id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":id", $id);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_OK_MSG;
                $this->result["data"] = $stmt->fetch();
            } else {
                $this->result["code"] = QUERY_SIN_DATOS; 
                $this->result["msg"] = QUERY_SIN_DATOS_MSG;
            }
        } else {
            $this->result["code"] = QUERY_NO_EJECUTADA; 
            $this->result["msg"] = QUERY_NO_EJECUTADA;
        }

        return $this->result;
    }

    public function updateUser($userID,$dataArray,$pwd) {
        $this->result = [];
        $query = "UPDATE library.user 
                    SET name_user=:name_user, mail=:mail, password=MD5(:password)
                    WHERE id_user=:userID";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":userID", $userID);
        $stmt->bindValue(":name_user", $dataArray["name_user"]);
        $stmt->bindValue(":mail", $dataArray["mail"] ?? null);
        $stmt->bindValue(":password", $pwd);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_UPDATE_USER_MSG;
            } else {
                $this->result["code"] = QUERY_SIN_DATOS; 
                $this->result["msg"] = QUERY_NO_UPDATE_MSG;
            }
        } else {
            $this->result["code"] = QUERY_NO_EJECUTADA; 
            $this->result["msg"] = QUERY_NO_EJECUTADA;
        }

        return $this->result;
    }
}

