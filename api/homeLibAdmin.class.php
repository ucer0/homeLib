<?php 

namespace homeLibAdmin;
use Book;

// Icluimos el archivo de constantes
require_once(__DIR__."/../config/constants.php");
require_once("BookAPI.class.php");

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

    /**
     * Recoge todos los datos de todos los libros de la cuenta de un usuario
     * @param int $id
     * @param array $filters --> No implementado, para una posible busqueda avanzada por varios campos
     * @return array $result --> Devuelve datos de la query (si los hubiese) y código de error
     */
    public function getPersonalLibrary($id, $filters=[]) {
        $this->result = [];

        $query = "SELECT b.id_book, b.isbn, b.title, b.subtitle, b.author, b.coauthor, b.editor, b.edition, b.year, b.pages, b.id_format, b.id_genre, b.pic, 
                            bp.dateBought, bp.price, bp.id_storage, bp.shelf, bp.lent, bp.lent_who, bp.lent_when,
                            f.name_format, g.name_genre, s.name_storage 
                    FROM book as b, book_personal as bp, format as f, genre as g, storage as s  
                    WHERE bp.id_user = :userID AND b.id_book = bp.id_book
                        AND bp.id_storage = s.id_storage
                        AND b.id_format = f.id_format
                        AND b.id_genre = g.id_genre
                    ORDER BY b.id_book DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":userID", $id);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_OK_MSG;
                $this->result["data"] = $this->cleanResult($stmt->fetchAll());
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
    
    /**
     * Recoge todos los datos de un libro específico
     * @param int $isbn
     * @return array $result --> Devuelve datos de la query (si los hubiese) y código de error
     */
    public function getBook($isbn) {
        $this->result = [];

        // Primero buscamos si existe en la base de datos
        $query = "SELECT id_book, isbn, title, subtitle, author, coauthor, editor, edition, year, pages, id_format, id_genre, pic
                    FROM book WHERE isbn=:isbn";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":isbn", $isbn);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                // Si existe, mandamos los datos directamente
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_OK_MSG;
                $this->result["data"] = $stmt->fetch();
            } else {
                // Si no existe, lo buscamos por la API
                $bookAPI = new Book();
                $book = $bookAPI->getBookByISBN($isbn);

                if (!empty($book)) {

                    if (empty($book["pic"])) {
                        $book["pic"] = $this->getPicFromISBN($isbn);
                    }

                    $this->result["code"] = QUERY_OK; 
                    $this->result["msg"] = API_FOUND_MSG;
                    $this->result["data"] = $book;
                } else {
                    $this->result["code"] = QUERY_SIN_DATOS; 
                    $this->result["msg"] = API_NOT_FOUND_MSG;
                }
            }
        } else {
            $this->result["code"] = QUERY_NO_EJECUTADA; 
            $this->result["msg"] = QUERY_NO_EJECUTADA;
        }

        return $this->result;
    }

    /**
     * Guarda un libro para la colección personal de un usuario, si este no está aún en la base de datos general se añade también
     * @param int $userID
     * @param array $dataArray
     * @param bool $isNew
     * @return array $result --> Devuelve datos de la query (si los hubiese) y código de error
     */
    public function saveBook($userID,$dataArray,$isNew) {
        $this->result = [];

        // Futura actualización: Quitar lo de isNew y hacer checkeo siempre de si existe al guardar el libro, no antes
        if ($isNew) {
            $query = "INSERT INTO book (isbn,title,subtitle,author,coauthor,editor,edition,year,pages,id_format,id_genre,pic) 
                        VALUES (:isbn,:title,:subtitle,:author,:coauthor,:editor,:edition,:year,:pages,:id_format,:id_genre,:pic)";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":isbn", $dataArray["isbn"]);
            $stmt->bindValue(":title", $dataArray["title"]);
            $stmt->bindValue(":subtitle", $dataArray["subtitle"] ?? null);
            $stmt->bindValue(":author", $dataArray["author"]);
            $stmt->bindValue(":coauthor", $dataArray["coauthor"] ?? null);
            $stmt->bindValue(":editor", $dataArray["editor"]);
            $stmt->bindValue(":edition", $dataArray["edition"]);
            $stmt->bindValue(":year", $dataArray["year"]);
            $stmt->bindValue(":pages", $dataArray["pages"]);
            $stmt->bindValue(":id_format", $dataArray["id_format"]);
            $stmt->bindValue(":id_genre", $dataArray["id_genre"]);
            $stmt->bindValue(":pic", $dataArray["pic"] ?? $this->getPicFromISBN($dataArray["isbn"]));

            $stmt->execute();
        }

        // Buscamos el ID del libro a añadir para la colección personal
        $query = "SELECT id_book FROM book WHERE isbn=:isbn";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":isbn", $dataArray["isbn"]);
        $stmt->execute();
        $bookID = $stmt->fetch()["id_book"];

        // Añadimos el libro y los datos personales a la cuenta
        $query = "INSERT INTO book_personal (id_user,id_book,dateBought,price,id_storage,shelf,lent,lent_who,lent_when,_dateAdded)
                    VALUES (:userID,:bookID,:dateBought,:price,:id_storage,:shelf,:lent,:lent_who,:lent_when,CURDATE())";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":userID", $userID);
        $stmt->bindValue(":bookID", $bookID);
        $stmt->bindValue(":dateBought", $dataArray["dateBought"] ?? null);
        $stmt->bindValue(":price", $dataArray["price"] ?? null);
        $stmt->bindValue(":id_storage", $dataArray["id_storage"] ?? 1);
        $stmt->bindValue(":shelf", $dataArray["shelf"] ?? "");
        $stmt->bindValue(":lent", $dataArray["lent"] ?? 0);
        $stmt->bindValue(":lent_who", $dataArray["lent_who"] ?? null);
        $stmt->bindValue(":lent_when", $dataArray["lent_when"] ?? null);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_INSERT_MSG;
            } else {
                $this->result["code"] = QUERY_SIN_DATOS; 
                $this->result["msg"] = QUERY_NO_INSERT_MSG;
            }
        } else {
            $this->result["code"] = QUERY_NO_EJECUTADA; 
            $this->result["msg"] = QUERY_NO_EJECUTADA;
        }

        return $this->result;
    }

    /**
     * Actualiza los datos personales de un libro de la cuenta de un usuario
     * @param int $userID
     * @param array $dataArray
     * @return array $result --> Devuelve datos de la query (si los hubiese) y código de error
     */
    public function updateBook($userID,$dataArray) {
        $this->result = [];
        $query = "UPDATE book_personal 
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

    /**
     * Borra un libro de la cuenta de un usuario
     * @param int $userID
     * @param int $bookID
     * @return array $result --> Devuelve datos de la query (si los hubiese) y código de error
     */
    public function deleteBook($userID,$bookID) {
        $this->result = [];

        $query = "DELETE FROM book_personal 
                    WHERE id_user=:userID AND id_book=:bookID";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":userID", $userID);
        $stmt->bindValue(":bookID", $bookID);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_DELETE_MSG;
            } else {
                $this->result["code"] = QUERY_SIN_DATOS; 
                $this->result["msg"] = QUERY_NO_DELETE_MSG;
            }
        } else {
            $this->result["code"] = QUERY_NO_EJECUTADA; 
            $this->result["msg"] = QUERY_NO_EJECUTADA;
        }

        return $this->result;
    }

    // ---------------------------
    // --- FUNCIONES DE CUENTA ---
    // ---------------------------

    /**
     * Exporta una copia legible de la biblioteca completa de un usuario
     * @param int $is
     * @return array $result --> Devuelve datos de la query (si los hubiese) y código de error
     */
    public function getDownloadableLibrary($id) {
        $this->result = [];

        $query = "SELECT b.isbn AS 'ISBN', b.title AS 'Título', b.subtitle AS 'Subtítulo', b.author AS 'Autor', b.coauthor AS 'Coautor', b.editor AS 'Editorial', 
                    b.edition AS 'Edición', b.year AS 'Año', b.pages AS 'Páginas', f.name_format AS 'Formato', g.name_genre AS 'Género', bp.dateBought AS 'Fecha Compra', 
                    bp.price AS 'Precio', s.name_storage AS 'Localización', bp.shelf AS 'Estante', bp.lent AS 'Prestado', bp.lent_who AS 'Quién Prestado', 
                    bp.lent_when AS 'Cuándo Prestado',b.pic AS 'Portada'
                    FROM book as b, book_personal as bp, format as f, genre as g, storage as s  
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
                $this->result["data"] = $this->exportArrayToCSV($this->cleanResult($stmt->fetchAll()));
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

    /**
     * Exporta una copia de seguridad local de la base de datos personal de un usuario
     * @param int $id
     * @return array $result --> Devuelve datos de la query (si los hubiese) y código de error
     */
    public function exportBackup($id) {
        $this->result = [];

        $query = "SELECT id_book, dateBought, price, id_storage, shelf, lent, lent_who, lent_when, _dateAdded
                    FROM book_personal WHERE id_user=:userID";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":userID", $id);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_OK_MSG;
                $this->result["data"] = $this->exportArrayToCSV($this->cleanResult($stmt->fetchAll()));
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

    
    /**
     * Importa una copia de seguridad local de la base de datos personal de un usuario
     * @param int $userID
     * @param string $dataArray --> Realmente es array
     * @param string $pwd
     * @return array $result --> Devuelve datos de la query (si los hubiese) y código de error
     */
    function importBackup($id,$dataArray) {
        $this->result = [];
        $dataArray = explode("\n",$dataArray);
        foreach ($dataArray as $key => $row) {
            $dataArray[$key] = explode(";",$row);
        }

        if ($dataArray) {
            $fields = implode(",",$dataArray[0]);
            array_shift($dataArray);

            $query = "INSERT INTO book_personal (id_user,$fields)
                        VALUES (:userID,:bookID,:dateBought,:price,:id_storage,:shelf,:lent,:lent_who,:lent_when,:_dateAdded) 
                        ON DUPLICATE KEY UPDATE
                            id_user=:userID,id_book=:bookID,dateBought=:dateBought,price=:price,id_storage=:id_storage,
                            shelf=:shelf,lent=:lent,lent_who=:lent_who,lent_when=:lent_when,_dateAdded=:_dateAdded
                        "; //WHERE id_user=:userID
            $stmt = $this->db->prepare($query);
            foreach ($dataArray as $key => $value) {
                $stmt->bindValue(":userID", $id);
                $stmt->bindValue(":bookID", $value[0]);
                $stmt->bindValue(":dateBought", $value[1]??null);
                $stmt->bindValue(":price", $value[2]??null);
                $stmt->bindValue(":id_storage", $value[3]??1);
                $stmt->bindValue(":shelf", $value[4]?? "");
                $stmt->bindValue(":lent", $value[5]??0);
                $stmt->bindValue(":lent_who", $value[6]??null);
                $stmt->bindValue(":lent_when", $value[7]??null);
                $stmt->bindValue(":_dateAdded", $value[8]??null);
                $stmt->execute();
            }

            $this->result["code"] = QUERY_OK; 
            $this->result["msg"] = QUERY_IMPORT_MSG;
        } else {
            $this->result["code"] = QUERY_NO_EJECUTADA; 
            $this->result["msg"] = QUERY_NO_IMPORT_MSG;
        }

        return $this->result;
    }

    // -------------------------------
    // --- FUNCIONES PARA DROPDOWN ---
    // -------------------------------

    
    /**
     * Recoge todas las localizaciones
     * @return array $result --> Devuelve datos de la query (si los hubiese) y código de error
     */
    public function getStorage() {
        $this->result = [];

        $query = "SELECT id_storage, name_storage FROM storage";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_OK_MSG;
                $this->result["data"] = $this->cleanResult($stmt->fetchAll());
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

    /**
     * Recoge todos los géneros de libros
     * @return array $result --> Devuelve datos de la query (si los hubiese) y código de error
     */
    public function getGenre() {
        $this->result = [];

        $query = "SELECT id_genre, name_genre FROM genre";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_OK_MSG;
                $this->result["data"] = $this->cleanResult($stmt->fetchAll());
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

    /**
     * Recoge todos los formatos de libros
     * @return array $result --> Devuelve datos de la query (si los hubiese) y código de error
     */
    public function getFormat() {
        $this->result = [];

        $query = "SELECT id_format, name_format FROM format";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->result["code"] = QUERY_OK; 
                $this->result["msg"] = QUERY_OK_MSG;
                $this->result["data"] = $this->cleanResult($stmt->fetchAll());
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

    // ----------------------------
    // --- FUNCIONES AUXILIARES ---
    // ----------------------------

    /**
     * Borra los valores numéricos del las respuestas, reduciendo a la mitad todos los datos
     * @param array $arr
     * @return array $arr
     */
    private function cleanResult($arr) {
        foreach ($arr as $mainKey => $value) {
            foreach ($value as $key => $field) {
                if (is_numeric($key)) {
                    unset($arr[$mainKey][$key]);
                }
            }
        }

        return $arr;
    }

    /**
     * Genera el link de una portada a partir de una base de datos de libros
     * @param int $isbn
     * @return string $imgLink
     */
    private function getPicFromISBN($isbn){
        $imgLink = '';

        if (strlen($isbn) == 13) {
            $lastDigits = substr($isbn,-4);
            $imgLink = 'https://images.isbndb.com/covers/'.substr($lastDigits, 0, 2).'/'.substr($lastDigits, 2).'/'.$isbn.'.jpg';
        } else if (strlen($isbn) == 10) {
            $lastDigits = substr($this->convertToISBN13($isbn),-4);
            $imgLink = 'https://images.isbndb.com/covers/'.substr($lastDigits, 0, 2).'/'.substr($lastDigits, 2).'/'.$isbn.'.jpg';
        }
        
        return $imgLink;
    }

    /**
     * Convierte un ISBN-10 a un ISBN-13
     * @param int $isbn
     * @return string $isbn13
     */
    private function convertToISBN13($isbn) {
        $isbn13 = '978'.substr($isbn,0,-1);
        $lastDigit = 0;

        foreach (str_split($isbn13) as $key => $value) {
            if ($key % 2 != 0) {
                $lastDigit += 3*intval($value);
            } else {
                $lastDigit += intval($value);
            }
        }

        $isbn13 = substr($isbn13,-1).(10-($lastDigit%10));

        return $isbn13;
    }

    /**
     * Transforma un Array en un .csv encriptado en base64
     * @param array $arr
     * @return string $csvContent
     */
    private function exportArrayToCSV($arr) {
        $csv = fopen('php://memory','rw+');

        fputcsv($csv, array_keys($arr[0]), ";");
        foreach ($arr as $key => $value) {
            fputcsv($csv, $value, ";");
        }

        rewind($csv);
        $csvContent = trim(stream_get_contents($csv));
        fclose($csv);

        return base64_encode($csvContent);
    }    
}
