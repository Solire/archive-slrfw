<?php
/**
 * Extention de PDO
 *
 * @package Library
 * @author  smonnot <smonnot@solire.fr>
 * @license Solire http://www.solire.fr/
 */

namespace Slrfw\Library;

/**
 * Extention de PDO
 *
 * @package Model
 * @author  smonnot <smonnot@solire.fr>
 * @license Solire http://www.solire.fr/
 * @version 1
 */
class MyPDO extends \PDO
{

    /**
     * Tableau des caractère accentué
     * @var array
     */
    private $Pattern = array('/À/', '/Á/', '/Â/', '/Ã/', '/Ä/', '/Å/', '/à/', '/á/', '/â/',
        '/ã/', '/ä/', '/å/', '/Ò/', '/Ó/', '/Ô/', '/Õ/', '/Ö/', '/Ø/', '/ò/',
        '/ó/', '/ô/', '/õ/', '/ö/', '/ø/', '/È/', '/É/', '/Ê/', '/Ë/', '/é/',
        '/è/', '/ê/', '/ë/', '/Ç/', '/ç/', '/Ì/', '/Í/', '/Î/', '/Ï/', '/ì/',
        '/í/', '/î/', '/ï/', '/Ù/', '/Ú/', '/Û/', '/Ü/', '/ù/', '/ú/', '/û/',
        '/ü/', '/ÿ/', '/Ñ/', '/ñ/', '/&/');

    /**
     * Tableau des caractères de remplacement des caractères accentués
     * @var array
     */
    private $RepPat = array('A', 'A', 'A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a', 'a',
        'O', 'O', 'O', 'O', 'O', 'O', 'o', 'o', 'o', 'o', 'o', 'o', 'E', 'E',
        'E', 'E', 'e', 'e', 'e', 'e', 'C', 'c', 'I', 'I', 'I', 'I', 'i', 'i',
        'i', 'i', 'U', 'U', 'U', 'U', 'u', 'u', 'u', 'u', 'y', 'N', 'n', 'et');

    /**
     *
     * @var bool
     */
    private $enableLog = false;

    /**
     *
     * @var float
     */
    private $tempsTotal = 0;

    /**
     *
     * @var int
     */
    private $nbreRequetes = 0;

    /**
     * Met en mode log, écrivant les requetes dans un fichier
     *
     * @param type $bool vrai ou faux
     *
     * @return void
     */
    public function enableLog($bool)
    {
        $this->enableLog = $bool;
    }

    /**
     * Supprime l'intégralité des accents de la chaine.
     *
     * @param string $String chaîne à traiter
     *
     * @return string
     */
    public function noAccent($String)
    {
        $String = preg_replace($this->Pattern, $this->RepPat, $String);
        return $String;
    }

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
    public function rewrit($string, $table = false, $name = 'rewrit', $param = '')
    {
        if ($table) {
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
                $rewrit = $this->makeRew($temp);
                $query  = 'SELECT *'
                        . ' FROM `' . $table . '`'
                        . ' WHERE `' . $name . '` = ' . $this->quote($rewrit)
                        . ' ' . $param;
                $row = $this->query($query)->fetch(\PDO::FETCH_ASSOC);
                $i++;
            } while ($row);
        } else {
            $rewrit = $this->makeRew($string);
        }

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
     * Transforme une chaine par une chaine avec uniquement des caractères
     * alphanumérique et des tirets du 6
     *
     * @param string $string chaîne de caractère à traiter
     *
     * @return string
     */
    private function makeRew($string)
    {
        $string = $this->noAccent($string);

        /** On retire tout ce qu'il y a entre chevrons, crochets, parenthèses. */
        $string = preg_replace('#\[(.+)]#isU', '', $string);
        $string = preg_replace('#<(.+)>#isU', '', $string);
        $string = preg_replace('#\((.+)\)#isU', '', $string);
        $string = strtolower($string);

        /** Tout les caractères qui ne sont pas aplhanum sur copprimés. */
        $string = preg_replace('#([^a-z0-9 \-]?)#', '', $string);
        $string = trim($string);
        $string = str_replace(' ', '-', $string);

        /** On remplace tous les - concécutifs par des uniques. */
        $string = preg_replace('#([\-]+)#', '-', $string);

        return $string;
    }

    /**
     * Execute une requete sql, retourne un objet PDOStatement
     *
     * @param string $query requete à exécuter
     *
     * @return \PDOStatement
     */
    public function query($query)
    {
        $start = microtime(true);

        $result = parent::query($query);

        $end = microtime(true);
        $time = $end - $start;
        $this->log($time, $query, 'query');

        return $result;
    }

    /**
     * Execute une requete sql, retourne le nombre de lignes impactées
     *
     * @param string $query requete à exécuter
     *
     * @return int
     */
    public function exec($query)
    {
        $start = microtime(true);

        $result = parent::exec($query);

        $end = microtime(true);
        $time = $end - $start;
        $this->log($time, $query, 'exec');

        return $result;
    }

    /**
     * Ecrit une requete et le temps d'exécution dans un fichier de log
     *
     * @param float  $time  temps d'exécution
     * @param string $query requete sql
     * @param string $pref  préfixe pour le fichier à écrire
     *
     * @return boolean
     */
    public function log($time, $query, $pref = '')
    {
        $value = $this->tempsTotal + $time;
        $this->tempsTotal = $value;

        $this->nbreRequetes++;

        $dir = '../logs/sql';
        if ($this->enableLog && is_dir($dir) && $query != 'SET NAMES UTF8') {
            $content    = '<div><u>' . date('H:i:s') . ' </u>&nbsp;<i>'
                        . $_SERVER['REQUEST_URI'] . '</i><br /> ' . $query
                        . '</div>'
                        . '<div style="color : #' . ($time > 0.2 ? 'CC0000' : ($time > 0.01 ? 'ED7F10' : '009900' )) . ';">'
                        . round($time, 4) . '</div><div style="color:pink;">total (' . $nb . ') : ' . round($value, 4) . '</div><hr />';
            file_put_contents($dir . '/' . date('Y-m-d') . '_' . $pref . '.html', $content, FILE_APPEND);
        }
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
     * @return boolean
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
     * @return boolean
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

}

