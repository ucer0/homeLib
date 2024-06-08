<?php 

/**
 * Para buscar estamos utilizando la Books API de OpenLibrary.org
 * Documentación aquí: https://openlibrary.org/dev/docs/api/books
 * 
 * No requiere Auth Tokens ni API keys
 */
class Book {

    /**
     * Recoge todos los datos posibles de un libro si se encuentra
     * @param int $isbn
     * @return array $result --> Devuelve datos de la query (si los hubiese) y código de error
     */
    public function getBookByISBN($isbn) {
        $result = [];
        $apiLink = "https://openlibrary.org/api/books?bibkeys=ISBN:".$isbn."&format=json&jscmd=data";
        $bookData = json_decode(file_get_contents($apiLink),1);

        if (!empty($bookData)) {
            // Hacemos que sea más fácil trabajar con el Array
            $bookData = $bookData["ISBN:".$isbn];
        
            $result["isbn"] = $isbn;
            $result["title"] = $bookData["title"];
            $result["subtitle"] = $bookData["subtitle"] ?? null;
            $result["author"] = $bookData["authors"][0]["name"];
            if (count($bookData["authors"])>1) {
                $result["coauthor"] = "";
                $isFirst = true;
                foreach ($bookData["authors"] as $key => $value) {
                    if ($isFirst) {
                        $isFirst = false;
                    } else {
                        $result["coauthor"] .= $value["name"].", ";
                    }
                }
                $result["coauthor"] = substr($result["coauthor"],0,-2);// Quita el ", " extra
            } else {
                $result["coauthor"] = null;
            }
            $result["editor"] = $bookData["publishers"][0]["name"] ?? null;
            $result["year"] = substr($bookData["publish_date"],-4) ?? null; // Solo queremos el año
            $result["pages"] = $bookData["number_of_pages"] ?? null;
            $result["pic"] = $bookData["cover"]["large"] ?? null;
        }

        return $result;
    }
}

