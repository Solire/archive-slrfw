<?php

require_once 'main-controller.php';
require_once 'ini.php';

class GabaritController extends MainController
{

    private $_cache = null;

    /**
     * Toujours executé avant l'action.
     *
     * @return void
     */
    public function start()
    {
        parent::start();
        $this->_cache = Registry::get('cache');
    }

//end start()

    /**
     * ACOMMENTER.
     *
     * @return void
     */
    public function startAction()
    {

        $this->_javascript->addLibrary("jquery/plugins/jquery.formtotable.js");
        $this->_javascript->addLibrary("jquery/plugins/jquery.selectload.js");


        // On récupère la liste des paramètres des champs
        $this->_view->gabChampType = $this->_dbProject->query('SELECT * FROM gab_champ_type;')->fetchAll();
        $this->_view->gabChampTypeDonnee = $this->_dbProject->query('SELECT * FROM gab_champ_typedonnee;')->fetchAll();
        $this->_view->gabChampTypeSql = array(
            'varchar(255) NOT NULL',
            'text NOT NULL',
            'date NOT NULL',
            'INT( 11 ) NOT NULL',
            'TINYINT( 1 ) NOT NULL',
        );
        $this->_view->gabChampMedia = array(
            '',
            'web',
            'mobile',
        );
    }

//end startAction()

    /**
     * ACOMMENTER.
     *
     * @return void
     */
    public function loadAction()
    {
        $this->_view->enable(false);
        $response = array();
        $response['gabarits'] = array();
        $response['fieldGroups'] = array();
        $response['fields'] = array();

        $response['gabarits']['data'] = $this->_dbProject->query('SELECT *  FROM gab_gabarit ORDER BY  id_parent, ordre;')->fetchAll();
        $response['fieldGroups']['data'] = $this->_dbProject->query('
            (SELECT id, id_gabarit, label, name, ordre, "group" type   FROM gab_champ_group)
            UNION
            (SELECT id, id_gabarit, label, name, ordre, "bloc" type  FROM gab_bloc)
            ORDER BY id_gabarit, ordre;')->fetchAll();
        $response['fields']['data'] = $this->_dbProject->query('SELECT *  FROM gab_champ ORDER BY  id_group, ordre;')->fetchAll();

        echo json_encode($response);
    }

    public function selectLoadAction()
    {
        $this->_view->enable(false);
        $load = $_REQUEST['load'];
        $response = array();
        switch ($load) {
            case 'gabarit' :
                $gabs = $this->_dbProject->query('SELECT *  FROM gab_gabarit ORDER BY IF(id_parent = 0, id, id_parent), id_parent, ordre;')->fetchAll();
                $this->single_genealogy($response, 0);
                
                break;
            default :
                break;
        }
        echo json_encode($response);
    }

    

    public function deleteAction()
    {
        $this->_view->enable(false);
        $table = $_POST['table'];

        if ($table == 'gab_bloc')
            $_POST['type'] = "bloc";
        if ($table == 'gab_champ_group')
            $_POST['type'] = "group";

        if (isset($_POST["id"])) {
            $response["status"] = $this->_dbProject->delete($table, 'id=' . $_POST["id"]);
        }
        unset($_POST['table']);

        echo json_encode($_POST);
    }

    public function processSortableAction()
    {
        $this->_view->enable(false);
        switch ($_GET['table']) {
            case 'gab_gabarit':
                foreach ($_GET['gabarits'] as $position => $item) :
                    $sql[] = "UPDATE `gab_gabarit` SET `ordre` = $position WHERE `id` = $item";
                endforeach;

                break;
            case 'gab_champ':
                $id_parent = $_GET['id_parent'];
                $id_group = $_GET['id_group'];
                $type_parent = $_GET['type_parent'];
                if ($type_parent == "bloc") {
                    $id_group = 0;
                    $id_parent = $_GET['id_group'];
                } else {
                    $type_parent = 'gabarit';
                }

                if (isset($_GET['fields']))
                    foreach ($_GET['fields'] as $position => $item) :
                        $sql[] = "UPDATE `gab_champ` SET `ordre` = $position, type_parent='$type_parent', `id_group` = $id_group, `id_parent` = $id_parent WHERE `id` = $item";
                    endforeach;

                break;
            case 'gab_champ_group':
                $id_gabarit = $_GET['id_gabarit'];
                if (isset($_GET['group']))
                    foreach ($_GET['group'] as $position => $item) :
                        $sql[] = "UPDATE `gab_champ_group` SET `ordre` = $position, `id_gabarit` = $id_gabarit  WHERE `id` = $item";
                    endforeach;
                if (isset($_GET['bloc']))
                    foreach ($_GET['bloc'] as $position => $item) :
                        $sql[] = "UPDATE `gab_bloc` SET `ordre` = $position, `id_gabarit` = $id_gabarit  WHERE `id` = $item";
                    endforeach;

                break;

            default:
                break;
        }
        foreach ($sql as $query) :
            $this->_dbProject->exec($query);
        endforeach;
    }

    function single_genealogy(&$response, $category, $level = 0)
    {   
        $q = "SELECT gc.id, gc.name, gc.id_parent
                FROM gab_gabarit gc
                JOIN gab_gabarit gp
                USING ( id )
                WHERE gp.id_parent =" . $category;
        $r = $this->_dbProject->query($q)->fetchAll();

        $level++;
        $prefixe = "&nbsp;&nbsp;";
        if(count($r) > 0)
        foreach ($r  as $key => $value) {
                    $response[$value['id']]['name'] = str_repeat($prefixe, $level ). '>&nbsp;' . $value["name"];
        $this->single_genealogy($response, $value['id'], $level);
        }

        
    }

}

//end class
?>