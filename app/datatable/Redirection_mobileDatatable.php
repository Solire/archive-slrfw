<?php

require_once 'datatable/datatable.php';

/**
 * Description of BoardDatatable
 *
 * @author shin
 */
class Redirection_mobileDatatable extends Datatable {
    
    public function start() {
        parent::start();
        $suf = $this->_db->query("SELECT suf FROM version WHERE id = " . BACK_ID_VERSION)->fetchColumn();
        $this->config["table"]["title"] .= ' <img src="img/flags/all/16/' . strtolower($suf) . '.png" />';
    }

}

?>
