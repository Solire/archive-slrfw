<?php
/**
 * Description of gabaritmanager
 *
 * @author thomas
 */
class gabaritManager extends manager {    
    /**
     * Donne l'identifiant d'une page d'après son rewriting et l'identifiant
     * @param string $rewriting
     * @param type $id_parent
     * @param type $id_version
     * @return type 
     */
    public function getIdByRewriting($rewriting, $id_parent = 0, $id_version = ID_VERSION) {
		$query = "SELECT `id` FROM `gab_page`"
               . " WHERE `suppr` = 0 AND `visible` = 1 AND `id_parent` = $id_parent"
               . " AND `id_version` = $id_version AND `rewriting` = " . $this->_db->quote($rewriting);
        return $this->_db->query($query)->fetchColumn();
    }
    
//    /**
//     *
//     * @param type $id
//     * @param type $id_version
//     * @return type 
//     */
//    public function getMeta($id, $id_version = ID_VERSION) {
//        $query = "SELECT * FROM `gab_page` WHERE `id_version` = $version AND `id` = $id AND `suppr` = 0";
//        $meta = $this->_db->query($query)->fetch(PDO::FETCH_ASSOC);
//        
//        return $meta;
//    }
    
    /**
     * <p>Retourne un objet page à partir de l'identifiant de la page <br />
     * ou un objet page vide à partir de l'idenfiant du gabarit</p>
     * @param type $id
     * @param type $id_gabarit
     * @param type $version 
     */
    public function getPage($id, $id_gabarit = 0, $id_version = ID_VERSION) {   
        $page = new page();
                
        if ($id) {
            $query = "SELECT * FROM `gab_page` WHERE `id_version` = $id_version AND `id` = $id AND `suppr` = 0";
            $meta = $this->_db->query($query)->fetch(PDO::FETCH_ASSOC);
            
            if (!$meta)
                return FALSE;
            
            $page->setMeta($meta);
            $id_gabarit = $meta['id_gabarit'];            
        }
        
        $gabarit = $this->getGabarit($id_gabarit);
        $page->setGabarit($gabarit);
        
        $blocs = $this->getBlocs($gabarit, $id);
        $page->setBlocs($blocs);
        
        if ($id) {
            $values = $this->getValues($page);
            
            if ($values) {
                $page->setValues($values);
                
                $blocs = $page->getBlocs();
                foreach ($blocs as $bloc) {                    
                    $valuesBloc = $this->getBlocValues($bloc, $values['id'], $id_version);
                    if ($valuesBloc)
                        $bloc->setValues($valuesBloc);
                }
            }
        }
        
        return $page;
    }
    
	/**
	 * <p>Retourne un objet gabarit à partir de l'identifiant du gabarit</p>
	 * @param int $id_gabarit
	 * @return gabarit 
	 */
	public function getGabarit($id_gabarit) {
		$query = "SELECT * FROM `gab_gabarit` WHERE `id` = $id_gabarit";
		$row = $this->_db->query($query)->fetch(PDO::FETCH_ASSOC);
		
		$gabarit = new gabarit($row['id'], $row['id_parent'], $row['name'], $row['label']);
		if ($row['id_api'] > 0) {
			$query = "SELECT `name` FROM `gab_api` WHERE `id` = " . $row['id_api'];
			$api = $this->_db->query($query)->fetchColumn();
            $table = $api . "_" . $row['name'];
		}
        else {
            $table = $row['name'];
        }
        $gabarit->setTable($table);
		        
		$query = "SELECT * FROM `gab_champ` WHERE `id_parent` = $id_gabarit AND `type_parent` = 'gabarit'";
        $champs = $this->_db->query($query)->fetchAll(PDO::FETCH_ASSOC);
		$gabarit->setChamps($champs);
        
		return $gabarit;
	}
    
    /**
     *
     * @param gabarit $gabarit 
     * @return array
     */
    public function getBlocs($gabarit, $id_gab_page = 0) {
        $query = "SELECT * FROM `gab_bloc` WHERE `id_gabarit` = " . $gabarit->getId();
        $rows = $this->_db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
		$query = "SELECT * FROM `gab_champ` WHERE `id_parent` = :id_bloc AND `type_parent` = 'bloc'";
        $stmt = $this->_db->prepare($query);
                
        $blocs = array();
        foreach ($rows as $row) {
            $gabarit_bloc = new gabarit($row['id'], 0, $row['name'], $row['label']);
            
            $table = $gabarit->getTable() . "_" . $row['name'];
            $gabarit_bloc->setTable($table);
            
            $stmt->bindValue(":id_bloc", $row['id'], PDO::PARAM_INT);
            $stmt->execute();
            $champs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            $gabarit_bloc->setChamps($champs);
            
            $bloc = new bloc();
            if ($id_gab_page > 0)
                $bloc->setId ($id_gab_page);
                
            $bloc->setGabarit($gabarit_bloc);
            
            $blocs[$gabarit_bloc->getName()] = $bloc;
        }
        
        return $blocs;
    }
    
    /**
     * Retourne la ligne des infos de la table générée à partir d'une page.
     * @param page $page 
     * @return array
     */
    public function getValues($page) {
        $query = "SELECT * FROM `" . $page->getGabarit()->getTable()
               . "` WHERE `id_parent` = " . $page->getMeta("id")
               . " AND `id_version` = " . $page->getMeta("id_version");
        
        return $this->_db->query($query)->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * <p>Retourne les lignes des infos de la table générée à partir d'un bloc<br />
     * et de la page parente.</p>
     * @param gabarit $bloc
     * @param int $id_parent identifiant de la page parente.
     * @param int $id_version identifiant de la version.
     * @param bool $visible <p>si faux on récupère les blocs visibles ou non,<br />
     * si vrai on récupère uniquement les blocs visibles.</p>
     * @return type 
     */
    public function getBlocValues($bloc, $id_parent, $id_version = ID_VERSION, $visible = FALSE) {
        $query = "SELECT * FROM `" . $bloc->getGabarit()->getTable() . "` WHERE `id_parent` = " . $id_parent . " AND `suppr` = 0"
               . " AND `id_version` = $id_version"
               . ($visible ? " AND `visible` = 1" : "")
               . " ORDER BY `ordre`";
//        echo $query;
        return $this->_db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
        
    /**
     * <p>Retourne les parents, grand-parents, aïeuls etc.<br />
     * dans un tableau associatif `nom du gabarit` => `objet page correspondant`</p>
     * @param int $id_gab_page_parent
     * @param int $id_version
     * @return array 
     */
    public function getParents($id_gab_page_parent, $id_version = ID_VERSION) {
        $parents = array();
        
        while ($id_gab_page_parent) {
            $query = "SELECT * FROM `gab_page` WHERE `id_version` = $id_version AND `id` = $id_gab_page_parent AND `suppr` = 0";
            $parentMeta = $this->_db->query($query)->fetch(PDO::FETCH_ASSOC);

            $parentPage = new page($parentMeta);
            $gabarit = $this->getGabarit($parentMeta['id_gabarit']);
            $parents[$gabarit->getName()] = $parentPage;        

            $id_gab_page_parent = $parentMeta['id_parent'];
        }
        
        return $parents;
    }
    
    /**
     * <p>Retourne un tableau de page a partir de l'identifiant d'un parent.</p>
     * <p>On peut préciser l'identifiant du gabarit.</p>
     * @param int $id_parent
     * @param int $id_gabarit
     * @param bool $visible
     * @param int $id_version
     * @return array 
     */
	public function getList($id_parent = 0, $id_gabarit = 0, $visible = FALSE, $orderby = "ordre", $sens = "ASC",$debut = 0, $nbre = 0, $id_version = ID_VERSION) {
		$query = "SELECT `p`.*, COUNT(`e`.`id`) `nbre_enfants`"
               . " FROM `gab_page` `p` LEFT JOIN `gab_page` `e` ON `e`.`id_parent` = `p`.`id` AND `e`.`suppr` = 0 AND `e`.`id_version` = $id_version"
               . ($visible ? " AND `e`.`visible` = 1" : "")
               . " WHERE `p`.`suppr` = 0 AND `p`.`id_version` = $id_version"
               . ($visible ? " AND `p`.`visible` = 1" : "");
        
        if ($id_parent) $query .= " AND `p`.`id_parent` = $id_parent";//`p`.`id_parent` = $id_parent AND 
        if ($id_gabarit) $query .= " AND `p`.`id_gabarit` = $id_gabarit";
        
        $query .= " GROUP BY `p`.`id`";
        
        $query .= " ORDER BY `p`.`$orderby` $sens";
        
        if ($nbre) $query .= " LIMIT $debut, $nbre";

        $metas = $this->_db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        $pages = array();
        foreach ($metas as $meta)
            $pages[] = new page($meta);
        
        return $pages;
	}
        
    /**
     * <p>identique à getList</p>
     * @param string $term
     * @param int $id_gabarit
     * @param int $id_parent
     * @param bool $visible
     * @param int $id_version
     * @return array
     * @see gabaritManager::getList
     */
    public function getSearch($term, $id_gabarit = 0, $id_parent = FALSE, $visible = FALSE, $id_version = ID_VERSION) {
		$query = "SELECT * FROM `gab_page` WHERE `suppr` = 0 AND `id_version` = "
               . $id_version . " AND `titre` LIKE " . $this->_db->quote("%$term%");
        
        if ($id_gabarit)
            $query .= " AND `id_gabarit` = $id_gabarit";
        
        if ($visible)
            $query .= " AND `visible` = 1";
        
        if ($id_parent != FALSE)
            $query .= " AND `id_parent` = $id_parent";
        
		$metas = $this->_db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        $pages = array();
        foreach ($metas as $meta)
            $pages[] = new page($meta);
        
        return $pages;
    }	

    /**
     * <p>Permet de récupère le premier enfant
     * (exemple : pour les rubriques qui n'ont pas de `view`)</p>
     * @param int $id_parent
     * @param int $id_version
     * @return array 
     */
	public function getFirstChild($id_parent = 0, $id_version = ID_VERSION) {
		$query = "SELECT *"
               . " FROM `gab_page`"
               . " WHERE `id_parent` = $id_parent AND `suppr` = 0 AND `id_version` = $id_version"
               . " AND `visible` = 1"
               . " ORDER BY `ordre`"
               . " LIMIT 0, 1";
		$meta = $this->_db->query($query)->fetch(PDO::FETCH_ASSOC);
        if ($meta) {
            $page = new page($meta);
            return $page;
        }
        
        return NULL;
	}
    
    
    
    
    
	/**
	 *
	 * @param type $id_gabarit 
	 */
	public function	save($post) { 
		$this->_post = $post;
		$this->_versions = $this->_db->query("SELECT `id` FROM `version`")->fetchAll(PDO::FETCH_COLUMN);
		
		$this->_upd = ($this->_post['id_gab_page'] > 0);

        if ($this->_upd)
			$page = $this->getPage($this->_post['id_gab_page'], 0, $this->_post['id_version']);
		else
			$page = $this->getPage(0, $this->_post['id_gabarit'], 1);
		    
        $id_gab_page = $this->_saveMeta($page);
        
        $page = $this->getPage($id_gab_page, 0, 1);
        
        $id_parent = $this->_savePage($page);
        
        $blocs = $page->getBlocs();
        foreach ($blocs as $bloc) {
            $this->_saveBloc($bloc, $id_parent);
        }
        
		$page = $this->getPage($page->getMeta("id"), 0, $this->_post['id_version'] ? $this->_post['id_version'] : 1);
        return $page;
        
//        return TRUE;
	}

    /**
	 *
	 * @param page $page
	 * @param type $id_page
	 * @return type 
	 */
	private function _saveMeta($page) {        
		// Insertion dans la table `gab_page`.
		if ($this->_upd) {
            $rewriting = $this->_db->rewrit($this->_post['titre'], 'gab_page', 'rewriting', "AND `suppr` = 0 AND `id_parent` = " . $page->getMeta("id_parent") . " AND `id_version` = " . $page->getMeta("id_version") . " AND `id` != " . $page->getMeta("id"));
//            if ($page->getMeta("rewriting") == "") $rewriting = "accueil";
            
            $query = "UPDATE `gab_page` SET"
                   . " `titre`			= " . $this->_db->quote($this->_post['titre']) . ","
                   . " `bal_title`		= " . $this->_db->quote($this->_post['bal_title'] ? $this->_post['bal_title'] : $this->_post['titre']) . ","
                   . " `bal_key`		= " . $this->_db->quote($this->_post['bal_key']) . ","
                   . " `bal_descr`		= " . $this->_db->quote($this->_post['bal_descr']) . ","
                   . " `importance`	= " . $this->_post['importance'] . ","
                   . " `date_modif`	= NOW(),"
                   . " `no_index`		= " . (isset($this->_post['no_index']) && $page->getMeta("id") != 1 ? $this->_post['no_index'] : 0)
                   . ($page->getMeta("rewriting") == "" ? ", `rewriting`		= " . $this->_db->quote($rewriting) : "")
                   . " WHERE `id` = " .  $page->getMeta("id")
                   . " AND `id_version` = " .  $page->getMeta("id_version");
                        
			if (!$this->_db->query($query))
                return FALSE;
            
            return $page->getMeta("id");
		}			
		else {
			$id_parent = isset($this->_post['id_parent']) ? $this->_post['id_parent'] : 0;
			
			$rewriting = $this->_db->rewrit($this->_post['titre'], 'gab_page', 'rewriting', "AND `suppr` = 0 AND `id_parent` = $id_parent AND `id_version` = 1");
			
			$ordre = $this->_db->query("SELECT MAX(`ordre`) FROM `gab_page` WHERE id_parent = $id_parent")->fetchColumn();
			$ordre = $ordre ? $ordre + 1 : 1;
			
			$id_gab_page = 0;
			foreach ($this->_versions as $version) {
				$query = "INSERT INTO `gab_page` ("
                       . "`id`,"
                       . "`id_gabarit`,"
                       . "`titre`,"
                       . "`rewriting`,"
                       . "`bal_title`,"
                       . "`bal_key`,"
                       . "`bal_descr`,"
                       . "`no_index`,"
                       . "`importance`,"
                       . "`id_parent`,"
                       . "`ordre`,"
                       . "`date_crea`,"
                       . "`date_modif`,"
                       . "`visible`,"
                       . "`id_version`"
                       . ") VALUES ("
                       . ($id_gab_page > 0 ? $id_gab_page : "NULL") . ","
                       . $page->getGabarit()->getId() . ","
                       . $this->_db->quote($this->_post['titre']) . ","
					   . ($id_gab_page > 0 || $version['id'] > 1 ? "''" : $this->_db->quote($rewriting)) . ","
					   . $this->_db->quote($this->_post['bal_title'] ? $this->_post['bal_title'] : $this->_post['titre']) . ","
					   . $this->_db->quote($this->_post['bal_key']) . ","
					   . $this->_db->quote($this->_post['bal_descr']) . ","
                       . (isset($this->_post['no_index']) ? $this->_post['no_index'] : 0) . ","
                       . $this->_post['importance'] . ","
                       . $id_parent . ", "
                       . $ordre . ","
                       . "NOW(),"
                       . "NOW(),"
                       . " 0,"
                       . $version['id']
					   . ")";
                
//                echo "$query<br />";
                
				if (!$this->_db->query($query))
					return FALSE;

				if ($id_gab_page == 0)
                    $id_gab_page = $this->_db->lastInsertId();                
			}
			
            return $id_gab_page;
		}		
	}
	
	/**
	 *
	 * @param page $page
	 * @return type 
	 */
	private function _savePage($page) {        
        $gabarit = $page->getGabarit();
        $id_parent = $page->getMeta("id");
        $table = $gabarit->getTable();
        
		$champs = $gabarit->getChamps();
		$champsExiste = count($champs);

        if (!isset($this->_post['id_' . $gabarit->getTable()]))
            return FALSE;

        $id_bloc = $this->_post['id_' . $gabarit->getTable()];
        $upd = ($id_bloc > 0);
                
        if ($upd) { 
            $sql1 = "UPDATE `$table` SET ";
            $sql2 = "WHERE `id_version` = " . $this->_post['id_version'] . " AND `id` = " . $id_bloc;
        }
        else {
            $sql1 = "INSERT INTO `$table` (`id_parent`,";
            $sql2 = ") VALUES ($id_parent,";
        }

        if ($champsExiste) {
            foreach ($champs as $champ) {
                $value = $this->_post['champ' . $champ['id']][0];

                if ($champ['typedonnee']=='date')
                    $value = dateFRtoUS($value);

                if ($upd)
                    $sql1 .= "`" . $champ['name'] . "` = " . $this->_db->quote($value) . ",";
                else {
                    $sql1 .= "`" . $champ['name'] . "`,";
                    $sql2 .= $this->_db->quote($value) . ",";
                }
            }
        }

        if ($upd) {
            if ($champsExiste) {
                $sql = substr($sql1, 0, -1) . " " . $sql2;
                
//                echo $sql;
                
                if (!$this->_db->query($sql)) {
                    echo "echec de l'update d'un " . $gabarit->getLabel() . "<br />$sql";   
                    return FALSE;
                }
            }
        }
        else {
            $id_bloc = 0;

            foreach ($this->_versions as $id_version) {
                if ($champsExiste) {
                    $sqltmp1 = $sql1;
                    $sqltmp2 = $sql2;

                    if ($id_bloc) {
                        $sqltmp1 .= "`id`,";
                        $sqltmp2 .= "$id_bloc,";
                    }

                    $sqltmp1 .= "`id_version`";
                    $sqltmp2 .= "$id_version";

                    $sql = $sqltmp1 . $sqltmp2 . ")";
                }
                else
                    $sql = "INSERT INTO `$table` (`id`, `id_parent`, `id_version`) VALUES (NULL, $id_parent, $id_version)";
                
                if (!$this->_db->query($sql)) {
                    echo "echec de l'insertion d'un " . $gabarit->getLabel() . "<br />$sql";
                    return FALSE;
                }

                if (!$id_bloc) $id_bloc = $this->_db->lastInsertId();
            }
        }
        
		return $id_bloc;		
	}
    
    /**
     *
     * @param bloc $bloc
     * @param int $id_parent
     * @return bool 
     */
    private function _saveBloc($bloc, $id_parent) {
        $gabarit = $bloc->getGabarit();
        $table = $gabarit->getTable();
        
        if (!count($this->_post['id_' . $gabarit->getTable()]))
            return FALSE;
        
        $champs = $gabarit->getChamps();
        
        if (count($champs) == 0)
            return FALSE;
        
        $ordre = 1;
        foreach ($this->_post['id_' . $gabarit->getTable()] as $id_bloc) {
            $upd = ($id_bloc > 0);
            
            $visible = array_shift($this->_post['visible']);
            
            if ($upd) { 
                $sql1 = "UPDATE `$table` SET `ordre` = $ordre, `visible` = $visible,";
                $sql2 = "WHERE `id_version` = " . $this->_post['id_version'] . " AND `id` = $id_bloc";
            }
            else {
                $sql1 = "INSERT INTO `$table` (`id_parent`, `id_version`, `ordre`, `visible`,";
                $sql2 = ") VALUES ($id_parent, " . $this->_post['id_version'] . ", $ordre, $visible,";
            }

            foreach ($champs as $champ) {
                $value = array_shift($this->_post['champ' . $champ['id']]);

                if ($champ['typedonnee']=='date')
                    $value = dateFRtoUS($value);

                if ($upd)
                    $sql1 .= "`" . $champ['name'] . "` = " . $this->_db->quote($value) . ",";
                else {
                    $sql1 .= "`" . $champ['name'] . "`,";
                    $sql2 .= $this->_db->quote($value) . ",";
                }
            }

            
            if ($upd) {
                $sql = substr($sql1, 0, -1) . " " . $sql2;
                                
                if (!$this->_db->query($sql)) {
                    echo "Echec de l'update d'un " . $gabarit->getLabel() . "<br />$sql";   
                    return FALSE;
                }
            }
            else {
                $sqltmp1 = $sql1;
                $sqltmp2 = $sql2;

                $sqltmp1 .= "`id`";
                $sqltmp2 .= $id_bloc ? $id_bloc : "NULL";

//                $sqltmp1 .= "`id_version`";
//                $sqltmp2 .= "1";

                $sql = $sqltmp1 . $sqltmp2 . ")";

                if (!$this->_db->query($sql)) {
                    echo "Echec de l'insertion d'un " . $gabarit->getLabel() . "<br />$sql";
                    return FALSE;
                }

                $id_bloc = $this->_db->lastInsertId();
            }
            
            $ids_blocs[] = $id_bloc;
            $ordre++;
        }
        
        if ($this->_upd) {
            $query = "UPDATE `$table` SET `suppr` = NOW() WHERE `suppr` = 0 AND `id_parent` = $id_parent AND `id_version` = " . $this->_post['id_version'] . " AND `id` NOT IN (" . implode(",", $ids_blocs) . ")";
            $this->_db->query($query);
        }
        
        
        
        return TRUE;
    }
    
    
    
	/**
	 *
	 * @param int $gabarit_id
	 * @param string $prefixe 
	 */
	public function createTables($id_gabarit) {
        $gabarit = $this->getGabarit($id_gabarit);
		$this->_createTable($gabarit);
		
        $blocs = $this->getBlocs($gabarit);
        foreach ($blocs as $bloc)
            $this->_createBlocTable ($bloc->getGabarit());
	}
	
	/**
	 *
	 * @param gabarit $gabarit
	 * @param string $prefixe
	 * @return bool 
	 */
	private function _createTable($gabarit) {
        $champs = $gabarit->getChamps();
        
		$createSql = "CREATE TABLE IF NOT EXISTS `" . $gabarit->getTable() . "` ("
			       . "`id` int(11) NOT NULL auto_increment,"
			       . "`id_version` tinyint(1) NOT NULL,"
			       . "`id_parent` int(11) NOT NULL,";
        
		foreach ($champs as $champ)
			$createSql .= "`" . $champ['name'] . "` " . $champ['typesql'] . ",";

		$createSql .= "PRIMARY KEY (`id`, `id_version`)"
		            . ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
        
		return $this->_db->query($createSql);
	}
    
    /**
     *
     * @param gabarit $bloc 
     */
    private function _createBlocTable($bloc) {
        $champs = $bloc->getChamps();
        
		$createSql = "CREATE TABLE IF NOT EXISTS `" . $bloc->getTable() . "` ("
			       . "`id` int(11) NOT NULL auto_increment,"
			       . "`id_version` tinyint(1) NOT NULL,"
			       . "`id_parent` int(11) NOT NULL,"
			       . "`ordre` int(11) NOT NULL,"
			       . "`visible` int(11) NOT NULL,"
			       . "`suppr` datetime NOT NULL,";
        
		foreach ($champs as $champ)
			$createSql .= "`" . $champ['name'] . "` " . $champ['typesql'] . ",";
        
		$createSql .= "PRIMARY KEY (`id`, `id_version`)"
		            . ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
		
        return $this->_db->query($createSql);
    }
}