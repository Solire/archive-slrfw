<?php
/**
 *
 */

namespace Slrfw\Model;

/**
 *
 */
class fileManager extends manager {
    
    /**
     *
     * @var Nom de la table media
     */
    protected $mediaTableName = 'media_fichier';

    /**
     *
     * @var array
     */
    static $_extensions = array(
        'image' => array(
            'jpg' => "jpeg",
            'jpeg' => "jpeg",
            'gif' => "gif",
            'png' => "png",
        )
    );

    /**
     *
     * @var array
     */
    static $_vignette = array(
        "max-width" => 200,
        "max-height" => 50,
    );

    /**
     *
     * @var array
     */
    static $_apercu = array(
        "max-width" => 200,
        "max-height" => 90,
    );

    /**
     * Renvoi l'extension de l'image (utilisé dans le nom des fonctions de
     * traitement des images, ou false si l'extension du fichier n'est pas
     * une image.
     *
     * @param string $fileName nom du fichier
     *
     * @return false|string
     */
    static function isImage($fileName)
    {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        if (isset(self::$_extensions['image'][$ext])) {
            return self::$_extensions['image'][$ext];
        }

        return false;
    }

    /**
     * Crée un dossier avec les droits 777
     *
     * @param string $chemin chemin du dossier à créer
     *
     * @return bool
     */
    public function createFolder($chemin)
    {
        umask(0000);
        return mkdir($chemin, 0777);
    }

    /**
     * Renvoi un tableau de fichiers lié à une page.
     *
     * @param int    $id_gab_page identifiant de la page
     * @param int    $id_temp     identifiant temporaire (page en création)
     * @param string $search      chaîne cherchée
     * @param string $orderby     colonne de tri
     * @param string $sens        sens du tri (ASC|DESC)
     *
     * @return array
     */
    public function getList(
        $id_gab_page = 0,
        $id_temp = 0,
        $search = null,
        $orderby = null,
        $sens = null
    ) {
        $query = 'SELECT media_fichier.*, IF(id_version IS NULL, 0, 1) utilise '
                . 'FROM `media_fichier` '
                . 'LEFT JOIN media_fichier_utilise '
                . 'ON `media_fichier`.rewriting = media_fichier_utilise.rewriting '
                . 'WHERE `suppr` = 0 ';

        if ($id_gab_page)
            $query .= ' AND `media_fichier`.`id_gab_page` = ' . $id_gab_page;

        if ($id_temp)
            $query .= ' AND `id_temp` = ' . $id_temp;

        if ($search) {
            $search = '%' . $search . '%';
            $query .= ' AND `media_fichier`.`rewriting` LIKE ' . $this->_db->quote($search);
        }

        $query .= ' GROUP BY `media_fichier`.rewriting';

        if ($orderby) {
            $query .= ' ORDER BY `' . $orderby . '` ';
            if ($sens)
                $query .= $sens;
        }



        $files = $this->_db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return $files;
    }

    /**
     * Renvoi un tableau de fichiers lié à une page.
     *
     * @param string   $term        chaîne cherchée
     * @param int      $id_gab_page identifiant de la page
     * @param int      $id_temp     identifiant temporaire (page en création)
     * @param string[] $extensions  tableau d'extension permise
     *
     * @return array
     */
    public function getSearch(
        $term,
        $id_gab_page = 0,
        $id_temp = 0,
        $extensions = false
    ) {
        $query = 'SELECT media_fichier.*, IF(id_version IS NULL, 0, 1) utilise '
                . 'FROM `media_fichier` '
                . 'LEFT JOIN media_fichier_utilise '
                . 'ON `media_fichier`.rewriting = media_fichier_utilise.rewriting '
                . 'WHERE `suppr` = 0';

        if ($id_gab_page) {
            $query .= ' AND `media_fichier`.`id_gab_page` = ' . $id_gab_page;
        }

        if ($id_temp) {
            $query .= ' AND `media_fichier`.`id_temp` = ' . $id_temp;
        }

        $term = '%' . $term . '%';
        $query .= ' AND `media_fichier`.`rewriting` LIKE ' . $this->_db->quote($term);

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

    /**
     * Upload un fichier et crée des vignettes
     *
     * @param string $uploadDir   dossier principal d'upload (exemple :
     * 'projet/upload')
     * @param string $targetTmp   dossier de téléchargement temporaire
     * (exemple : 'temp')
     * @param string $targetDir   dossier de téléchargement final (exemple :
     * identifiant d'une page ou 'temp-' + l'identifiant temporaire d'une
     * page en création)
     * @param string $vignetteDir dossier contenant les vignettes (exemple :
     * 'mini')
     * @param string $apercuDir   dossier contenant les apercus (exemple :
     * 'apercu')
     *
     * @return array
     */
    public function upload(
        $uploadDir,
        $targetTmp,
        $targetDir,
        $vignetteDir,
        $apercuDir
    ) {
        /** HTTP headers for no cache etc. */
        header('Content-type: text/plain; charset=UTF-8');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        /** 5 minutes execution time */
        @set_time_limit(5 * 60);

        /** Get parameters */
        $chunk      = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $chunks     = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 1;
        $fileName   = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
        $fileName = strtolower($fileName);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $name = pathinfo($fileName, PATHINFO_FILENAME);

        if ($chunk == $chunks - 1) {
            if (!file_exists($uploadDir . DS . $targetDir)) {
                $this->createFolder($uploadDir . DS . $targetDir);
            }

            if (!file_exists($uploadDir . DS . $vignetteDir)) {
                $this->createFolder($uploadDir . DS . $vignetteDir);
            }

            if (!file_exists($uploadDir . DS . $apercuDir)) {
                $this->createFolder($uploadDir . DS . $apercuDir);
            }
        }

        /** Clean the fileName for security reasons */
        $name = $this->_db->rewrit($name);
        $fileName = $name . '.' . $ext;

        /** Look for the content type header */
        if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
            $contentType = $_SERVER['HTTP_CONTENT_TYPE'];
        }

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $contentType = $_SERVER['CONTENT_TYPE'];
        }

        /**
         * Handle non multipart uploads older WebKit
         * versions didn't support multipart in HTML5
         */
        if (strpos($contentType, 'multipart') !== false) {
            if (isset($_FILES['file']['tmp_name'])
                && is_uploaded_file($_FILES['file']['tmp_name'])
            ) {
                /** Open temp file */

                if ($chunk == 0) {
                    $mode = 'wb';
                } else {
                    $mode = 'ab';
                }

                $out = fopen($uploadDir . DS . $targetTmp . DS . $fileName,
                    $mode);

                if ($out) {
                    /** Read binary input stream and append it to temp file */
                    $in = fopen($_FILES['file']['tmp_name'], 'rb');

                    if ($in) {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else {
                        return array(
                            'jsonrpc' => '2.0',
                            'status' => 'error',
                            'error' => array(
                                'code' => 101,
                                'message' => 'Failed to open input stream.'
                            ),
                            'id' => 'id'
                        );
                    }

                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                } else {
                    return array(
                        'jsonrpc' => '2.0',
                        'status' => 'error',
                        'error' => array(
                            'code' => 102,
                            'message' => 'Failed to open output stream.'
                        ),
                        'id' => 'id'
                    );
                }
            } else {
                return array(
                    'jsonrpc' => '2.0',
                    'status' => 'error',
                    'error' => array(
                        'code' => 103,
                        'message' => 'Failed to move uploaded file.'
                    ),
                    'id' => 'id'
                );
            }
        } else {
            /** Open temp file */
            if ($chunk == 0) {
                $mode = 'wb';
            } else {
                $mode = 'ab';
            }

            $out = fopen($uploadDir . DS . $targetTmp . DS . $fileName, $mode);

            if ($out) {
                /** Read binary input stream and append it to temp file */
                $in = fopen('php://input', 'rb');

                if ($in) {
                    while ($buff = fread($in, 4096))
                        fwrite($out, $buff);
                } else {
                    return array(
                        'jsonrpc' => '2.0',
                        'status' => 'error',
                        'error' => array(
                            'code' => 101,
                            'message' => 'Failed to open input stream.'
                        ),
                        'id' => 'id'
                    );
                }

                fclose($in);
                fclose($out);
            } else {
                return array(
                    'jsonrpc' => '2.0',
                    'status' => 'error',
                    'error' => array(
                        'code' => 102,
                        'message' => 'Failed to open output stream.'
                    ),
                    'id' => 'id'
                );
            }
        }

        /** Construct JSON-RPC response */
        $jsonrpc = array(
            'jsonrpc' => '2.0',
            'status' => 'success',
            'result' => $fileName,
        );

        /** Dernière partie. */
        if ($chunk == $chunks - 1) {
            $fileNameNew = $fileName;

            /** On renomme pour éviter d'écraser un fichier existant */
            if (file_exists($uploadDir . DS . $targetDir . DS . $fileName)) {
                $fileName_a = pathinfo($fileName, PATHINFO_FILENAME);
                $fileName_b = pathinfo($fileName, PATHINFO_EXTENSION);

                $count = 1;
                $path   = $uploadDir . DS . $targetDir . DS . $fileName_a . '-'
                        . $count . '.' . $fileName_b;
                while (file_exists($path)) {
                    $count++;
                    $path   = $uploadDir . DS . $targetDir . DS . $fileName_a
                            . '-' . $count . '.' . $fileName_b;
                }

                $fileNameNew = $fileName_a . '-' . $count . '.' . $fileName_b;
            }

            /** On déplace le fichier temporaire */
            rename($uploadDir . DS . $targetTmp . DS . $fileName,
                $uploadDir . DS . $targetDir . DS . $fileNameNew);

            $size = filesize($uploadDir . DS . $targetDir . DS . $fileNameNew);

            /** Création de la miniature. */
            $ext = pathinfo($fileNameNew, PATHINFO_EXTENSION);
            if (array_key_exists($ext, self::$_extensions['image'])) {
                $filePath = $uploadDir . DS . $targetDir . DS . $fileNameNew;
                $sizes = getimagesize($filePath);
                $width = $sizes[0];
                $height = $sizes[1];
                $jsonrpc['taille'] = $sizes[0] . " x " . $sizes[1];

                /** Création de la vignette  */
                $largeurmax = self::$_vignette['max-width'];
                $hauteurmax = self::$_vignette['max-height'];
                $this->vignette(
                    $filePath, $ext,
                    $uploadDir . DS . $vignetteDir . DS . $fileNameNew,
                    $largeurmax, $hauteurmax
                );
                $jsonrpc['mini_path'] = $uploadDir . DS . $vignetteDir . DS
                                      . $fileNameNew;
                $jsonrpc['mini_url']  = $vignetteDir . DS . $fileNameNew;

                /** Création de l'apercu  */
                $largeurmax = self::$_apercu['max-width'];
                $hauteurmax = self::$_apercu['max-height'];
                $this->vignette(
                    $filePath, $ext,
                    $uploadDir . DS . $apercuDir . DS . $fileNameNew,
                    $largeurmax, $hauteurmax
                );
            } else {
                $width = 0;
                $height = 0;
                $jsonrpc['taille'] = '';
            }

            /** Ajout d'informations utiles (ou pas) */
            $jsonrpc['filename']    = $fileNameNew;
            $jsonrpc['size']        = $size;
            $jsonrpc['width']       = $width;
            $jsonrpc['height']      = $height;
            $jsonrpc['path']        = $uploadDir . DS . $targetDir . DS
                                    . $fileNameNew;
            $jsonrpc['url']         = $targetDir . DS . $fileNameNew;
            $jsonrpc['date']        = date('d/m/Y H:i:s');
        }

        return $jsonrpc;
    }

    /**
     * Upload un média lié à une page
     *
     * @param string $uploadDir   dossier principal d'upload (exemple :
     * 'projet/upload')
     * @param int    $id_gab_page identifiant de la page (si elle est déjà créée)
     * @param int    $id_temp     identifiant temporaire de la page (si elle est
     * en cours de création)
     * @param string $targetTmp   dossier de téléchargement temporaire
     * (exemple : 'temp')
     * @param string $targetDir   dossier de téléchargement final (exemple :
     * identifiant d'une page ou 'temp-' + l'identifiant temporaire d'une
     * page en création)
     * @param string $vignetteDir dossier contenant les vignettes (exemple :
     * 'mini')
     * @param string $apercuDir   dossier contenant les apercus (exemple :
     * 'apercu')
     *
     * @return array
     */
    public function uploadGabPage(
        $uploadDir,
        $id_gab_page,
        $id_temp,
        $targetTmp,
        $targetDir,
        $vignetteDir,
        $apercuDir
    ) {
        $json = $this->upload($uploadDir, $targetTmp, $targetDir, $vignetteDir,
            $apercuDir);

        if (isset($json['filename'])) {
            $json['id'] = $this->insertToMediaFile($json['filename'],
                $id_gab_page, $id_temp, $json['size'], $json['width'],
                $json['height']
            );
        }

        return $json;
    }

//    protected function resizeGif($src, $dest, $largeur, $hauteur)
//    {
//        /** load/create images */
//        $img_src    = imagecreatefromgif($src);
//        $img_dst    = imagecreatetruecolor($largeur,$hauteur);
//        imagealphablending($img_dst, false);
//
//        /** get and reallocate transparency-color */
//        $transindex = imagecolortransparent($img_src);
//        if ($transindex >= 0) {
//            $transcol = imagecolorsforindex($img_src, $transindex);
//            $transindex = imagecolorallocatealpha($img_dst, $transcol['red'], $transcol['green'], $transcol['blue'], 127);
//            imagefill($img_dst, 0, 0, $transindex);
//        }
//
//        /** resample */
//        imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $largeur, $hauteur, $g_is[0], $g_is[1]);
//
//        /** restore transparency */
//        if($transindex >= 0) {
//            imagecolortransparent($img_dst, $transindex);
//            for($y=0; $y<$g_ih; ++$y) {
//                for($x=0; $x<$g_iw; ++$x) {
//                    if(((imagecolorat($img_dst, $x, $y)>>24) & 0x7F) >= 100) {
//                        imagesetpixel($img_dst, $x, $y, $transindex);
//                    }
//                }
//            }
//        }
//
//        /** save GIF */
//        imagetruecolortopalette($img_dst, true, 255);
//        imagesavealpha($img_dst, false);
//        imagegif($img_dst, $dest);
//        imagedestroy($img_dst);
//    }


    /**
     * Redimensionne, recadre et insert une image liée à une page.
     *
     * @param string    $uploadDir dossier principal d'upload (exemple :
     * 'projet/upload')
     * @param string    $fileSource  fichier a recadrer (exemple : '11/image.jpg',
     * 'temp-12/picture.png')
     * @param string    $ext         extension du fichier
     * @param string    $targetDir   dossier où l'image recadrée sera enregistrée.
     * @param string    $target      nom à donner au fichier recadré
     * @param int       $id_gab_page identifiant de la page (si elle est en cours
     * d'édition)
     * @param int       $id_temp     identifiant temporaire de la page (si elle
     * est en cours de création)
     * @param string    $vignetteDir dossier ou enregistré la vignette de l'image
     * recadrée
     * @param string    $apercuDir   dossier ou enregistré l'apercu de l'image
     * recadrée
     * @param int       $x           abscisse du coin en haut à gauche
     * @param int       $y           ordonnée du coin en haut à gauche
     * @param int       $w           largeur du recadrage
     * @param int       $h           hauteur du recadrage
     * @param false|int $targ_w      largeur de l'image redimensionné ou false
     * si pas de redimensionnement
     * @param false|int $targ_h      hauteur de l'image redimensionné ou false
     * si pas de redimensionnement
     *
     * @return array
     */
    public function crop(
        $uploadDir,
        $fileSource,
        $ext,
        $targetDir,
        $target,
        $id_gab_page,
        $id_temp,
        $vignetteDir,
        $apercuDir,
        $x,
        $y,
        $w,
        $h,
        $targ_w = false,
        $targ_h = false
    ) {
        $destinationName = $uploadDir . DS . $targetDir . DS . $target;
        $fileNameNew     = $target;
        $ext             = pathinfo($fileNameNew, PATHINFO_EXTENSION);

        /** On créé et on enregistre l'image recadrée */
        if ($targ_w == false) {
            $targ_w = $w;
        }

        if ($targ_h == false) {
            $targ_h = $h;
        }

        $src = $uploadDir . DS . $fileSource;
        $img_r = call_user_func(
            'imagecreatefrom' . self::$_extensions['image'][$ext], $src);
        $dst_r = imagecreatetruecolor($targ_w, $targ_h);

        /** Transparence */
        if ($ext == 'png' || $ext == 'gif') {
            imagecolortransparent($dst_r, imagecolorallocatealpha($dst_r, 0, 0,
                0, 127));
            imagealphablending($dst_r, false);
            imagesavealpha($dst_r, true);
        }

        imagecopyresampled($dst_r, $img_r, 0, 0, $x, $y, $targ_w, $targ_h, $w, $h);

        if ($ext == "png") {
            call_user_func("image" . self::$_extensions['image'][$ext], $dst_r,
                $destinationName, 0);
        }
        elseif ($ext == "gif") {
            call_user_func("image" . self::$_extensions['image'][$ext], $dst_r,
                $destinationName);
        }
        else {
            call_user_func("image" . self::$_extensions['image'][$ext], $dst_r,
                $destinationName, 95);
        }

        imagedestroy($dst_r);

        $size   = filesize($destinationName);
        $sizes  = getimagesize($destinationName);
        $width  = $sizes[0];
        $height = $sizes[1];

        $json = array(
            'taille'    => $sizes[0] . " x " . $sizes[1],
            'filename'  => $fileNameNew,
            'size'      => $size,
            'width'     => $width,
            'height'    => $height,
            'path'      => $targetDir . DS . $fileNameNew,
            'date'      => date("d/m/Y H:i:s"),
        );

        /** On créé la vignette */
        $largeurmax = self::$_vignette['max-width'];
        $hauteurmax = self::$_vignette['max-height'];
        $this->vignette($uploadDir . DS . $targetDir . DS . $fileNameNew,
            $ext, $uploadDir . DS . $vignetteDir . DS . $fileNameNew,
            $largeurmax, $hauteurmax);
        $jsonrpc['minipath'] = $vignetteDir . DS . $fileNameNew;

        /** On créé l'apercu */
        $largeurmax = self::$_apercu['max-width'];
        $hauteurmax = self::$_apercu['max-height'];
        $this->vignette($uploadDir . DS . $targetDir . DS . $fileNameNew,
            $ext, $uploadDir . DS . $apercuDir . DS . $fileNameNew,
            $largeurmax, $hauteurmax);

        /** On insert la ressource en base */
        $json['id'] = $this->insertToMediaFile($json['filename'], $id_gab_page,
            $id_temp, $json['size'], $json['width'], $json['height']);

        return $json;
    }

    /**
     * Insert un fichier (lié à une page) en base de donnée
     *
     * @param string $fileName    nom du fichier
     * @param int    $id_gab_page identifiant de la page (si elle est en cours
     * d'édition)
     * @param int    $id_temp     identifiant temporaire de la page (si elle
     * est en cours de création)
     * @param int    $size        taille (en octets) du fichier
     * @param int    $width       si le fichier est une image alors ceci est la
     * largeur de l'image
     * @param int    $height      si le fichier est une image alors ceci est la
     * hauteur de l'image
     *
     * @return int
     */
    public function insertToMediaFile(
        $fileName,
        $id_gab_page,
        $id_temp,
        $size,
        $width,
        $height
    ) {
        $query  = 'INSERT INTO `' . $this->mediaTableName . '` SET'
                . ' `rewriting` = ' . $this->_db->quote($fileName) . ','
                . ' `id_gab_page` = ' . $id_gab_page . ','
                . ' `id_temp` = ' . $id_temp . ','
                . ' `taille` = ' . $this->_db->quote($size) . ','
                . ' `width` = ' . $width . ','
                . ' `height` = ' . $height . ','
                . ' `vignette` = ' . $this->_db->quote($fileName) . ','
                . ' `date_crea` = NOW()';
        $this->_db->exec($query);
        return $this->_db->lastInsertId();
    }

    /**
     * Crée une version redimenssionnée d'une image
     *
     * @param string $fileSource      fichier à redimensionner
     * @param string $ext             extension du fichier à redimensionner
     * @param string $destinationName nom du fichier redimensionné
     * @param int    $largeurmax      largeur maximum de l'image redimensionnée
     * @param int    $hauteurmax      hauteur maximum de l'image redimensionnée
     *
     * @return bool
     */
    public function vignette(
        $fileSource,
        $ext,
        $destinationName,
        $largeurmax,
        $hauteurmax
    ) {
        if (!array_key_exists($ext, self::$_extensions['image'])) {
            return false;
        }

        $source = call_user_func(
            'imagecreatefrom' . self::$_extensions['image'][$ext],
            $fileSource
        );

        /**
         * Les fonctions imagesx et imagesy renvoient la largeur et la hauteur
         * d'une image
         */
        $largeur_source = imagesx($source);
        $hauteur_source = imagesy($source);

        if ($largeur_source > $largeurmax || $hauteur_source > $hauteurmax) {
            $ratio = $hauteur_source / $hauteurmax;
            if ($largeur_source / $ratio > $largeurmax) {
                $ratio = $largeur_source / $largeurmax;
            }

            $largeur_destination = $largeur_source / $ratio;
            $hauteur_destination = $hauteur_source / $ratio;

            $destination = imagecreatetruecolor($largeur_destination,
                $hauteur_destination);

            /** Transparence */
            if ($ext == 'png' || $ext == 'gif') {
                imagealphablending($destination, false);
                imagesavealpha($destination, true);
            }

            /** On crée la miniature */
            imagecopyresampled($destination, $source, 0, 0, 0, 0,
                $largeur_destination, $hauteur_destination, $largeur_source,
                $hauteur_source);

            if ($ext == "png") {
                call_user_func('image' . self::$_extensions['image'][$ext],
                $destination, $destinationName, 0);
            }
            elseif ($ext == "gif") {
                call_user_func('image' . self::$_extensions['image'][$ext],
                $destination, $destinationName);
            }
            else {
                call_user_func('image' . self::$_extensions['image'][$ext],
                $destination, $destinationName, 95);
            }
        }
        else {
            copy($fileSource, $destinationName);
        }

        return true;
    }

    public function setMediaTableName($mediaTableName) {
        $this->mediaTableName = $mediaTableName;
    }
    
}

