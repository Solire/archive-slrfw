<?php
/**
 * Description of gabaritmanager
 *
 * @author thomas
 */
class gabaritManager extends manager {    
    /**
     * <p>Donne l'identifiant d'une page d'après son rewriting et l'identifiant.</p>
     * @param string $rewriting
     * @param int $id_parent
     * @param int $id_version
     * @return int 
     */
    public function getIdByRewriting($id_version, $rewriting, $id_parent = 0) {
		$query = "SELECT `id` FROM `gab_page`"
               . " WHERE `suppr` = 0 AND `visible` = 1 AND `id_parent` = $id_parent"
               . " AND `id_version` = $id_version AND `rewriting` = " . $this->_db->quote($rewriting);
        
        return $this->_db->query($query)->fetchColumn();
    }
    
    /**
     * <p>Retourne un objet page à partir de l'identifiant de la page <br />
     * ou un objet page vide à partir de l'idenfiant du gabarit</p>
     * @param int $id_gab_page
     * @param int $id_gabarit
     * @param int $version 
     */
    public function getPage($id_version, $id_gab_page, $id_gabarit = 0) {   
        $page = new gabaritPage();
                
        if ($id_gab_page) {
            $query = "SELECT * FROM `gab_page` WHERE `id_version` = $id_version AND `id` = $id_gab_page AND `suppr` = 0";
            $meta = $this->_db->query($query)->fetch(PDO::FETCH_ASSOC);
            
            if (!$meta)
                return FALSE;
            
            $page->setMeta($meta);
            $id_gabarit = $meta['id_gabarit'];            
        }
        
        $gabarit = $this->getGabarit($id_gabarit);
        
        $query = "SELECT * FROM `gab_gabarit` WHERE `id` = " . $gabarit->getIdParent();
        $parentData = $this->_db->query($query)->fetch(PDO::FETCH_ASSOC);
        $gabarit->setGabaritParent($parentData);
        
        if (!$id_gab_page && $gabarit->getIdParent() > 0) {
            $query = "SELECT `p`.`id`, `p`.`titre`, `p`.`rewriting`,"
                . " `q`.`id` `p_id`, `q`.`titre` `p_titre`, `q`.`rewriting` `p_rewriting`,"
                . " `r`.`id` `pp_id`, `r`.`titre` `pp_titre`, `r`.`rewriting` `pp_rewriting`"
                . " FROM `gab_page` `p`"
                . " LEFT JOIN `gab_page` `q` ON `q`.`id` = `p`.`id_parent` AND `q`.`suppr` = 0 AND `q`.`id_version` = $id_version"
                . " LEFT JOIN `gab_page` `r` ON `r`.`id` = `q`.`id_parent` AND `r`.`suppr` = 0 AND `r`.`id_version` = $id_version"
                . " WHERE `p`.`id_gabarit` = " . $gabarit->getIdParent() . " AND `p`.`suppr` = 0 AND `p`.`id_version` = $id_version";
            
            $parents = $this->_db->query($query)->fetchAll(PDO::FETCH_ASSOC);
            $gabarit->setParents($parents);
        }
        
        $page->setGabarit($gabarit);
        
        $blocs = $this->getBlocs($gabarit, $id_gab_page);
        $page->setBlocs($blocs);
        
        if ($id_gab_page) {
            $parents = $this->getParents($meta['id_parent'], $id_version);
            $page->setParents($parents);
            
            $values = $this->getValues($page);
            
            if ($values) {
                $page->setValues($values);
                
                $blocs = $page->getBlocs();
                foreach ($blocs as $bloc) {                    
                    $valuesBloc = $this->getBlocValues($bloc, $id_gab_page, $id_version);
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
		        
		$query = "SELECT IF (`g`.`label` IS NULL, 'general', `g`.`label`), `c`.*"
               . " FROM `gab_champ` `c`"
               . " LEFT JOIN `gab_champ_group` `g` ON `g`.`id` = `c`.`id_group`"
               . " WHERE `id_parent` = $id_gabarit AND `type_parent` = 'gabarit'"
               . " ORDER BY `g`.`ordre`, `c`.`ordre`";
        $champs = $this->_db->query($query)->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
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
            
            $bloc = new gabaritBloc();
                
            $bloc->setGabarit($gabarit_bloc);
            
            $blocs[$gabarit_bloc->getName()] = $bloc;
        }
        
        return $blocs;
    }
    
    /**
     * Retourne la ligne des infos de la table générée à partir d'une page.
     * @param gabaritPage $page 
     * @return array
     */
    public function getValues($page) {
        $query = "SELECT * FROM `" . $page->getGabarit()->getTable() . "`"
               . " WHERE `id_gab_page` = " . $page->getMeta("id")
               . " AND `id_version` = " . $page->getMeta("id_version");
        
        return $this->_db->query($query)->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * <p>Retourne les lignes des infos de la table générée à partir d'un bloc<br />
     * et de la page parente.</p>
     * @param gabarit $bloc
     * @param int $id_gab_page identifiant de la page parente.
     * @param int $id_version identifiant de la version.
     * @param bool $visible <p>si faux on récupère les blocs visibles ou non,<br />
     * si vrai on récupère uniquement les blocs visibles.</p>
     * @return type 
     */
    public function getBlocValues($bloc, $id_gab_page, $id_version, $visible = FALSE) {
        $query = "SELECT * FROM `" . $bloc->getGabarit()->getTable() . "`"
               . " WHERE `id_gab_page` = " . $id_gab_page . " AND `suppr` = 0"
               . " AND `id_version` = $id_version"
               . ($visible ? " AND `visible` = 1" : "")
               . " ORDER BY `ordre`";
        
        return $this->_db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
        
    /**
     * <p>Retourne les parents, grand-parents, aïeuls etc.<br />
     * dans un tableau associatif `nom du gabarit` => `objet page correspondant`</p>
     * @param int $id_gab_page_parent
     * @param int $id_version
     * @return array 
     */
    public function getParents($id_gab_page_parent, $id_version) {
        $parents = array();
        
        while ($id_gab_page_parent > 0) {
            $query = "SELECT * FROM `gab_page`"
                   . " WHERE `id_version` = $id_version AND `id` = $id_gab_page_parent AND `suppr` = 0";
            $parentMeta = $this->_db->query($query)->fetch(PDO::FETCH_ASSOC);

            $parentPage = new gabaritPage();
            $parentPage->setMeta($parentMeta);
            $gabarit = $this->getGabarit($parentMeta['id_gabarit']);
            $parents[] = $parentPage;        

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
	public function getList($id_version, $id_parent = FALSE, $id_gabarit = 0, $visible = FALSE, $orderby = "ordre", $sens = "ASC",$debut = 0, $nbre = 0) {
		$query = "SELECT `p`.*, COUNT(`e`.`id`) `nbre_enfants`"
               . " FROM `gab_page` `p` LEFT JOIN `gab_page` `e` ON `e`.`id_parent` = `p`.`id` AND `e`.`suppr` = 0 AND `e`.`id_version` = $id_version"
               . ($visible ? " AND `e`.`visible` = 1" : "")
               . " WHERE `p`.`suppr` = 0 AND `p`.`id_version` = $id_version"
               . ($visible ? " AND `p`.`visible` = 1" : "");
        
        if ($id_parent !== FALSE) $query .= " AND `p`.`id_parent` = $id_parent";//`p`.`id_parent` = $id_parent AND 
        if ($id_gabarit) $query .= " AND `p`.`id_gabarit` = $id_gabarit";
        
        $query .= " GROUP BY `p`.`id`";
        
        $query .= " ORDER BY `p`.`$orderby` $sens";
        
        if ($nbre) $query .= " LIMIT $debut, $nbre";
        
        $metas = $this->_db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        $pages = array();
        foreach ($metas as $meta) {
            $page = new gabaritPage();
            $page->setMeta($meta);
            $pages[] = $page;
        }
        
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
    public function getSearch($id_version, $term, $id_gabarit = 0, $id_parent = FALSE, $visible = FALSE) {
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
        foreach ($metas as $meta) {
            $page = new gabaritPage();
            $page->setMeta($meta);
            $pages[] = $page;
        }
        
        return $pages;
    }	

    /**
     * <p>Permet de récupère le premier enfant
     * (exemple : pour les rubriques qui n'ont pas de `view`)</p>
     * @param int $id_parent
     * @param int $id_version
     * @return array 
     */
	public function getFirstChild($id_version, $id_parent = 0) {
		$query = "SELECT *"
               . " FROM `gab_page`"
               . " WHERE `id_parent` = $id_parent AND `suppr` = 0 AND `id_version` = $id_version"
               . " AND `visible` = 1"
               . " ORDER BY `ordre`"
               . " LIMIT 0, 1";
		$meta = $this->_db->query($query)->fetch(PDO::FETCH_ASSOC);
        if ($meta) {
            $page = new gabaritPage();
            $page->setMeta($meta);
            return $page;
        }
        
        return NULL;
	}
    
    
    
    
    
    /**
     * <p>Sauve une page et ses blocs dynamique.</p>
     * @param array $donnees
     * @return gabaritPage 
     */
	public function	save($donnees) { 
		$this->_versions = $this->_db->query("SELECT `id` FROM `version`")->fetchAll(PDO::FETCH_COLUMN);
		
		$updating = ($donnees['id_gab_page'] > 0);

        if ($updating)
			$page = $this->getPage($donnees['id_version'], $donnees['id_gab_page'], 0);
		else
			$page = $this->getPage(1, 0, $donnees['id_gabarit']);
		    
        $id_gab_page = $this->_saveMeta($page, $donnees);
        
        if (!$id_gab_page)
            return NULL;
        
        $page = $this->getPage(1, $id_gab_page, 0);
        
        $id_parent = $this->_savePage($page, $donnees);
        
        $blocs = $page->getBlocs();
        foreach ($blocs as $bloc) {
            $this->_saveBloc($bloc, $id_gab_page, $page->getMeta("id_version"), $donnees);
        }
        
		$page = $this->getPage($donnees['id_version'] ? $donnees['id_version'] : 1, $page->getMeta("id"), 0);
        return $page;
	}

    /**
	 *
	 * @param gabaritPage $page
     * @param array $donnees
	 * @return type 
	 */
	private function _saveMeta($page, $donnees) {
        $updating = $donnees['id_gab_page'] > 0;
        
		// Insertion dans la table `gab_page`.
		if ($updating) {
            $rewriting = $this->_db->rewrit($donnees['titre'], 'gab_page', 'rewriting', "AND `suppr` = 0 AND `id_parent` = " . $page->getMeta("id_parent") . " AND `id_version` = " . $page->getMeta("id_version") . " AND `id` != " . $page->getMeta("id"));
            
            $query = "UPDATE `gab_page` SET"
                   . " `titre`      = " . $this->_db->quote($donnees['titre']) . ","
                   . " `bal_title`  = " . $this->_db->quote($donnees['bal_title'] ? $donnees['bal_title'] : $donnees['titre']) . ","
                   . " `bal_key`    = " . $this->_db->quote($donnees['bal_key']) . ","
                   . " `bal_descr`	= " . $this->_db->quote($donnees['bal_descr']) . ","
                   . " `importance`	= " . $donnees['importance'] . ","
                   . " `date_modif`	= NOW(),"
                   . " `no_index`   = " . (isset($donnees['no_index']) && $page->getMeta("id") != 1 ? $donnees['no_index'] : 0)
                   . ", `rewriting`		= " . $this->_db->quote($rewriting)
//                   . ($page->getMeta("rewriting") == "" ? ", `rewriting`		= " . $this->_db->quote($rewriting) : "")
                   . " WHERE `id` = " .  $page->getMeta("id")
                   . " AND `id_version` = " .  $page->getMeta("id_version");
                        
			if (!$this->_db->query($query))
                return FALSE;
            
            return $page->getMeta("id");
		}			
		else {
			$id_parent = isset($donnees['id_parent']) && $donnees['id_parent'] ? $donnees['id_parent'] : 0;
			
			$rewriting = $this->_db->rewrit($donnees['titre'], 'gab_page', 'rewriting', "AND `suppr` = 0 AND `id_parent` = $id_parent AND `id_version` = 1");
			
			$ordre = $this->_db->query("SELECT MAX(`ordre`) FROM `gab_page` WHERE id_parent = $id_parent")->fetchColumn();
			$ordre = $ordre ? $ordre + 1 : 1;
			
			$id_gab_page = 0;
			foreach ($this->_versions as $version) {
				$query = "INSERT INTO `gab_page` SET "
                       . "`id` = "          . ($id_gab_page > 0 ? $id_gab_page : "NULL") . ","
                       . "`id_gabarit` = "  . $page->getGabarit()->getId() . ","
                       . "`titre` = "       . $this->_db->quote($donnees['titre']) . ","
                       . "`rewriting` = "   . ($id_gab_page > 0 || $version['id'] > 1 ? "''" : $this->_db->quote($rewriting)) . ","
                       . "`bal_title` = "   . $this->_db->quote($donnees['bal_title'] ? $donnees['bal_title'] : $donnees['titre']) . ","
                       . "`bal_key` = "     . $this->_db->quote($donnees['bal_key']) . ","
                       . "`bal_descr` = "   . $this->_db->quote($donnees['bal_descr']) . ","
                       . "`no_index` = "    . (isset($donnees['no_index']) ? $donnees['no_index'] : 0) . ","
                       . "`importance` = "  . $donnees['importance'] . ","
                       . "`id_parent` = "   . $id_parent . ", "
                       . "`ordre` = "       . $ordre . ","
                       . "`date_crea` = NOW(),"
                       . "`date_modif` = NOW(),"
                       . "`visible` = 0,"
                       . "`id_version` = "  . $version['id'];
                
//                echo "$query<br />";
                
				if (!$this->_db->exec($query))
					return FALSE;

				if ($id_gab_page == 0)
                    $id_gab_page = $this->_db->lastInsertId();                
			}
			
            return $id_gab_page;
		}
	}
	
	/**
	 *
	 * @param gabaritPage $page
     * @param array $donnees
	 * @return type 
	 */
	private function _savePage($page, $donnees) {
        $updating = $donnees['id_gab_page'] > 0;
        
        $gabarit = $page->getGabarit();
        $id_gab_page = $page->getMeta("id");
        $id_version = $page->getMeta("id_version");
        $table = $gabarit->getTable();
        
		$allchamps = $gabarit->getChamps();
		$champsExiste = count($allchamps);
                
        if ($updating) { 
            $query = "UPDATE `$table` SET ";
            $where = "WHERE `id_version` = $id_version AND `id_gab_page` = $id_gab_page";
        }
        else {
            $query = "INSERT INTO `$table` SET `id_gab_page` = $id_gab_page,";
        }

        foreach ($allchamps as $name_group => $champs) {
            foreach ($champs as $champ) {                
                $value = $donnees['champ' . $champ['id']][0];

                if ($champ['typedonnee']=='date')
                    $value = dateFRtoUS($value);

                $query .= "`" . $champ['name'] . "` = " . $this->_db->quote($value) . ",";
            }
        }

        if ($updating) {
            if ($champsExiste) {
                $queryTmp = substr($query, 0, -1) . " " . $where;
                
                if (!$this->_db->query($queryTmp)) {
                    echo "echec de l'update d'un " . $gabarit->getLabel() . "<br /><textarea>$queryTmp</textarea>";
                    return FALSE;
                }
            }
        }
        else {
            foreach ($this->_versions as $id_version) {
                $queryTmp = $query . "`id_version` = $id_version";
                
                if (!$this->_db->exec($queryTmp)) {
                    echo "echec de l'insertion d'un " . $gabarit->getLabel() . "<br /><textarea>$queryTmp</textarea>";
                    return FALSE;
                }
            }
        }
        
        return TRUE;
	}
    
    /**
     *
     * @param gabaritBloc $bloc
     * @param int $id_gab_page
     * @param int $id_version
     * @param array $donnees
     * @return boolean 
     */
    private function _saveBloc($bloc, $id_gab_page, $id_version, $donnees) {     
        $gabarit = $bloc->getGabarit();
        $table = $gabarit->getTable();
        $champs = $gabarit->getChamps();
        
        $ordre = 1;
        foreach ($donnees['id_' . $gabarit->getTable()] as $id_bloc) {
            $updating = ($id_bloc > 0);
            
            $visible = array_shift($donnees['visible']);
            
            if ($updating) { 
                $query = "UPDATE `$table` SET"
                       . " `ordre` = $ordre,"
                       . " `visible` = $visible,";
            }
            else {
                $query = "INSERT INTO `$table` SET"
                      . " `id_gab_page` = $id_gab_page,"
                      . " `id_version`  = $id_version,"
                      . " `ordre`       = $ordre,"
                      . " `visible`     = $visible,";
            }

            foreach ($champs as $champ) {
                $value = array_shift($donnees['champ' . $champ['id']]);

                if ($champ['typedonnee']=='date')
                    $value = dateFRtoUS($value);

                $query .= "`" . $champ['name'] . "` = " . $this->_db->quote($value) . ",";
            }

            
            if ($updating) {
                $queryTmp = substr($query, 0, -1) . " WHERE `id_version` = $id_version AND `id` = $id_bloc";
                                
                if (!$this->_db->query($queryTmp)) {
                    echo "Echec de l'update d'un " . $gabarit->getLabel() . "<br /><textarea>$queryTmp</textarea>";
                    return FALSE;
                }
            }
            else {
                $queryTmp = substr($query, 0, -1);
                
                if (!$this->_db->exec($queryTmp)) {
                    echo "Echec de l'insertion d'un " . $gabarit->getLabel() . "<br /><textarea>$queryTmp</textarea>";
                    return FALSE;
                }

                $id_bloc = $this->_db->lastInsertId();
            }
            
            $ids_blocs[] = $id_bloc;
            $ordre++;
        }
        
        $query = "UPDATE `$table` SET `suppr` = 1, `date_modif` = NOW()"
                . " WHERE `suppr` = 0 AND `id_gab_page` = $id_gab_page AND `id_version` = $id_version"
                . " AND `id` NOT IN (" . implode(",", $ids_blocs) . ")";
        $this->_db->query($query);

        return TRUE;
    }
}