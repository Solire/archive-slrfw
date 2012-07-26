<?php

require_once 'main-controller.php';

class PageController extends MainController {

    private $_cache = null;

    /**
     *
     * @var gabarit_page
     */
    private $_page = null;

    /**
     * Toujours executé avant l'action.
     *
     * @return void
     */
    public function start() {
        parent::start();
    }

//end start()


    public function matriceAction() {
        $this->_view->enable(FALSE);
        //TODO
        $query = "SELECT `c`.*,"
                . " `table_field_id`.`value` `table_field_id_value`,"
                . " `table_name`.`value` `table_name_value`,"
                . " `table_field_label`.`value` `table_field_label_value`"
                . " FROM `gab_champ` `c`"
                . " JOIN `gab_champ_param_value` `table_field_id`"
                . " ON `table_field_id`.`id_champ` = `c`.`id` AND `table_field_id`.`code_champ_param` LIKE 'TABLE.FIELD.ID'"
                . " JOIN `gab_champ_param_value` `table_name`"
                . " ON `table_name`.`id_champ` = `c`.`id` AND `table_name`.`code_champ_param` LIKE 'TABLE.NAME'"
                . " JOIN `gab_champ_param_value` `table_field_label`"
                . " ON `table_field_label`.`id_champ` = `c`.`id` AND `table_field_label`.`code_champ_param` LIKE 'TABLE.FIELD.LABEL'"
                . " WHERE `c`.`type` LIKE 'JOIN'";
        $champs = $this->_db->query($query)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($champs as $champ) {
            if ($champ['type_parent'] == "bloc") {
                $query = "SELECT"
                        . " `g`.`id`,"
                        . " CONCAT(`a`.`name`, '_', `g`.`name`, '_', `b`.`name`),"
                        . " CONCAT(`a`.`name`, '_', `g`.`name`)"
                        . " FROM `gab_bloc` `b`"
                        . " JOIN `gab_gabarit` `g` ON `g`.`id` = `b`.`id_gabarit`"
                        . " JOIN `gab_api` `a` ON `a`.`id` = `g`.`id_api`"
                        . " WHERE `b`.`id` = " . $champ['id_parent'];
                $results = $this->_db->query($query)->fetch(PDO::FETCH_NUM);

                $id_gabarit = $results[0];
                $blocOrigin = $results[1];
                $tableOrigin = $results[2];
            } else {
                $query = "SELECT"
                        . " `g`.`id`,"
                        . " CONCAT(`a`.`name`, '_', `g`.`name`)"
                        . " FROM `gab_gabarit` `g`"
                        . " JOIN `gab_api` `a` ON `a`.`id` = `g`.`id_api`"
                        . " WHERE `g`.`id` = " . $champ['id_parent'];
                $results = $this->_db->query($query)->fetch(PDO::FETCH_NUM);

                $id_gabarit = $results[0];
                $tableOrigin = $results[1];
            }

            $liste = $this->_gabaritManager->getList(BACK_ID_VERSION, FALSE, $id_gabarit);

            foreach ($liste as $ii => $page) {
                $fullPage = $this->_gabaritManager->getPage(BACK_ID_VERSION, $page->getMeta("id"), 0, TRUE);

                $liste[$ii] = $fullPage;
            }

            echo $tableOrigin . '<br />' . $champ['table_name_value']
            . '<pre>' . print_r($liste, true) . '</pre>'
            . '<hr />';
        }
    }

    /**
     * 
     * @return void
     */
    public function listeAction() {
        $this->_javascript->addLibrary("back/liste.js");

        $gabaritsList = array();

        if ($this->_utilisateur->get("niveau") == "solire")
            $query = "SELECT `gab_gabarit`.id, `gab_gabarit`.* FROM `gab_gabarit`";
        else
            $query = "SELECT `gab_gabarit`.id, `gab_gabarit`.* FROM `gab_gabarit`";


        //Si on a un fichier de conf
        $indexConfig = isset($_GET["c"]) && intval($_GET["c"]) ? intval($_GET["c"]) : 0;
        $currentConfigPageModule = $this->_configPageModule[$indexConfig];

        $gabaritsList = $currentConfigPageModule["gabarits"];

        //Si on liste que certains gabarits
        if ($gabaritsList != "*" && count($gabaritsList) > 0) {
            $query .= " WHERE id IN ( " . implode(", ", $gabaritsList) . ")";
            //Permet de séparer les différents gabarits
            if (isset($_GET["gabaritByGroup"])) {
                $this->_view->gabaritByGroup = true;
                foreach ($gabaritsList as $gabariId) {
                    $this->_view->pagesGroup[$gabariId] = $this->_gabaritManager->getList(BACK_ID_VERSION, 0, $gabariId);
                }
            } else {
                $this->_pages = $this->_gabaritManager->getList(BACK_ID_VERSION, 0, $gabaritsList);
                $this->_view->pagesGroup[0] = 1;
            }
        } else {
            $this->_pages = $this->_gabaritManager->getList(BACK_ID_VERSION, 0);
            $this->_view->pagesGroup[0] = 1;
        }



        $this->_gabarits = $this->_db->query($query)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        //Liste des début de label à regrouper pour les boutons de création
        $groupIdentifications = array("Rubrique ", "Sous rubrique ", "Page ");
        foreach ($this->_gabarits as $gabarit) {
            $found = false;

            $gabaritsGroup = array(
                "label" => $gabarit["label"],
            );

            //Si utilisateur standart à le droit de créer ce type de gabarit ou si utilisateur solire
            if ($gabarit["creable"] || $this->_utilisateur->get("niveau") == "solire") {

                //Si on a un regroupement des boutons personnalisés dans le fichier de config
                if (isset($currentConfigPageModule["boutons"]) && isset($currentConfigPageModule["boutons"]["groups"])) {
                    foreach ($currentConfigPageModule["boutons"]["groups"] as $customGroup) {
                        //Si le gabarit courant appartien à un des groupes personnalisés
                        if (in_array($gabarit["id"], $customGroup["gabarits"])) {
                            $gabaritsGroup = array(
                                "label" => $customGroup["label"],
                            );
                            $found = true;
                            break;
                        }
                    }
                }

                //On parcourt les Début de label à regrouper
                if ($found == false) {
                    foreach ($groupIdentifications as $groupIdentification) {
                        if (preg_match("/^$groupIdentification/", $gabarit["label"])) {
                            $gabaritsGroup = array(
                                "label" => $groupIdentification,
                            );
                            $gabarit["label"] = ucfirst(trim(preg_replace("#^" . $groupIdentification . "#", "", $gabarit["label"])));
                            $found = true;
                            break;
                        }
                    }
                }
                $gabaritsGroup["gabarit"][] = $gabarit;
                if (!$found) {
                    $gabaritsGroup["label"] = "";
                    $this->_view->gabaritsBtn[] = $gabaritsGroup;
                } else {

                    if (isset($this->_view->gabaritsBtn[md5($gabaritsGroup["label"])]))
                        $this->_view->gabaritsBtn[md5($gabaritsGroup["label"])]["gabarit"][] = $gabarit;
                    else
                        $this->_view->gabaritsBtn[md5($gabaritsGroup["label"])] = $gabaritsGroup;
                }
            }
        }


        $this->_view->gabarits = $this->_db->query($query)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        $this->_view->pages = $this->_pages;

        $this->_view->breadCrumbs[] = array(
            "label" => $currentConfigPageModule["label"],
            "url" => "page/liste.html",
        );
    }

    /**
     * 
     * @return void
     */
    public function childrenAction() {
        if ($this->_utilisateur->get("niveau") == "solire")
            $query = "SELECT `gab_gabarit`.id, `gab_gabarit`.* FROM `gab_gabarit`";
        else
            $query = "SELECT `gab_gabarit`.id, `gab_gabarit`.* FROM `gab_gabarit`";

        $this->_gabarits = $this->_db->query($query)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        $this->_view->gabarits = $this->_gabarits;

        $this->_view->main(FALSE);
        $this->_pages = $this->_gabaritManager->getList(BACK_ID_VERSION, $_REQUEST['id_parent']);
        if (count($this->_pages) == 0)
            exit();
        $this->_view->pages = $this->_pages;

        $query = "SELECT `gab_gabarit`.id, `gab_gabarit`.* FROM `gab_gabarit`";

        $this->_gabarits = $this->_db->query($query)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        $this->_view->gabarits = $this->_gabarits;
    }

    /**
     * 
     * @return void
     */
    public function displayAction() {
        $upload_path = $this->_mainConfig->get("path", "upload");

        $id_gab_page = isset($_GET['id_gab_page']) ? $_GET['id_gab_page'] : 0;
        $id_gabarit = isset($_GET['id_gabarit']) ? $_GET['id_gabarit'] : 1;

        $this->_view->action = "liste";

        $this->_javascript->addLibrary("back/tiny_mce/tiny_mce.js");
        $this->_javascript->addLibrary("back/jquery/jquery.livequery.min.js");
        $this->_javascript->addLibrary("back/autocomplete.js");
        $this->_javascript->addLibrary("back/plupload/plupload.full.min.js");
        $this->_javascript->addLibrary("back/formgabarit.js");
        $this->_javascript->addLibrary("back/jquery/jquery.tipsy.js");
        $this->_javascript->addLibrary("back/jquery/jquery.qtip.min.js");
        $this->_javascript->addLibrary("back/affichegabarit.js");
        $this->_javascript->addLibrary("back/join-simple.js");
        $this->_javascript->addLibrary("back/jquery/jquery.autogrow.js");
        $this->_javascript->addLibrary("back/jquery/jquery.dataTables.min.js");
        $this->_css->addLibrary("back/demo_table_jui.css");


        $this->_javascript->addLibrary("back/autocomplete_multi/jquery.tokeninput.js");
        $this->_javascript->addLibrary("back/autocomplete_multi.js");

        $this->_css->addLibrary("back/tipsy.css");
        $this->_css->addLibrary("back/jquery.qtip.min.css");
        $this->_css->addLibrary("back/autocomplete_multi/token-input.css");
        $this->_css->addLibrary("back/autocomplete_multi/token-input-facebook.css");

        $this->_form = '';

        if ($id_gab_page) {
            $versions = $this->_db->query("SELECT * FROM `version`")->fetchAll(PDO::FETCH_ASSOC);

            $form = '';
            $devant = '';
            foreach ($versions as $version) {
                $page = $this->_gabaritManager->getPage($version['id'], $id_gab_page);

                $urlParent = "";
                foreach ($this->_gabaritManager->getParents($page->getMeta("id_parent"), $page->getMeta("id_version")) as $parent) {
                    $urlParent .= $parent->getMeta("rewriting") . "/";
                }

                $url = $urlParent . $page->getMeta("rewriting") . $page->getGabarit()->getExtension();

                $redirections = $this->_db->query("
                    SELECT * 
                    FROM `redirection` 
                    WHERE  new = " . $this->_db->quote($url) . "
                        AND id_version = " . $version['id'] . "
                ")->fetchAll(PDO::FETCH_COLUMN);

                $devant .= '<div style="height: 54px;float: left;">'
                        . '<div class="btn gradient-blue" style="margin-bottom: 5px;display:block;"><a title="' . $version['nom'] . '" class="openlang' . ($version['id'] == BACK_ID_VERSION ? ' active' : ' translucide') . '">Langue : <img src="img/flags/png/' . strtolower($version['suf']) . '.png" alt="'
                        . $version['nom'] . '" /></a></div>';

                if ($page->getMeta("rewriting") != "") {
                    if ($page->getGabarit()->getMake_hidden()
                            || $this->_utilisateur->get("niveau") == "solire"
                            || !$page->getMeta("visible")
                    ) {
                        $devant .= '<div style="margin-left: 6px;margin-top: -7px;"><label style="color:#A1A1A1;text-shadow:none;margin-left:10px;" for="visible-'
                                . $version['id'] . '">Visible : </label><input class="visible-lang" value="'
                                . $page->getMeta("id") . '|' . $version['id'] . '" id="visible-' . $version['id'] . '" style="" '
                                . ($page->getMeta("visible") ? 'checked="checked"' : '') . ' type="checkbox" /></div>';
                    }
                } else {
                    $devant .= '<span class="notification gradient-red" style="margin-left: 6px;">Non traduit</span>';
                }

                $devant .= '</a></div>';


                $form .= '<div class="langue" style="clear:both;' . ($version['id'] == BACK_ID_VERSION ? '' : ' display:none;')
                        . '"><div class="clearin"></div>'
                        . $page->getForm("page/save.html", "page/liste.html", $upload_path, FALSE, $page->getGabarit()->getMeta(), $page->getGabarit()->get301_editable(), $page->getGabarit()->getMeta_titre(), $page->getGabarit()->getExtension(), $version["id"], $redirections)
                        . '</div>';
            }

            $this->_page = $this->_gabaritManager->getPage(BACK_ID_VERSION, $id_gab_page);

            $this->_form .= '<div>' . $devant . '</div>' . $form;
        } else {
            $this->_page = $this->_gabaritManager->getPage(BACK_ID_VERSION, 0, $id_gabarit);

            $form = $this->_page->getForm("page/save.html", "page/liste.html", $upload_path, FALSE, $this->_page->getGabarit()->getMeta(), $this->_page->getGabarit()->get301_editable(), $this->_page->getGabarit()->getMeta_titre(), $this->_page->getGabarit()->getExtension(), 1);
            $this->_form = $form;
        }



        //on recupere la sous rubrique de page a laquelle il appartient pour le breadCrumbs et le lien retour
        $found = false;
        foreach ($this->_configPageModule as $index => $currentConfigPageModule) {
            //Si le gabarit courant appartien à un des groupes personnalisés
            if ($currentConfigPageModule["gabarits"] == "*" || in_array($this->_page->getGabarit()->getId(), $currentConfigPageModule["gabarits"])) {
                $indexPageList = $index;
                $found = true;
                break;
            }

            if ($found) {
                break;
            }
        }



        $this->_view->page = $this->_page;
        $this->_view->form = $this->_form;

        if ($found) {
            $this->_view->breadCrumbs[] = array(
                "label" => $this->_configPageModule[$indexPageList]["label"],
                "url" => "page/liste.html?c=" . $indexPageList,
            );
        } else {
            $this->_view->breadCrumbs[] = array(
                "label" => "Liste des pages",
                "url" => "page/liste.html",
            );
        }

        $this->_view->breadCrumbs[] = array(
            "label" => "Gestion des pages",
            "url" => "",
        );
    }

    /**
     * 
     * @return void
     */
    public function saveAction() {
        $this->_view->main(FALSE);
        $this->_view->enable(FALSE);

        $this->_page = $this->_gabaritManager->save($_POST);

        $contenu = '<a href="' . Registry::get("basehref") . 'page/display.html?id_gab_page='
                . $this->_page->getMeta("id") . '">'
                . $this->_page->getMeta("titre") . '</a>';

        $headers = "From: " . Registry::get("mail-contact") . "\r\n"
                . "Reply-To: " . Registry::get("mail-contact") . "\r\n"
                . "Bcc: contact@solire.fr \r\n"
                . "X-Mailer: PHP/" . phpversion();

        Tools::mail_utf8("Modif site <modif@solire.fr>", "Modification de contenu sur " . Registry::get("site"), $contenu, $headers);

        $json = array(
            "status" => $this->_page ? "success" : "error",
            "search" => "?id_gab_page=" . $this->_page->getMeta("id"),
            "id_gab_page" => $this->_page->getMeta("id"),
        );

        exit(json_encode($json));
    }

    /**
     * 
     * @return void
     */
    public function autocompleteAction() {
        $this->_view->enable(FALSE);
        $this->_view->main(FALSE);

        $json = array();
        $dejaLiees = is_array($_REQUEST['deja']) ? $_REQUEST['deja'] : array();

        if (!isset($_REQUEST['id_gabarit']) || !is_numeric($_REQUEST['id_gabarit']))
            exit(json_encode($json));

        $pages = $this->_gabaritManager->getSearch(BACK_ID_VERSION, $_GET['term'], $_REQUEST['id_gabarit']);
        foreach ($pages as $page) {
            if (!in_array($page->getMeta('id'), $dejaLiees))
                $json[] = array("value" => $page->getMeta('id'), "label" => $page->getMeta('titre'), "visible" => $page->getMeta('titre'));
        }

        exit(json_encode($json));
    }

    /**
     * 
     * @return void
     */
    public function autocompleteJoinAction() {
        $this->_view->enable(FALSE);
        $this->_view->main(FALSE);

        $json = array();
        $term = $_REQUEST["term"];
        $idField = $_REQUEST["id_field"];
        $idGabPage = $_REQUEST["id_gab_page"];
        $queryFilter = str_replace("[ID]", $idGabPage, $_REQUEST["query_filter"]);
        $table = $_REQUEST["table"];
        $labelField = "";
        $lang = $_REQUEST["id_version"];
        $gabPageJoin = "";


        $filterVersion = "`$table`.id_version = $lang";
        if (isset($_REQUEST["no_version"]) && $_REQUEST["no_version"] == 1) {
            $filterVersion = 1;
        } else {
            $gabPageJoin = "INNER JOIN gab_page ON visible = 1 AND suppr = 0 AND gab_page.id = `$table`.$idField " . ($filterVersion != 1 ? "AND gab_page.id_version = $lang" : "");
        }



        if (substr($_REQUEST["label_field"], 0, 9) == "gab_page.") {
            $labelField = $_REQUEST["label_field"];
        } else {
            $labelField = "`$table`.`" . $_REQUEST["label_field"] . "`";
        }

        $sql = "SELECT `$table`.$idField id, $labelField label"
                . " FROM `$table`"
                . " $gabPageJoin"
                . " WHERE $filterVersion "
                . ($queryFilter != "" ? "AND (" . $queryFilter . ")" : "")
                . " AND $labelField  LIKE '%$term%'";

        $json = $this->_db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        exit(json_encode($json));
    }

    /**
     * 
     * @return void
     */
    public function liveSearchAction() {
        $this->_view->enable(FALSE);
        $this->_view->main(FALSE);

        $pages = array();


        $qSearch = isset($_GET["term"]) ? $_GET["term"] : "";

        /*
         * Traitement de la chaine de recherche
         */

        $searchTab = array();

        //Variable qui contient la chaine de recherche
        $stringSearch = strip_tags(trim($qSearch));
        $this->filter->stringSearch = $stringSearch;

        //Si un seul mot
        if (strpos($stringSearch, " ") === false) {
            $searchTab[0] = $stringSearch;
        } else {
            //Si plusieurs  mots on recupere un tableau de mots
            $searchTab = preg_split("#[ ]+#", $stringSearch);
        }

        //Tableau de mot(s)
        $this->filter->words = $searchTab;

        //On teste si un mot est supérieurs à 3 caractères
        $this->filter->errors["len_words"] = true;
        for ($i = 0, $I = count($this->filter->words); $i < $I; $i++) {
            if (trim($this->filter->words[$i]) != "" && strlen(trim($this->filter->words[$i])) >= 2) {
                $this->filter->errors["len_words"] = false;
            }
        }

        if ($this->filter->errors["len_words"]) {
            echo json_encode(null);
            return;
        }

        //Pour chaque mot ou essaie de mettre au singulier ou pluriel
        // + Traitement de la chaine de recherche (elimine mot trop court
        $mode[] = "s";
        $mode[] = "p";
        $i = 0;
        foreach ($this->filter->words as $t1) {
            foreach ($mode as $m) {
                if (strlen($t1) >= 2) {
                    $this->filter->wordsAdvanced[$i++] = (($m == "s") ? $this->singulier($t1) : $this->pluriel($t1));
                }
            }
        }

        //Tri des mots par strlen
        if (is_array($this->filter->wordsAdvanced))
            usort($this->filter->wordsAdvanced, array($this, "length_cmp"));

        if ($qSearch != null) {
            $filterWords[] = 'CONCAT(" ", gab_page.titre, " ") LIKE ' . $this->_db->quote("%" . $this->filter->stringSearch . "%");

            if (isset($this->filter->wordsAdvanced) && is_array($this->filter->wordsAdvanced) && count($this->filter->wordsAdvanced) > 0)
                foreach ($this->filter->wordsAdvanced as $word) {
                    $filterWords[] = 'CONCAT(" ", gab_page.titre, " ") LIKE ' . $this->_db->quote("%" . $word . "%");
                }

            foreach ($filterWords as $filterWord) {
                $orderBy[] = "IF($filterWord , 0, 1)";
            }
        }


        $sql = "SELECT `gab_page`.`id` id, gab_page.titre label, gab_page.titre visible, gab_gabarit.label gabarit_label,  CONCAT('page/display.html?id_gab_page=', `gab_page`.`id`) url"
                . " FROM `gab_page`"
                . " LEFT JOIN `gab_gabarit`"
                . "     ON `gab_page`.id_gabarit = `gab_gabarit`.id"
                . "     AND `gab_gabarit`.editable = 1"
                . " WHERE `gab_page`.`id_version` = " . BACK_ID_VERSION
                . " AND `gab_page`.`suppr` = 0 "
                . (isset($filterWords) ? " AND (" . implode(" OR ", $filterWords) . ")" : '')
                . " ORDER BY " . implode(",", $orderBy) . " LIMIT 10";

        $pagesFound = $this->_db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pagesFound as $page) {
            $pages[] = array(
                "label" => Tools::highlightedSearch($page["label"], $this->filter->wordsAdvanced, true),
                "id" => $page["id"],
                "gabarit_label" => $page["gabarit_label"],
                "url" => $page["url"],
            );
        }


        exit(json_encode($pages));
    }

    /**
     * 
     * @return void
     */
    public function autocompleteLinkAction() {
        header("content-type: application/x-javascript; charset=UTF-8");
        $json = file_get_contents($this->_url . "../sitemap.xml?visible=0&json=1&onlylink=1");
        exit("var tinyMCELinkList = " . $json . ";");
    }

    /**
     * 
     * @return void
     */
    public function visibleAction() {
        $this->_view->enable(FALSE);

        $json = array('status' => "error");

        $idVersion = BACK_ID_VERSION;

        if (isset($_POST["id_version"]) && $_POST["id_version"] > 0) {
            $idVersion = intval($_POST["id_version"]);
        }

        if (is_numeric($_POST['id_gab_page']) && is_numeric($_POST['visible'])) {
            $query = "UPDATE `gab_page` SET `visible` = " . $_POST['visible'] . " WHERE id_version =  " . $idVersion . " AND `id` = " . $_POST['id_gab_page'];
            if ($this->_db->query($query)) {
                $json['status'] = "success";
                $json['debug'] = $query;
            }
        }


        exit(json_encode($json));
    }

    /**
     * 
     * @return void
     */
    public function deleteAction() {
        $this->_view->enable(FALSE);

        $json = array('status' => "error");

        if (is_numeric($_POST['id_gab_page'])) {
            $query = "UPDATE `gab_page` SET `suppr` = 1, `date_modif` = NOW() WHERE `id` = " . $_POST['id_gab_page'];
            $json['query'] = $query;
            if ($this->_db->exec($query)) {
                $json['status'] = "success";
            }
        }

        exit(json_encode($json));
    }

    /**
     * 
     * @return void
     */
    public function orderAction() {
        $ok = true;

        $this->_view->main(FALSE);
        $this->_view->enable(FALSE);

        $prepStmt = $this->_db->prepare("UPDATE `gab_page` SET `ordre` = :ordre WHERE `id` = :id");
        foreach ($_POST['positions'] as $id => $ordre) {
            $prepStmt->bindValue(":ordre", $ordre, PDO::PARAM_INT);
            $prepStmt->bindValue(":id", $id, PDO::PARAM_INT);
            $tmp = $prepStmt->execute();
            if ($ok)
                $ok = $tmp;
        }

        echo $ok ? 'Succès' : 'Echec';

        return FALSE;
    }

    protected function singulier($mot) {
        return (substr($mot, -1) == "s") ? substr($mot, 0, -1) : $mot;
    }

    protected function pluriel($mot) {
        return (substr($mot, -1) == "s") ? $mot : ($mot . 's');
    }

    protected function length_cmp($a, $b) {
        return strlen($b) - strlen($a);
    }

}

//end class
