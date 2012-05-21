<?php

/**
 * @version 2
 */
class Translate
{

    private $_bigEndian = false;
    private $_file = false;
    private $_translate = array();
    private $_locale = false;

    /**
     *
     * @var MyPDO
     */
    private $_db = null;

    /**
     * Langue par d�faut
     */
    const DEFAULT_LANG = 1;

    const DEBUG = true;

    /**
     * Chemin relatif vers le dossier de traduction.
     * @var string 
     */
    private $_dir = "";

    /**
     * Lites des fichiers de langue utilis�s.
     * @var array 
     */
    private $_langFiles = array();

    /**
     * Lites des fichiers de langue JS utilis�s.
     * @var array 
     */
    private $_langFilesJs = array();

    public function __construct($locale, $dir)
    {
        $this->_dir = $dir;

        $MyDirectory = opendir(realpath($this->_dir));
        while ($Entry = @readdir($MyDirectory))
            if ($Entry != '.' && $Entry != '..')
                if (is_dir(realpath($this->_dir) . DIRECTORY_SEPARATOR . $Entry))
                    $this->_lang[] = $Entry;

        $this->setLocale($locale);
    }

    /**
     *
     * @param string $string mot � traduire
     * @return string mot traduit
     */
    public function _($string)
    {
        if (isset($this->_translate[$this->_locale][$string]))
            return $this->_translate[$this->_locale][$string];

        if (!self::DEBUG)
            return $string;

        if (!@strpos(file_get_contents($this->_dir . "trad_manquante_" . $this->_locale . ".txt"), 'msgid "' . $string . '"')) {
            $fp = fopen($this->_dir . "trad_manquante_" . $this->_locale . ".txt", "a+");
            fputs($fp, '# ' . " [" . implode("|", $this->_langFiles) . "]"
                    . "\r\nmsgid \"" . $string . "\"\r\nmsgstr \"" . $string . "\"\r\n\r\n");
            fclose($fp);
        }
        return $string;
    }

    /**
     * Choix de la langue utilis�.
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        if (in_array($locale, $this->_lang))
            $this->_locale = $locale;
        else {
            $this->_locale = self::DEFAULT_LANG;
        }
    }

    /**
     * Ajoute un fichier de traduction
     * @param string $filename
     * @param <type> $locale Obsol�te
     */
    public function addTranslation($filename)
    {
        if ($filename == "")
            return false;

        $this->_langFiles[] = $filename;
        if (!strpos($filename, $this->_dir))
            $filename = $this->_dir . DIRECTORY_SEPARATOR . $this->_locale . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $filename . ".mo";

        $this->_loadTranslationData($filename, $this->_locale);

        /* = Chargement des mask de traduction
          `--------------------- */
        
        foreach ($this->_translate[$this->_locale] as $Key => $Value) {
//            echo 'INSERT INTO traduction SET cle = ' . Registry::get("db")->quote($Key) . ', valeur = ' . Registry::get("db")->quote($Key) . ', id_version = 1 ;';
            if (!preg_match("/^([0-9\,\.]+) (.+)/", $Key, $Foo))
                continue;
            $this->_translateMask[$this->_locale][$Foo[2]][$Foo[1]] = $Value;
            unset($this->_translate[$this->_locale][$Key]);
        }
    }

    /**
     * Ajoute un fichier de traduction JS (requiert Jquery)
     * @param string $filename
     */
    public function addTranslationJs($filename)
    {
        if ($filename == "")
            return false;


        if (!strpos($filename, $this->_dir))
            $this->_langFilesJs[] = 'locale' . DIRECTORY_SEPARATOR . $this->_locale . DIRECTORY_SEPARATOR . 'LC_MESSAGES_JS' . DIRECTORY_SEPARATOR . $filename;
    }
    
    /**
     * Renvoie les fichiers de traduction JS
     * 
     * @return array Liste des traduction JS
     */
    public function getTranslationJs()
    {
            return $this->_langFilesJs;
    }

    private function _readMOData($bytes)
    {
        if ($this->_bigEndian === false) {
            return unpack('V' . $bytes, fread($this->_file, 4 * $bytes));
        } else {
            return unpack('N' . $bytes, fread($this->_file, 4 * $bytes));
        }
    }

    private function _loadTranslationData($filename, $locale)
    {
        $this->_bigEndian = false;
        $this->_file = @fopen($filename, 'rb');

        if (!$this->_file) {
            self::error("Erreur lors de l'ouverture du fichier '" . $filename . "'.");
            return false;
        }

        if (filesize($filename) < 10) {
            self::error("'" . $filename . "' n'est pas un fichier gettext.");
            return false;
        }

        // Endian?
        $input = $this->_readMOData(1);

        if (strtolower(substr(dechex($input[1]), -8)) == "950412de") {
            $this->_bigEndian = false;
        } else if (strtolower(substr(dechex($input[1]), -8)) == "de120495") {
            $this->_bigEndian = true;
        } else {
            self::error("'" . $filename . "' n'est pas un fichier gettext.");
            return false;
        }
        // read revision - not supported for now
        $input = $this->_readMOData(1);

        // number of bytes
        $input = $this->_readMOData(1);
        $total = $input[1];

        // number of original strings
        $input = $this->_readMOData(1);
        $OOffset = $input[1];

        // number of translation strings
        $input = $this->_readMOData(1);
        $TOffset = $input[1];

        // fill the original table
        fseek($this->_file, $OOffset);
        $origtemp = $this->_readMOData(2 * $total);
        fseek($this->_file, $TOffset);
        $transtemp = $this->_readMOData(2 * $total);

        for ($count = 0; $count < $total; ++$count) {
            if ($origtemp[$count * 2 + 1] != 0) {
                fseek($this->_file, $origtemp[$count * 2 + 2]);
                $original = @fread($this->_file, $origtemp[$count * 2 + 1]);
            } else {
                $original = '';
            }

            if ($transtemp[$count * 2 + 1] != 0) {
                fseek($this->_file, $transtemp[$count * 2 + 2]);
                $this->_translate[$locale][$original] = fread($this->_file, $transtemp[$count * 2 + 1]);
            }
        }
    }

    /**
     * Gestion des erreurs
     * @param string $Message
     */
    private static function error($Message)
    {
        if (self::DEBUG)
            throw new Exception($Message, 0);
    }

}