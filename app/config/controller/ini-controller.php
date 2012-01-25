<?php

require_once 'main-controller.php';
require_once 'ini.php';


class IniController extends MainController
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

    }//end start()


    /**
     * ACOMMENTER.
     *
     * @return void
     */
    public function editAction()
    {
        
        $config = array();
        $name = $_REQUEST['name'];
        $path = $_SESSION['project']['chemin'] . "config/$name.ini";
        Ini::read_ini_file($path, $config);
        
        
        if(isset($_POST['ini-save'])) {
            foreach ($_POST as $key => $value) {
                if($key == 'ini-save')
                    continue;
                list($sectionKey, $keyConf) = explode('----', $key);
                $config[$sectionKey][$keyConf] = $value;
                
            }
            Ini::write_ini_file($path, $config);
        }
        
        
        
        $fieldsets = array();
        
        Ini::read_ini_file($path, $config);
        foreach ($config as $keySection => $section) {
            if(count($section) > 0 && $keySection != "") {
                $myFieldset = array();
                $myFieldset['legend'] = $keySection;
                foreach ($section as $key => $value) {
                    if(substr($key, 0, 8) == 'Newline_' || substr($key, 0, 8) == 'Comment_')
                            continue;
                    $myFieldset['fields'][] = array(
                        'value' => $value,
                        'name'  => $keySection . '----' . $key,
                    );
                }
                $fieldsets[] = $myFieldset;
            }
            
        }
        
        $this->_view->fieldsets = $fieldsets;
        
        
    }//end editAction()



}//end class

?>