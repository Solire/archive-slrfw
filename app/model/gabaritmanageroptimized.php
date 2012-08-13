<?php

/**
 * Description of gabaritManagerOptimized
 *
 * @author Stephane
 */
class gabaritManagerOptimized extends gabaritManager {

    protected $_versions = array();

    /**
     * Récupère les informations de la version selon son id
     *  Avec mise en cache
     * 
     * @param int $id_version
     * @return array 
     */
    public function getVersion($id_version) {
        if (!isset($this->_versions[$id_version])) {
            $this->_versions[$id_version] = parent::getVersion($id_version);
        }
        return $this->_versions[$id_version];
    }

}
