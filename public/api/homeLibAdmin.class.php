<?php 

namespace homeLibAdmin;

// Icluimos el archivo de constantes
require_once(__DIR__."/../config/constants.php");

class Library {

    /** 
     *  Cuando $result se va use para el return de una función, va a tener 2/3 parámetros:
     *  ["code"] - Código de error
     *  ["msg"]  - Mensaje relacionado con el código de error
     *  ["data"] - Datos devueltos de la query (opcional)
     */
    public $result;    
    private $db; 

    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }

    // ----------------------
    // --- ENSEÑAR LIBROS ---
    // ----------------------

    public function getPersonalLibrary($id, $filters=[]) {
        $this->result = [];

        $query = "SELECT b.id_book, b.isbn, b.title, b.subtitle, b.author, b.coauthor, b.editor, b.edition, b.year, b.pages, b.id_format, b.id_genre, b.pic, 
                            bp.dateBought, bp.price, bp.id_storage, bp.shelf, bp.lent, bp.lent_who, bp.lent_when,
                            f.name_format, g.name_genre, s.name_storage 
                    FROM library.book as b, library.book_personal as bp, library.format as f, library.genre as g, library.storage as s  
                    WHERE bp.id_user = :userID AND b.id_book = bp.id_book
                        AND bp.id_storage = s.id_storage
                        AND b.id_format = f.id_format
                        AND b.id_genre = g.id_genre";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":userID", $id);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_OK_MSG;
                $this->result["data"] = $stmt->fetchAll();
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

    // QUIZÁS PARA LIBERAR ALGO DE ESPACIO DE LA PRINCIPAL?
    public function getBookData() {

    }
    
    public function updateBook($userID,$dataArray) {
        $this->result = [];
        $query = "UPDATE library.book_personal 
                    SET dateBought=:dateBought, price=:price, id_storage=:id_storage, shelf=:shelf, 
                        lent=:lent, lent_who=:lent_who, lent_when=:lent_when
                    WHERE id_user=:userID AND id_book=:bookID";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":userID", $userID);
        $stmt->bindValue(":bookID", $dataArray["id_book"]);
        $stmt->bindValue(":dateBought", $dataArray["dateBought"]);
        $stmt->bindValue(":price", $dataArray["price"]);
        $stmt->bindValue(":id_storage", $dataArray["id_storage"]);
        $stmt->bindValue(":shelf", $dataArray["shelf"]);
        $stmt->bindValue(":lent", $dataArray["lent"]);
        $stmt->bindValue(":lent_who", $dataArray["lent_who"]);
        $stmt->bindValue(":lent_when", $dataArray["lent_when"]);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_UPDATE_MSG;
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

    public function getStorage() {
        $this->result = [];

        $query = "SELECT id_storage, name_storage FROM library.storage";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_OK_MSG;
                $this->result["data"] = $stmt->fetchAll();
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

    public function getGenre() {
        $this->result = [];

        $query = "SELECT id_genre, name_genre FROM library.genre";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_OK_MSG;
                $this->result["data"] = $stmt->fetchAll();
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

    public function getFormat() {
        $this->result = [];

        $query = "SELECT id_format, name_format FROM library.format";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_OK_MSG;
                $this->result["data"] = $stmt->fetchAll();
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
}