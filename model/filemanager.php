<?php

namespace Slrfw\Model;

class fileManager extends manager {

//    static $_dirs = array(
//        'temp'     => "temp",
//        'media'    => "fichiers",
//        'vignette' => "mini",
//    );

    static $_extensions = array(
        'image' => array(
            'jpg' => "jpeg",
            'jpeg' => "jpeg",
            'gif' => "gif",
            'png' => "png",
        )
    );
    static $_vignette = array(
        "max-width" => 200,
        "max-height" => 50,
    );
    static $_apercu = array(
        "max-width" => 200,
        "max-height" => 90,
    );

    static function isImage($file) {
        $ext = strtolower(array_pop(explode(".", $file)));
        if (array_key_exists($ext, self::$_extensions['image']))
            return self::$_extensions['image'][$ext];

        return FALSE;
    }

    public function addFolder($id, $nom, $id_parent) {
        $json = array('status' => "error");

        if (strlen($nom) > 2) {
            $ordre = "SELECT MAX(`ordre`) FROM `media_dossier` WHERE `id_parent` = $id_parent";
            $rewriting = $this->_db->rewrit($nom, "media_dossier", "rewriting", "AND `id_parent` = $id_parent");
            $query = "INSERT INTO `media_dossier` (`id`, `id_parent`, `rewriting`, `ordre`, `date_crea`) VALUES"
                    . " ($id, $id_parent, " . $this->_db->quote($rewriting) . ", $ordre, NOW())";

            if ($db->query($queryInsert)) {
                $json['id'] = $db->lastInsertId();
                $json['rewriting'] = $rewriting;

//            $query = "
//                SELECT `r`.*, `p`.`rewriting` `prewriting`
//                FROM `res_categorie` `r`
//                    LEFT JOIN `res_categorie` `p` ON `r`.`id_parent` = `p`.`id`
//                WHERE `r`.`id` = " . ($_REQUEST['id'] ? $_REQUEST['id'] : "0");
//            $categorie = $db->query($query)->fetch(\PDO::FETCH_ASSOC);
//            $rewriting = $db->rewrit($_REQUEST['title'], "res_categorie", "rewriting", "AND `id_parent` = " . $_REQUEST['id']);
                //	$json['rewriting'] = $rewriting;
            }

            $json['status'] = "success";
        }

        return $json;
    }

    public function createFolder($chemin) {
        umask(0000);
        return mkdir($chemin, 0777);
    }

    public function getList($id_gab_page = 0, $id_temp = 0, $search = null, $orderby = null, $sens = null) {
        $query = "SELECT * FROM `media_fichier` WHERE `suppr` = 0";

        if ($id_gab_page)
            $query .= " AND `id_gab_page` = $id_gab_page";

        if ($id_temp)
            $query .= " AND `id_temp` = $id_temp";

        if ($search) {
            $search = "%" . $search . "%";
            $query .= " AND `rewriting` LIKE " . $this->_db->quote($search);
        }

        if ($orderby) {
            $query .= " ORDER BY `$orderby` ";
            if ($sens)
                $query .= $sens;
        }

//        echo "<!--$query-->";

        $files = $this->_db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $files;
    }

    public function getSearch($term, $id_gab_page = 0, $id_temp = 0, $extensions = FALSE) {
        $query = "SELECT * FROM `media_fichier` WHERE `suppr` = 0";

        if ($id_gab_page)
            $query .= " AND `id_gab_page` = $id_gab_page";

        if ($id_temp)
            $query .= " AND `id_temp` = $id_temp";

//        $term = "%" . array_pop(explode("/", $term)) . "%";
        $term = "%" . $term . "%";
        $query .= " AND `rewriting` LIKE " . $this->_db->quote($term);

        $files = $this->_db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        if (is_array($extensions)) {
            $files2 = array();

            foreach ($files as $file) {
                $ext = pathinfo($file['rewriting'], PATHINFO_EXTENSION);
                if (in_array($ext, $extensions)) {
                    $files2[] = $file;
                }
            }

            return $files2;
        }

        return $files;
    }

    public function upload($targetTmp, $targetDir, $vignetteDir, $apercuDir) {
        // HTTP headers for no cache etc.
        header('Content-type: text/plain; charset=UTF-8');
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

//        $targetTmp   = self::$_dirs['temp'];
//        $targetDir   = self::$_dirs['media'] . DIRECTORY_SEPARATOR . $id_gab_page;
//        $vignetteDir = self::$_dirs['media'] . DIRECTORY_SEPARATOR . $id_gab_page . DIRECTORY_SEPARATOR . self::$_dirs['vignette'];

        if (!file_exists($targetDir))
            $this->createFolder($targetDir);

        if (!file_exists($vignetteDir))
            $this->createFolder($vignetteDir);

        if (!file_exists($apercuDir))
            $this->createFolder($apercuDir);

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Get parameters
        $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 1;
        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

        // Clean the fileName for security reasons
        $fileName = str_replace(array("_", " "), "-", $fileName);
        $fileName = preg_replace("#[-]+#Usi", '-', strtolower($fileName));
        $fileName = preg_replace("#[^\w\.-]+#Usi", '', strtolower($fileName));

        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"]))
            $contentType = $_SERVER["CONTENT_TYPE"];

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen($targetTmp . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = fopen($_FILES['file']['tmp_name'], "rb");

                    if ($in) {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else
                        return array("jsonrpc" => "2.0", "status" => "error", "error" => array("code" => 101, "message" => "Failed to open input stream."), "id" => "id");
                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                } else
                    return array("jsonrpc" => "2.0", "status" => "error", "error" => array("code" => 102, "message" => "Failed to open output stream."), "id" => "id");
            } else
                return array("jsonrpc" => "2.0", "status" => "error", "error" => array("code" => 103, "message" => "Failed to move uploaded file."), "id" => "id");
        } else {
            // Open temp file
            $out = fopen($targetTmp . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
            if ($out) {
                // Read binary input stream and append it to temp file
                $in = fopen("php://input", "rb");

                if ($in) {
                    while ($buff = fread($in, 4096))
                        fwrite($out, $buff);
                } else
                    return array("jsonrpc" => "2.0", "status" => "error", "error" => array("code" => 101, "message" => "Failed to open input stream."), "id" => "id");

                fclose($in);
                fclose($out);
            } else
                return array("jsonrpc" => "2.0", "status" => "error", "error" => array("code" => 102, "message" => "Failed to open output stream."), "id" => "id");
        }

        // Construct JSON-RPC response
        $jsonrpc = array(
            "jsonrpc" => "2.0",
            "status" => "success",
            "result" => $fileName,
                //		"debug"		=> json_encode($_REQUEST)
        );

        // Dernière partie.
        if ($chunk == $chunks - 1) {
            $fileNameNew = $fileName;

            // On renomme pour éviter d'écraser un fichier existant
            if (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
                $ext = strrpos($fileName, '.');
                $fileName_a = substr($fileName, 0, $ext);
                $fileName_b = substr($fileName, $ext);

                $count = 1;
                while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '-' . $count . $fileName_b))
                    $count++;

                $fileNameNew = $fileName_a . '-' . $count . $fileName_b;
            }

            // On déplace le fichier temporaire
            @copy($targetTmp . DIRECTORY_SEPARATOR . $fileName, $targetDir . DIRECTORY_SEPARATOR . $fileNameNew);
            @unlink($targetTmp . DIRECTORY_SEPARATOR . $fileName);

            $size = filesize($targetDir . DIRECTORY_SEPARATOR . $fileNameNew);

            // Création de la miniature.
            $ext = strtolower(array_pop(explode(".", $fileNameNew)));
            if (array_key_exists($ext, self::$_extensions['image'])) {
                $largeurmax = self::$_vignette['max-width'];
                $hauteurmax = self::$_vignette['max-height'];

                $sizes = getimagesize($targetDir . DIRECTORY_SEPARATOR . $fileNameNew);
                $width = $sizes[0];
                $height = $sizes[1];
                $jsonrpc['taille'] = $sizes[0] . " x " . $sizes[1];

                if ($this->_vignette($targetDir . DIRECTORY_SEPARATOR . $fileNameNew, $ext, $vignetteDir . DIRECTORY_SEPARATOR . $fileNameNew, $largeurmax, $hauteurmax))
                    $jsonrpc['minipath'] = $vignetteDir . DIRECTORY_SEPARATOR . $fileNameNew;

                $largeurmax = self::$_apercu['max-width'];
                $hauteurmax = self::$_apercu['max-height'];
                $this->_vignette($targetDir . DIRECTORY_SEPARATOR . $fileNameNew, $ext, $apercuDir . DIRECTORY_SEPARATOR . $fileNameNew, $largeurmax, $hauteurmax);
            }
            else {
                $width = 0;
                $height = 0;
                $jsonrpc['taille'] = "";
            }


            // Ajout d'informations utiles (ou pas)
            $jsonrpc['filename'] = $fileNameNew;
            $jsonrpc['size'] = $size;
            $jsonrpc['width'] = $width;
            $jsonrpc['height'] = $height;
            $jsonrpc['path'] = $targetDir . DIRECTORY_SEPARATOR . $fileNameNew;
            $jsonrpc['date'] = date("d/m/Y H:i:s");
        }

        return $jsonrpc;
    }

    public function uploadGabPage($id_gab_page, $id_temp, $targetTmp, $targetDir, $vignetteDir, $apercuDir) {
        $json = $this->upload($targetTmp, $targetDir, $vignetteDir, $apercuDir);
        if (isset($json['filename'])) {
            $this->_insertToMediaFile($json['filename'], $id_gab_page, $id_temp, $json['size'], $json['width'], $json['height']);
        }

        return $json;
    }

    /**
     *
     */
    private function _insertToMediaFile($fileNameNew, $id_gab_page, $id_temp, $size, $width, $height) {
        $query = "INSERT INTO `media_fichier` (`rewriting`, `id_gab_page`, `id_temp`, `taille`, `width`, `height`, `vignette`, `date_crea`) VALUES ('$fileNameNew', $id_gab_page, $id_temp, '$size', $width, $height, '$fileNameNew', NOW())";
        $this->_db->query($query);
        return $this->_db->lastInsertId();
    }

    /**
     *
     * @param string $fileSource
     * @param string $ext
     * @param string $destinationName
     * @param int $largeurmax
     * @param int $hauteurmax
     */
    private function _vignette($fileSource, $ext, $destinationName, $largeurmax, $hauteurmax) {
        if (!array_key_exists($ext, self::$_extensions['image']))
            return FALSE;

        $source = call_user_func("imagecreatefrom" . self::$_extensions['image'][$ext], $fileSource);

        // Les fonctions imagesx et imagesy renvoient la largeur et la hauteur d'une image
        $largeur_source = imagesx($source);
        $hauteur_source = imagesy($source);

        if ($largeur_source > $largeurmax || $hauteur_source > $hauteurmax) {
            $ratio = $hauteur_source / $hauteurmax;
            if ($largeur_source / $ratio > $largeurmax)
                $ratio = $largeur_source / $largeurmax;

            $largeur_destination = $largeur_source / $ratio;
            $hauteur_destination = $hauteur_source / $ratio;

            $destination = imagecreatetruecolor($largeur_destination, $hauteur_destination); //image miniature vide crée
            // Transparence
            if ($ext == "png" || $ext == "gif") {
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
            }

            // On crée la miniature
            imagecopyresampled($destination, $source, 0, 0, 0, 0, $largeur_destination, $hauteur_destination, $largeur_source, $hauteur_source);

            // On enregistre la miniature sous le nom "mini_image.jpg"
            call_user_func("image" . self::$_extensions['image'][$ext], $destination, $destinationName);
        }
        else
            copy($fileSource, $destinationName);

        return TRUE;
    }

}