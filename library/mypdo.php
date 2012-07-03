<?php

/**
 * Extention de PDO
 * @version 1
 */
class MyPDO extends PDO
{
    const BINDMODE_VALUE = 'bindValue';
    const BINDMODE_PARAM = 'bindParam';

    private $Pattern = Array("/À/", "/Á/", "/Â/", "/Ã/", "/Ä/", "/Å/", "/à/", "/á/", "/â/",
        "/ã/", "/ä/", "/å/", "/Ò/", "/Ó/", "/Ô/", "/Õ/", "/Ö/", "/Ø/", "/ò/",
        "/ó/", "/ô/", "/õ/", "/ö/", "/ø/", "/È/", "/É/", "/Ê/", "/Ë/", "/é/",
        "/è/", "/ê/", "/ë/", "/Ç/", "/ç/", "/Ì/", "/Í/", "/Î/", "/Ï/", "/ì/",
        "/í/", "/î/", "/ï/", "/Ù/", "/Ú/", "/Û/", "/Ü/", "/ù/", "/ú/", "/û/",
        "/ü/", "/ÿ/", "/Ñ/", "/ñ/", "/&/");
    private $RepPat = Array("A", "A", "A", "A", "A", "A", "a", "a", "a", "a", "a", "a",
        "O", "O", "O", "O", "O", "O", "o", "o", "o", "o", "o", "o", "E", "E",
        "E", "E", "e", "e", "e", "e", "C", "c", "I", "I", "I", "I", "i", "i",
        "i", "i", "U", "U", "U", "U", "u", "u", "u", "u", "y", "N", "n", "et");

    /**
     * Supprime l'intégralité des accents de la chaine.
     * @param <string> $String
     * @return <string>
     */
    public function no_accent($String)
    {
        $String = preg_replace($this->Pattern, $this->RepPat, $String);
        return $String;
    }

    /**
     * Transforme la chaine passé en parametre en chaine capable d'être mis
     * en url.
     * @param <string> $String Chaîne a passer en mode URL.
     * @param <string> $Table Nom de la table où il faudrait controller l'existence
     * du rewritt
     * @param <string> $Name Nom du champ de la table où ce trouve le rewrit,
     *  à pour valeur par défaut : rewrit
     * @return <string>
     */
    public function rewrit($String, $Table = false, $Name = "rewrit", $Param = "")
    {
        if ($Table) {
//Controle de l'existence du rewrit contenu dans le champ $Name
// de la table $Table.
            $I = 0;
            do {
                $Temp = (($I) ? $I : "") . " $String";
                $Rewrit = $this->make_rew($Temp);
                $Query = "SELECT * FROM $Table WHERE $Name = '$Rewrit' $Param;";
                $Row = $this->query($Query)->fetch(PDO::FETCH_ASSOC);
                $I++;
            } while ($Row);
        } else {
            $Rewrit = $this->make_rew($String);
        }

        return $Rewrit;
    }

    public function listTable($table_name)
    {
        $query = $this->query("Select * from $table_name");
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getRowFromTable($table_name, $id, $fieldId = "id")
    {
        $query = $this->query("Select * from $table_name WHERE $fieldId=$id");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getRowsFromTable($table_name, $id, $fieldId = "id")
    {
        $query = $this->query("Select * from $table_name WHERE $fieldId=$id");
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    private function make_rew($String)
    {
        $String = $this->no_accent($String);
// On retire tout ce qu'il y a entre chevrons, crochets, parenthèses.
        $String = preg_replace('#\[(.+)]#isU', '', $String);
        $String = preg_replace('#<(.+)>#isU', '', $String);
        $String = preg_replace('#\((.+)\)#isU', '', $String);

        $String = strtolower($String);

// Tout les caractères qui ne sont pas aplhanum sur copprimés.
        $String = preg_replace("#([^a-z0-9 \-]?)#", "", $String);
        $String = trim($String);
        $String = str_replace(" ", "-", $String);

// On remplace tous les - concécutifs par des uniques.
        $String = preg_replace("#([\-]+)#", "-", $String);

        return $String;
    }

    public function query($query, $params = array())
    {
        if (!is_array($params))
            $params = array($params);
        foreach ($params as &$p) {
            if (!is_numeric($p))
                $p = $this->quote($p);
        }
        unset($p);
        if ($params == NULL) {
            $funcQuery = parent::query($query);
            return $funcQuery;
        } else {
            $funcQuery = parent::query(vsprintf($query, $params));
            return $funcQuery;
        }
    }

    public function exec($query, $params = array())
    {
        if (!is_array($params))
            $params = array($params);
        foreach ($params as &$p) {
            if (!is_numeric($p))
                $p = $this->quote($p);
        }
        unset($p);
        if ($params == NULL) {
            return parent::exec($query);
        } else {
            return parent::exec(vsprintf($query, $params));
        }
    }
    
    // insertion de données dans MySQL
	public function insert($table, $values) {
		$values = array_map(array($this, 'quote'), (array)$values);
		$fieldNames = array_keys($values);
		return $this->exec("INSERT INTO `".$table."` (`".implode("`,`", $fieldNames)."`) VALUES(".implode(",", $values).")");
	}
 
	// sélection de données depuis MySQL
	public function select($table, $small_size = FALSE, $fields, $where = '', $order = '') {
 
		$result_size = !empty($small_size) ? 'SQL_SMALL_RESULT' : '';
		$where = !empty($where) ? ' WHERE '.$where : '';
 
		return $this->query("SELECT ".$result_size." ".implode(", ", (array)$fields)." FROM ".
		$this->quote($table).$where.$order);
	}
 
	// tri des résultat d'une requête SELECT
	public function order($fields, $order = 'ASC') {
 
		$order = array_map(array($this, 'quote'), (array)$order);
		if (count($fields) == count($order)) {
 
			$set = array();
			$fields = (array)$fields;
			for ($i = 0; $i < count($fields); $i++) {
 
				$set[] = $fields[$i].' '.$order[$i];
			}
 
			return " ORDER BY ".implode(", ", $set);
		}
 
		else {
 
			return FALSE;
		}
	}
 
	// limitation des résultats d'une requête SELECT
	public function limit($offset, $number) {
 
		if (is_numeric($offset) && is_numeric($number)) {
 
			return " LIMIT ".intval($offset).", ".intval($number);
		}
 
		else {
 
			return FALSE;
		}
	}
 
	// mis à jour de données de MySQL
	public function update($table, $values, $where = FALSE) {
		$set = array();
		foreach ((array)$values as $field => $value) {
 
			$set[] = "`" . $field."` = ".$this->quote($value);
		}
 
		return $this->exec("UPDATE `".$table."` SET ".implode(", ", $set).(!empty($where) ? " WHERE ".$where : ''));
	}
 
	// suppression de données de MySQL
	public function delete($table, $where) {
 
		return $this->exec("DELETE FROM ".$table." WHERE ".$where);
	}

}

class MyPDOStatement extends PDOStatement
{

    protected $bound_params;

    public function execute($allParams = array(), $bindMode = MyPDO::BINDMODE_PARAM)
    {
        if (!in_array($bindMode, array(MyPDO::BINDMODE_VALUE, MyPDO::BINDMODE_PARAM))) {
            $this->throwError('Unknow bind mode "<b>' . $bindMode . '</b>".');
            return FALSE;
        } else {
            $last_marker = 0;
            foreach ($allParams as $marker => $value) {
                if (!is_string($marker))
                    $marker = ++$last_marker;
                $type = MyPDO::PARAM_STR;
                if (is_int($value))
                    $type = MyPDO::PARAM_INT;
                $this->$bindMode($marker, $value, $type);
            }
            return parent::execute();
        }
    }

    protected function throwError($error_message, $code = NULL)
    {
        if ($this->getAttribute(MyPDO::ATTR_ERRMODE) == MyPDO::ERRMODE_EXCEPTION)
            throw new MyPDOException($error_message, $code);
        elseif ($this->getAttribute(MyPDO::ATTR_ERRMODE) == MyPDO::ERRMODE_WARNING)
            trigger_error($error_message, E_WARNING);
    }

    public function bindValue($parameter, $value, $data_type = MyPDO::PARAM_STR)
    {
        if ($data_type == MyPDO::PARAM_INT)
            $value = (int) $value;
        $this->setBoundParams($parameter, $value, $data_type);
        return parent::bindValue($parameter, $value, $data_type);
    }

    public function bindParam($parameter, &$variable, $data_type = null, $length = null, $driver_options = null)
    {
        if ($data_type == MyPDO::PARAM_INT)
            $variable = (int) $variable;
        $this->setBoundParams($parameter, $variable, $data_type);
        return parent::bindParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    protected function setBoundParams($name, $value, $data_type = MyPDO::PARAM_STR)
    {
        if ($data_type == MyPDO::PARAM_STR)
            $value = "'$value'";
        if (is_string($name) AND $name[0] != ':')
            $name = ':' . $name;
        $this->bound_params[$name] = $value;
    }

    public function getBuiltQuery()
    {
        $res = $this->queryString;
        if (count($this->bound_params)) {
            if (is_int(key($this->bound_params))) {
                $res = str_replace('?', '%s', $this->queryString);
                foreach ($this->bound_params as &$p)
                    $p = (string) $p;
                unset($p);
                $vals = $this->bound_params;
                for ($i = count($this->bound_params), $max = substr_count($this->queryString, '?'); $i < $max; $i++)
                    $vals[] = '?';
                $res = vsprintf($res, $vals);
            } elseif (is_string(key($this->bound_params))) {
                $res = str_replace(array_keys($this->bound_params), $this->bound_params, $this->queryString);
            }
        }
        return $res;
    }

}


?>
