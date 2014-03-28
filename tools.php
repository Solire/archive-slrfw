<?php

namespace Slrfw;

/** @todo faire la présentation du code */

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tools
 *
 * @author stephane
 */
class Tools {

    static function mail_utf8($to, $subject = '(No subject)', $message = '', $header = '', $type = "text/plain") {
        $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: ' . $type . '; charset="UTF-8"' . "\r\n";
        mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $message, $header_ . $header);
    }
    
    static function cut($text, $nbCharsMax)
    {
        return mb_strlen($text, 'UTF-8') > $nbCharsMax ? mb_substr($text, 0, $nbCharsMax, 'UTF-8') . '...' : $text;
    }

    /**
     *
     *
     * @param type $valeur
     *
     * @return type
     */
    static function format_taille($valeur) {
        $strTmp = "";

        if (preg_match("#^[0-9]{1,}$#", $valeur)) {
            if ($valeur >= 1000000) {
                // Taille supÃ©rieur Ã  1 MegaOctet
                $strTmp = sprintf("%01.2f", $valeur / 1000000);
                // Suppression des "0" en fin de chaine
                $strTmp = preg_replace("#[\.]{1}[0]{1,}$#", "", $strTmp) . " Mo";
            } else if ($valeur >= 1000) {
                // Taille infÃ©rieur Ã  1 MegaOctet
                $strTmp = sprintf("%01.2f", $valeur / 1000);
                // Suppression des "0" en fin de chaine
                $strTmp = preg_replace("#[\.]{1}[0]{1,}$#", "", $strTmp) . " Ko";
            } else if ($valeur >= 0) {
                // Taille infÃ©rieur Ã  1 KiloOctet
                $strTmp = $valeur . " octect";
                if ($valeur > 0)
                    $strTmp .= "s";
            }
            else {
                $strTmp = $valeur;
            }
        } else {
            $strTmp = $valeur;
        }

        return $strTmp;
    }

    /**
     * @deprecated ???
     */
    static function RelativeTimeFromDate($date) {
        if (substr($date, 0, 10) == "0000-00-00")
            return "";

        $timestamp = strtotime($date);
        return self::RelativeTime($timestamp);
    }

    /**
     * @deprecated ???
     */
    static function RelativeTime($timestamp) {
        $difference = time() - $timestamp;
        $periods = array("seconde", "minute", "heure", "jour", "semaine",
            "mois", "année", "décennie");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

        if ($difference > 0) { // this was in the past
            $ending = "il y a";
        } else { // this was in the future
            $difference = -$difference;
            $ending = "dans";
        }
        for ($j = 0; $difference >= $lengths[$j]; $j++)
            $difference /= $lengths[$j];
        $difference = round($difference);
        if ($difference != 1 && $periods[$j] != "mois")
            $periods[$j].= "s";
        $text = "$ending $difference $periods[$j]";
        return $text;
    }

    /**
     * @deprecated ???
     */
    static function random($car) {
        $string = "";
        $chaine = "abcdefghijklmnpqrstuvwxy0123456789";
        srand((double) microtime() * 1000000);
        for ($i = 0; $i < $car; $i++) {
            $string .= $chaine[rand() % strlen($chaine)];
        }
        return $string;
    }

    /**
     * @deprecated ???
     */
    static function configAssign($arrayConf, $columnName, $columnValue, $columnLang = null, $lang = null) {
        $tableConfig = array();

        foreach ($arrayConf as $key => $value) {
            //SI PAS DE GESTION DE LANGUE
            if ($columnLang == null) {
                $tableConfig[$value[$columnName]] = $value[$columnValue];
            }
            //SI GESTION DE LANGUE
            else {
                // ON RECUPERE LA CONFIG DE LA LANG PASSER EN PARAM
                if ($lang != null && ($value[$columnLang] == $lang || $value[$columnLang] == 0))
                    $tableConfig[$value[$columnName]] = $value[$columnValue];

                // ON RECUPERE LA CONFIG POUR TOUTES LES LANGS
                if ($lang == null)
                    $tableConfig[$value[$columnLang]][$value[$columnName]] = $value[$columnValue];
            }
        }
        return $tableConfig;
    }

    /**
     * Recherche dans un array multidim
     *
     * @param array $parents
     * @param array $searched
     * @return boolean
     */
    static function multidimensional_search($parents, $searched) {
        if (empty($searched) || empty($parents)) {
            return false;
        }

        foreach ($parents as $key => $value) {
            $exists = true;
            foreach ($searched as $skey => $svalue) {
                $exists = ($exists && IsSet($parents[$key][$skey]) && $parents[$key][$skey] == $svalue);
            }
            if ($exists) {
                return $key;
            }
        }

        return false;
    }

    static function might_serialize($val) {
        if (is_object($val)) {
            $obj_keys = array_keys(get_object_vars($val));
            foreach ($obj_keys as $k) {
                $val->$k = self::might_serialize($val->$k);
            }
            $val = serialize($val);
        }
        if (is_array($val)) {
            foreach ($val as &$v) {
                $v = self::might_serialize($v);
            }
            $val = serialize($val);
        }

        return $val;
    }

    static function might_unserialized($val) {
//$pattern = "/.*\{(.*)\}/";
        if (self::is_serialized($val)) {
            $ret = unserialize($val);
            foreach ($ret as &$r) {
                $r = self::might_unserialized($r);
            }

            return $ret;
        } else {
            return $val;
        }
    }

    static function is_serialized($val) {
        if (!is_string($val)) {
            return false;
        }
        if (trim($val) == "") {
            return false;
        }
//if (preg_match("/^(i|s|a|o|d)(.*);/si",$val)) { return true; }
        if (preg_match("/^(i|s|a|o|d):(.*);/si", $val) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Construit une url propre à partir d'une chaine de caractere,
     * @param string $string la chaine de base à transformer.
     * @return string La chaine transfomée.
     *
     * @deprecated ???
     */
    static function friendlyURL($string) {
        $string = preg_replace("`\[.*\]`U", "", $string);
        $string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace("`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i", "\\1", $string);
        $string = preg_replace(array("`[^a-z0-9]`i", "`[-]+`"), "-", $string);
        return strtolower(trim($string, '-'));
    }

    /**
     * @deprecated ???
     */
    static function trimUltime($chaine) {
        $chaine = trim($chaine);
        $chaine = str_replace("\t", " ", $chaine);
        $chaine = str_replace("\r", " ", $chaine);
        $chaine = str_replace("\n", " ", $chaine);
        $chaine = preg_replace("( +)", " ", $chaine);
        return $chaine;
    }

    /**
     * Advanced browse of a directory<br/>
     * Retrieves the names of files and directories
     * @param string $path <p>
     * Path to list.
     * </p>
     * @param bool $dir [optional] <p>
     * return directories's name.
     * </p>
     * @param bool $file [optional] <p>
     * return files's name.
     * </p>
     * @param bool $recursive [optional] <p>
     * recursive browse
     * </p>
     * @param bool $hierarch [optional] <p>
     * return multi dimension array, keeping the tree
     * </p>
     * @param bool $fullPath [optional] <p>
     * return fullpath of each files and directories.
     * </p>
     * @param bool $extension [optional] <p>
     * return files with their extension.
     * </p>
     * @return array an array with each name of directories/files
     * @deprecated ???
     */
    static function listDir($path, $dir = true, $file = true, $recursive = true, $hierarch = false, $fullPath = true, $extension = true) {
//        $path2 = $path . (substr($path, - 1) != "/" ? "/" : "" ) . "*";
        $path2 = $path;
        $myVideos = array();
        foreach (iglob($path2) as $value) {
            $value3 = substr($value, strrpos($value, "/") + 1);
            $value2 = $fullPath ? $value : $value3;
            if (is_dir($value)) {
                if ($dir)
                    if ($hierarch)
                        $myVideos[$value3] = $value2 . '/';
                    else
                        $myVideos[] = $value2 . '/';
                if ($recursive)
                    if ($hierarch)
                        $myVideos[$value3] = Tools::listDir($value, $dir, $file, $recursive, $hierarch, $fullPath);
                    else
                        $myVideos = array_merge(Tools::listDir($value . "/*", $dir, $file, $recursive, $hierarch, $fullPath), $myVideos);
            }
            else
            if ($file)
                if ($extension)
                    $myVideos[] = $value2;
                else
                    $myVideos[] = self::removeExtension($value2);
        }
        return $myVideos;
    }

    /**
     * @deprecated ???
     */
    static function removeExtension($fileName) {
// cherche la postion du '.'
        $position = strpos($fileName, ".");
// enleve l'extention, tout ce qui se trouve apres le '.'
        $fileNameWithoutExtension = substr($fileName, 0, $position);
        return $fileNameWithoutExtension;
    }

    /**
     * @deprecated ???
     */
    static function get_file_extension($file_name) {
        return substr(strrchr($file_name, '.'), 1);
    }

    /**
     *
     *
     * @param string $date
     * @param bool $moiscomplet
     *
     * @return string
     *
     * @deprecated ??? use Slrfw\Format\DateTime::toText($date) instead
     * @see Format\DateTime
     */
    static function formate_date_texte($date, $moiscomplet = FALSE) {
        if ($moiscomplet)
            $lesmois = array("", "janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre");
        else
            $lesmois = array("", "janv.", "fév.", "mars", "avril", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc.");

        $date = explode("-", $date);
        $d = strpos($date[2], " ");
        if ($d) {
            $heure = substr($date[2], $d + 1, strrpos($date[2], ":") - ($d + 1));
            $ladate = (int) $date[2] . " " . $lesmois[(int) $date[1]] . " " . $date[0] . " à " . $heure;
        }
        else
            $ladate = (int) $date[2] . " " . $lesmois[(int) $date[1]] . " " . $date[0];

        return ($ladate);
    }

    /**
     * transforme une date au format US (format DATE de mysql) en date au format FR et inverse.
     *
     * @param string $date date sous la forme YYYY-mm-dd resp. dd-mm-YYYY
     * @param string $glueBefore séparateur de la $dateUS
     * @param string $glueAfter séparateur de la date à retourner
     *
     * @return string sous la forme dd-mm-YYYY resp. YYYY-mm-dd
     *
     * @deprecated ??? use Slrfw\Format\DateTime::sql($dateSql) instead
     * @see Format\DateTime
     */
    static function formate_date_nombre($dateUS, $glueBefore = '-', $glueAfter = '-') {
        $heureUs = substr($dateUS, 10);
        $dateUS = substr($dateUS, 0, 10);



        return implode($glueAfter, array_reverse(explode($glueBefore, $dateUS))) . $heureUs;
    }

    /**
     *
     *
     * @param type $chaine
     *
     * @return type
     *
     * @deprecated ???
     */
    static function regexAccents($chaine) {
        mb_internal_encoding("UTF-8");
        mb_regex_encoding('UTF-8');
        $accent = array('a', 'à', 'á', 'â', 'ã', 'ä', 'å', 'c', 'ç', 'e', 'è', 'é', 'ê', 'ë', 'i', 'ì', 'í', 'î', 'ï', 'o', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'u', 'ù', 'ú', 'û', 'ü', 'y', 'ý', 'ý', 'ÿ');
        $inter = array('%01', '%02', '%03', '%04', '%05', '%06', '%07', '%08', '%09', '%10', '%11', '%12', '%13', '%14', '%15', '%16', '%17', '%18',
            '%19', '%20', '%21', '%22', '%23', '%24', '%25', '%26', '%27', '%28', '%29', '%30', '%31', '%32', '%33', '%34', '%35');
        $regex = array('[aàáâãäå]', '[aàáâãäå]', '[aàáâãäå]', '[aàáâãäå]', '[aàáâãäå]', '[aàáâãäå]', '[aàáâãäå]',
            '[cç]', '[cç]',
            '[eèéêë]', '[eèéêë]', '[eèéêë]', '[eèéêë]', '[eèéêë]',
            '[iìíîï]', '[iìíîï]', '[iìíîï]', '[iìíîï]', '[iìíîï]',
            '[oðòóôõö]', '[oðòóôõö]', '[oðòóôõö]', '[oðòóôõö]', '[oðòóôõö]', '[oðòóôõö]', '[oðòóôõö]',
            '[uùúûü]', '[uùúûü]', '[uùúûü]', '[uùúûü]',
            '[yýýÿ]', '[yýýÿ]', '[yýýÿ]', '[yýýÿ]');
        $chaine = str_ireplace($accent, $inter, $chaine);
        $chaine = str_replace($inter, $regex, $chaine);
        return $chaine;
    }

    /**
     *
     * @param type $chaine
     * @param string $keywords
     *
     * @return type
     */
    static function highlightedSearch($chaine, $keywords)
    {
        mb_internal_encoding("UTF-8");
        mb_regex_encoding('UTF-8');
        for ($Z = 0; $Z < count($keywords); $Z++) {
            if (str_replace(' ', '', $keywords) != "") {
                $keywords[$Z] = '#(' . self::regexAccents(str_replace(array('<¤>', '</¤>'), '', $keywords[$Z])) . ')#iu';
            } else {
                array_splice($keywords, $Z, 1);
                $Z--;
            }
        }
        if (is_array($keywords) && count($keywords) > 0) {
            $chaine = preg_replace($keywords, '<¤>$1</¤>', $chaine);
            $chaine = str_replace(array('<¤>', '</¤>'), array('<strong>', '</strong>'), $chaine);
        }
        return $chaine;
    }

}

/**
 *
 *
 * @param type $pattern
 * @param type $flags
 *
 * @return type
 *
 * @deprecated ???
 */
function iglob($pattern, $flags = null) {
    $path = preg_split(
            '#(?<=\A|[\\\\/])((?>[^\\\\/*?]*)[*?](?>[^\\\\/]*))(?=\Z|[\\\\/])#', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
    );

    foreach ($path as &$n)
        if (preg_match('/[*?]/', $n)) {
            $re = '';
            for ($i = 0, $l = strlen($n); $i < $l; $i++)
                switch ($n{$i}) {
                    case '*': $re .= '.*';
                        break;
                    case '?': $re .= '.';
                        break;
                    default: $re .= sprintf('\x%02x', ord($n{$i}));
                }
            $n = array(0, "/^$re$/i");
        }
        else
            $n = array(1, $n);

    $res = array();
    iglob_DFS($path, $flags, '', 0, $res);
    if (!($flags & GLOB_NOSORT))
        sort($res);

    return $res;
}

/**
 *
 * @param type $path
 * @param type $flags
 * @param type $parent
 * @param type $lvl
 * @param type $res
 *
 * @return void
 *
 * @deprecated ???
 */
function iglob_DFS($path, $flags, $parent, $lvl, &$res) {
    $depth = count($path) - 1;

    if (($lvl < $depth) && $path[$lvl][0])
        $parent .= $path[$lvl++][1];

    $files = array();
    if ($path[$lvl][0])
        $files[] = $path[$lvl][1];
    else
    if ($d = @opendir(($parent == '') ? '.' : $parent)) {
        while (($n = readdir($d)) !== false)
            if ($n != '.' && $n != '..')
                $files[] = $n;
        closedir($d);
    }

    foreach ($files as $f)
        if ($path[$lvl][0] || preg_match($path[$lvl][1], $f)) {
            $fullpath = $parent . $f;
            if ($lvl == $depth) {
                if (!($flags & GLOB_ONLYDIR) || is_dir($fullpath))
                    $res[] = $fullpath;
            }
            else
                iglob_DFS($path, $flags, $fullpath, $lvl + 1, $res);
        }
}

