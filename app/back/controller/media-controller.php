<?php

require_once 'main-controller.php';

class MediaController extends MainController {

    /**
     *
     * @var page
     */
    private $_page = null;

    public function start() {
        parent::start();

        $upload = $this->_mainConfig->get("upload");
        $this->_upload_path = $upload['path'];
        $this->_upload_temp = $upload['temp'];
        $this->_upload_vignette = $upload['vignette'];
        $this->_upload_apercu = $upload['apercu'];
    }

    public function startAction() {

        $extensionsImage = array("jpeg", "jpg", "png", "gif");

        $this->_javascript->addLibrary("back/jquery/jquery.hotkeys.js");
        $this->_javascript->addLibrary("back/jstree/jquery.jstree.js");
        $this->_javascript->addLibrary("back/jquery/jquery.dataTables.min.js");
        $this->_javascript->addLibrary("back/plupload/plupload.full.min.js");
        $this->_javascript->addLibrary("back/listefichiers.js");

        $this->_css->addLibrary("back/demo_table_jui.css");

        $this->_view->breadCrumbs[] = array(
            "label" => "Gestion des fichiers",
            "url" => "",
        );
    }

    public function listAction() {
        $this->_view->main(FALSE);
        $this->_files = array();

        $id_gab_page = isset($_REQUEST['id_gab_page']) && $_REQUEST['id_gab_page'] ? $_REQUEST['id_gab_page'] : 0;

        if ($id_gab_page) {
            $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
            $orderby = isset($_REQUEST['orderby']['champ']) ? $_REQUEST['orderby']['champ'] : '';
            $sens = isset($_REQUEST['orderby']['sens']) ? $_REQUEST['orderby']['sens'] : '';

            $this->_page = $this->_gabaritManager->getPage(BACK_ID_VERSION, BACK_ID_API, $id_gab_page);

            $this->_files = $this->_fileManager->getList($this->_page->getMeta("id"), $search, $orderby, $sens);
        }

        foreach ($this->_files as &$file) {
            $ext = strtolower(array_pop(explode(".", $file['rewriting'])));
            $prefixPath = $this->_api["id"] == 1 ? ".." . DIRECTORY_SEPARATOR : ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR;
            $file['path'] = $prefixPath . $this->_upload_path . DIRECTORY_SEPARATOR
                    . $file['id_gab_page'] . DIRECTORY_SEPARATOR
                    . $file['rewriting'];

            $serverpath = ".." . DIRECTORY_SEPARATOR . $this->_upload_path . DIRECTORY_SEPARATOR
                    . $file['id_gab_page'] . DIRECTORY_SEPARATOR
                    . $file['rewriting'];

            $file['class'] = 'hoverprevisu vignette';

            if (array_key_exists($ext, fileManager::$_extensions['image'])) {
                $file['path_mini'] = $prefixPath . $this->_upload_path . DIRECTORY_SEPARATOR
                        . $file['id_gab_page'] . DIRECTORY_SEPARATOR
                        . $this->_upload_vignette . DIRECTORY_SEPARATOR
                        . $file['rewriting'];

                $sizes = getimagesize($serverpath);
                $file['width'] = $sizes[0];
                $file['height'] = $sizes[1];
            } else {
                $file['class'] = 'vignette';
                $file['path_mini'] = "img/back/$ext.png";
            }

//            $file['poids'] = (round((100 * $file['taille']) / (8 * 1024)) / 100) . " Ko";
            $file['poids'] = Tools::format_taille($file['taille']);
        }

        $this->_view->files = $this->_files;
    }

    public function popuplistefichiersAction() {
        $this->listAction();
    }

    public function folderlistAction() {
        $this->_view->main(FALSE);
        $this->_view->enable(FALSE);

        $res = array();

        if ($_REQUEST['id'] === "") {
            $res[] = array(
                "attr" => array(
                    "id" => "node_0",
                    "rel" => "root"
                ),
                "data" => array(
                    "title" => "Ressources"
                ),
                "state" => "closed"
            );
        } elseif ($_REQUEST['id'] === "0") {
            $rubriques = $this->_gabaritManager->getList(BACK_ID_VERSION, $this->_api["id"], 0);
            foreach ($rubriques as $rubrique)
                $res[] = array(
                    "attr" => array(
                        "id" => "node_" . $rubrique->getMeta('id'),
                        "rel" => "page"
                    ),
                    "data" => array(
                        "title" => $rubrique->getMeta('titre')
                    ),
                    "state" => "closed"
                );
        } else {
            $sous_rubriques = $this->_gabaritManager->getList(BACK_ID_VERSION, $this->_api["id"], $_REQUEST['id']);

            foreach ($sous_rubriques as $sous_rubrique) {
                $nbre = $this->_db->query("SELECT COUNT(*) FROM `media_fichier` WHERE `suppr` = 0 AND `id_gab_page` = " . $sous_rubrique->getMeta('id'))->fetchColumn();

                $res[] = array(
                    "attr" => array(
                        "id" => "node_" . $sous_rubrique->getMeta('id'),
                        "rel" => "page"
                    ),
                    "data" => array(
                        "title" => ( strlen($sous_rubrique->getMeta('titre')) > 16 ? mb_substr($sous_rubrique->getMeta('titre'), 0, 16, "utf-8") . "&hellip;" : $sous_rubrique->getMeta('titre') ) . " (<i>$nbre</i>)",
                        "attr" => array(
                            "title" => $sous_rubrique->getMeta('titre')
                        )
                    ),
                    "state" => "closed"
                );
            }
        }

        echo json_encode($res);
    }

    public function uploadAction() {
        $this->_view->enable(FALSE);
        $this->_view->main(FALSE);

        $id_gab_page = isset($_COOKIE['id_gab_page']) && $_COOKIE['id_gab_page'] ? $_COOKIE['id_gab_page'] : 0;

        if ($id_gab_page) {
            $this->_page = $this->_gabaritManager->getPage(BACK_ID_VERSION, BACK_ID_API, $id_gab_page);
            $prefixPath = $this->_api["id"] == 1 ? "" : ".." . DIRECTORY_SEPARATOR;
            if ($this->_page) {
                $targetTmp = "../" . $this->_upload_path . DIRECTORY_SEPARATOR . $this->_upload_temp;
                $targetDir = "../" . $this->_upload_path . DIRECTORY_SEPARATOR . $this->_page->getMeta("id");
                $vignetteDir = "../" . $this->_upload_path . DIRECTORY_SEPARATOR . $this->_page->getMeta("id") . DIRECTORY_SEPARATOR . $this->_upload_vignette;
                $apercuDir = "../" . $this->_upload_path . DIRECTORY_SEPARATOR . $this->_page->getMeta("id") . DIRECTORY_SEPARATOR . $this->_upload_apercu;

                $json = $this->_fileManager->uploadGabPage($this->_page->getMeta("id"), $targetTmp, $targetDir, $vignetteDir, $apercuDir);
                if (isset($json["minipath"]))
                    $json["minipath"] = $prefixPath . $json["minipath"];
                $json["path"] = $prefixPath . $json["path"];
                $json["size"] = tools::format_taille($json["size"]);
                
            } else {
                $json = array(
                    "jsonrpc" => "2.0",
                    "status" => "error",
                    "error" => array(
                        "code" => 110,
                        "message" => "Pas de page parente",
                    ),
                    "id" => "id",
                );
            }
        } else {
            $json = array(
                "jsonrpc" => "2.0",
                "status" => "error",
                "error" => array(
                    "code" => 110,
                    "message" => "Failed to receive folder's id.",
                ),
                "id" => "id",
            );
        }
        if ($json["status"] == "error") {
            $this->_log->logThis("Upload échoué", $this->_utilisateur->get("id"), "<b>Nom</b> : " . $_REQUEST["name"] . "<br /><b>Page</b> : " . $this->_page->getMeta("id") . '<br /><span style="color:red;">Error ' . $json["error"]["code"] . " : " . $json["error"]["message"] . '</span>');
        } else {
            $this->_log->logThis("Upload réussi", $this->_utilisateur->get("id"), "<b>Nom</b> : " . $_REQUEST["name"] . "<br /><b>Page</b> : " . $this->_page->getMeta("id"));
        }

        exit(json_encode($json));
    }

    public function deleteAction() {
        $this->_view->enable(FALSE);
        $this->_view->main(FALSE);

        $id_media_fichier = isset($_COOKIE['id_media_fichier']) && $_COOKIE['id_media_fichier'] ? $_COOKIE['id_media_fichier'] : (isset($_REQUEST['id_media_fichier']) && $_REQUEST['id_media_fichier'] ? $_REQUEST['id_media_fichier'] : 0);
        $query = "UPDATE `media_fichier` SET `suppr` = NOW() WHERE `id` = " . $id_media_fichier;
        $status = $this->_db->query($query) ? "success" : "error";
        $json = array("status" => $status);
        if (!$json["status"]) {
            $this->_log->logThis("Suppression de fichier échouée", $this->_utilisateur->get("id"), "<b>Id</b> : " . $id_media_fichier . '<br /><span style="color:red;">Error</span>');
        } else {
            $this->_log->logThis("Suppression de fichier réussie", $this->_utilisateur->get("id"), "<b>Id</b> : " . $id_media_fichier);
        }
        exit(json_encode($json));
    }

    public function autocompleteAction() {
        $this->_view->enable(FALSE);
        $this->_view->main(FALSE);

        $id_gab_page = isset($_REQUEST['id_gab_page']) && $_REQUEST['id_gab_page'] ? $_REQUEST['id_gab_page'] : (isset($_COOKIE['id_gab_page']) && $_COOKIE['id_gab_page'] ? $_COOKIE['id_gab_page'] : 0);

        if (isset($_REQUEST['extensions']) && $_REQUEST['extensions'] != "") {
            $extensions = explode(";", $_REQUEST['extensions']);
        } else {
            $extensions = FALSE;
        }

//        $extensionsImage = array("jpeg", "jpg", "png", "gif");


        $json = array();

        $term = isset($_GET['term']) ? $_GET['term'] : '';
        $tinyMCE = isset($_GET['tinyMCE']);

        if ($id_gab_page) {
            $this->_page = $this->_gabaritManager->getPage(BACK_ID_VERSION, BACK_ID_API, $id_gab_page, 0);

            $files = $this->_fileManager->getSearch($term, $this->_page->getMeta("id"), $extensions);



            foreach ($files as $file) {
                if (!$tinyMCE || fileManager::isImage($file['rewriting'])) {
                    $path = Registry::get("base") . $this->_upload_path . DIRECTORY_SEPARATOR
                            . $file['id_gab_page'] . DIRECTORY_SEPARATOR
                            . $file['rewriting'];
                    $vignette = Registry::get("base") . $this->_upload_path . DIRECTORY_SEPARATOR
                            . $file['id_gab_page'] . DIRECTORY_SEPARATOR
                            . $this->_upload_vignette . DIRECTORY_SEPARATOR
                            . $file['rewriting'];
                    $serverpath = ".." . DIRECTORY_SEPARATOR . $this->_upload_path . DIRECTORY_SEPARATOR
                            . $file['id_gab_page'] . DIRECTORY_SEPARATOR
                            . $file['rewriting'];

//                    $this->_page = $this->_gabaritManager->getPage(BACK_ID_VERSION, $file['id_gab_page']);
                    $realpath = Registry::get("base") . $file['id_gab_page'] . '/' . $file['rewriting'];

//                    $ext = array_pop(explode(".", $file['rewriting']));
                    if (fileManager::isImage($file['rewriting'])) {
                        $sizes = getimagesize($serverpath);
                        $size = $sizes[0] . " x " . $sizes[1];
                    }
                    else
                        $size = "";

                    if ($tinyMCE) {
                        $json[] = array(
                            $file['rewriting'] . ($size ? " ($size)" : ""),
                            $realpath,
                        );
                    } else {
                        $json[] = array(
                            "path" => $path,
                            "vignette" => $vignette,
                            "label" => $file['rewriting'],
                            "size" => ($size ? "$size" : ""),
                            "value" => $file['rewriting'],
                        );
                    }
                }
            }
        }

        if ($tinyMCE) {
            header("content-type: application/x-javascript; charset=UTF-8");
            exit("var tinyMCEImageList = " . json_encode($json) . ";");
        }

        exit(json_encode($json));
    }

}