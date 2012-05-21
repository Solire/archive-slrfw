<?php

/**
 * @version 2
 */
class TranslateMysql
{

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

    public function __construct($locale, $db)
    {
        $this->setLocale($locale);
        $this->_db = $db;
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
        $this->_db->exec('INSERT INTO traduction SET cle = ' . $this->_db->quote($string) . ', valeur = ' . $this->_db->quote($string) . ', id_version =  ' . intval($this->_locale));
        $this->_translate[$this->_locale][$string] = $string;
        return $string;
    }

    /**
     * Choix de la langue utilis�.
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
    }

    /**
     * charge les translations de la base
     */
    public function addTranslation()
    {


        $this->_loadTranslationData($this->_locale);

        if (isset($this->_translate[$this->_locale])) {
            foreach ($this->_translate[$this->_locale] as $Key => $Value) {
                if (!preg_match("/^([0-9\,\.]+) (.+)/", $Key, $Foo))
                    continue;
                $this->_translateMask[$this->_locale][$Foo[2]][$Foo[1]] = $Value;
                unset($this->_translate[$this->_locale][$Key]);
            }
        } else {
            $this->_translate[$this->_locale] = array();
        }
    }

    private function _loadTranslationData($locale)
    {
        $translateData = $this->_db->query("SELECT cle, valeur FROM traduction WHERE id_version = " . intval($locale))->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_COLUMN);
        foreach ($translateData as $key => $value) {
            $this->_translate[$locale][$key] = $value;
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