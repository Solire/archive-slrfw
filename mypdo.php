<?php
/**
 * Extension de PDO
 *
 * @author     smonnot <smonnot@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Slrfw;

/**
 * Extension de PDO
 *
 * @author     smonnot <smonnot@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class MyPDO extends \PDO
{
    /**
     * Transforme la chaine passé en parametre en chaine capable d'être mis
     * en url.
     *
     * @param string $string Chaîne a passer en mode URL
     * @param string $table  Nom de la table où il faudrait controller l'existence
     * @param string $name   Nom du champ de la table où ce trouve le rewrit
     * @param string $param  Ajout de condition supplémentaire en mysql
     *
     * @return string
     */
    public function rewrit($string, $table = null, $name = 'rewrit', $param = '')
    {
        if (!$table) {
            return Format\String::urlSlug($string);
        }
        /**
         * Controle de l'existence du rewrit contenu dans le champ $Name
         * de la table $Table.
         */
        $i = 0;
        do {
            if ($i > 0) {
                $temp = $i . ' ' . $string;
            } else {
                $temp = $string;
            }
            $rewrit = Format\String::urlSlug($temp, '-', 255);

            $query  = 'SELECT COUNT(*)'
                    . ' FROM `' . $table . '`'
                    . ' WHERE `' . $name . '` = ' . $this->quote($rewrit)
                    . ' ' . $param;
            $existe = $this->query($query)->fetch(\PDO::FETCH_COLUMN);
            $i++;
        } while ($existe);

        return $rewrit;
    }

    /**
     * Renvoi toutes les lignes d'une table de la bdd
     *
     * @param string $table nom de la table où il faudrait controller l'existence
     *
     * @return type
     */
    public function listTable($table)
    {
        $query  = 'SELECT *'
                . ' FROM `' . $table . '`';
        $result = $this->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Renvoi une ligne d'une table de la bdd
     *
     * @param string $table   nom de la table où il faudrait controller l'existence
     * @param int    $id      valeur du champ
     * @param string $fieldId nom du champ
     *
     * @return array
     */
    public function getRowFromTable($table, $id, $fieldId = 'id')
    {
        $query  = 'SELECT *'
                . ' FROM `' . $table . '`'
                . ' WHERE `' . $fieldId . '` = ' . $id;
        $result = $this->query($query)->fetch(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * insertion de données dans MySQL
     *
     * @param string $table  nom de la table où il faudrait controller l'existence
     * @param array  $values tableau des valeurs à insérer
     *
     * @return type
     */
    public function insert($table, $values)
    {
        $values = array_map(array($this, 'quote'), (array) $values);
        $fieldNames = array_keys($values);
        $query  = 'INSERT INTO `' . $table . '`'
                . ' (`' . implode('`,`', $fieldNames) . '`)'
                . ' VALUES(' . implode(',', $values) . ')';
        return $this->exec($query);
    }

    /**
     * replace de données dans MySQL
     *
     * @param type $table
     * @param type $values
     *
     * @return type
     */
    public function replace($table, $values)
    {
        $values = array_map(array($this, 'quote'), (array) $values);
        $fieldNames = array_keys($values);
        $query  = 'REPLACE INTO `' . $table . '`'
                . ' (`' . implode("`,`", $fieldNames) . '`)'
                . ' VALUES(' . implode(',', $values) . ')';
        return $this->exec($query);
    }

    /**
     * sélection de données depuis MySQL
     *
     * @param string $table      table
     * @param array  $fields     tableau des champs à récupérer
     * @param bool   $small_size petite requete
     * @param string $where      condition
     * @param string $order      ordre
     *
     * @return array
     */
    public function select($table, $fields, $small_size = false, $where = '', $order = '')
    {
        if (!empty($small_size)) {
            $result_size = 'SQL_SMALL_RESULT';
        } else {
            $result_size = '';
        }

        if (!empty($where)) {
            $where =' WHERE ' . $where;
        }

        $query  = 'SELECT ' . $result_size . ' ' . implode(', ', (array) $fields)
                . ' FROM ' . '`' . $table . '`'
                . $where . $order;

        return $this->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * tri des résultat d'une requête SELECT
     *
     * @param array  $fields
     * @param string $order
     *
     * @return bool
     */
    public function order($fields, $order = 'ASC')
    {
        $order = array_map(array($this, 'quote'), (array) $order);
        if (count($fields) == count($order)) {
            $set = array();
            $fields = (array) $fields;
            for ($i = 0; $i < count($fields); $i++) {
                $set[] = $fields[$i] . ' ' . $order[$i];
            }

            return ' ORDER BY ' . implode(', ', $set);
        }

        return FALSE;
    }

    /**
     * limitation des résultats d'une requête SELECT
     *
     * @param int $offset
     * @param int $number
     *
     * @return bool
     */
    public function limit($offset, $number)
    {
        if (is_numeric($offset) && is_numeric($number)) {
            return ' LIMIT ' . intval($offset) . ', ' . intval($number);
        } else {
            return FALSE;
        }
    }

    /**
     * mis à jour de données de MySQL
     *
     * @param string $table  nom de la table où il faudrait controller l'existence
     * @param array  $values
     * @param string $where
     *
     * @return int
     */
    public function update($table, $values, $where = false)
    {
        $set = array();
        foreach ((array) $values as $field => $value) {

            $set[] = '`' . $field . '` = ' . $this->quote($value);
        }

        return $this->exec('UPDATE `' . $table . '` SET ' . implode(', ', $set) . (!empty($where) ? ' WHERE ' . $where : ''));
    }

    /**
     * suppression de données de MySQL
     *
     * @param string $table nom de la table où il faudrait controller l'existence
     * @param string $where
     *
     * @return int
     */
    public function delete($table, $where)
    {
        return $this->exec('DELETE FROM ' . $table . ' WHERE ' . $where);
    }

    /**
     * Liste des valeurs d'un champ ENUM
     *
     * @param string $table nom de la table où il faudrait controller l'existence
     * @param string $field nom du champ enum
     *
     * @return array
     */
    function getEnumValues($table, $field) {
        $query  = 'SHOW FIELDS FROM `' . $table . '` LIKE \'' . $field . '\'';
        $row    = $this->query($query)->fetch(\PDO::FETCH_ASSOC);

        $match  = array();
        if (!preg_match('`^enum\((.*?)\)$`ism', $row['Type'], $match)) {
            return null;
        }

        $enum = str_getcsv($match[1], ',', '\'');

        return $enum;
    }

    /**
     * Creation d'une table
     *
     * @param string $table
     * @param array  $columns
     *
     * @return bool
     */
    public function createTable($table, $columns) {
        $sql = "CREATE TABLE $table (";
        foreach ($columns as $columnName) {
            $sql .= "`$columnName` VARCHAR(255),";
        }
        $sql = substr($sql, 0, -1) . ");";
        return $this->exec($sql);
    }

    /**
     * Retourne les éléments de tri (WHERE et ORDER BY) pour la requête de
     * recherche en fonction d'un terme de recherche
     *
     * @param string   $term
     * @param string[] $columns
     *
     * @return array
     */
    public function search($term, $columns)
    {
        /**
         * Variable qui contient la chaine de recherche
         */
        $stringSearch = trim($term);

        /**
         * On divise en mots (séparé par des espace)
         */
        $words = preg_split('`\s+`', $stringSearch);

        if (count($words) > 1) {
            array_unshift($words, $stringSearch);
        }

        $filterWords = array();
        $orderBy     = array();
        foreach ($words as $word) {
            foreach ($columns as $key => $value) {
                if (is_numeric($value)) {
                    $pond    = $value;
                    $colName = $key;
                } else {
                    $pond    = 1;
                    $colName = $value;
                }

                $filterWord     = $colName . ' LIKE '
                                . $this->quote('%' . $word . '%');
                $filterWords[]  = $filterWord;
                $orderBy[]      = 'IF(' . $filterWord . ', ' . mb_strlen($word) * $pond . ', 0)';
            }
        }

        return array(
            'where'  => ' (' . implode(' OR ', $filterWords) . ')',
            'order'  => ' ' . implode(' + ', $orderBy),
        );
    }
}

