<?php

namespace Slrfw;

/**
 * @version 2
 */
class TranslateMysql
{
    /**
     *
     * @var TranslateMysql
     */
    private static $self;

    private $_translate = array();
    private $_locale = false;
    private $_api = 1;

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

    public function __construct($locale, $idApi, $db)
    {
        $this->setLocale($locale);
        $this->setApi($idApi);
        $this->_db = $db;

        self::$self = $this;
    }

    /**
     * Traduit un message
     *
     * @param string $message Message à traduire
     *
     * @return string message traduit
     * @throws Exception\Lib Si aucune instance de TranslateMysql n'est active
     */
    public static function trad($message)
    {
        if (empty(self::$self)) {
            throw new Exception\Lib('Aucune traduction activée');
        }

        return self::$self->_($message);
    }

    /**
     *
     * @param string $string mot à traduire
     * @return string mot traduit
     */
    public function _($string)
    {
        if (isset($this->_translate[$this->_locale][$string]))
            return $this->_translate[$this->_locale][$string];

        if (!self::DEBUG)
            return $string;
        $this->_db->exec('INSERT INTO traduction SET id_api =  ' . intval($this->_api) . ', cle = ' . $this->_db->quote($string) . ', valeur = ' . $this->_db->quote($string) . ', id_version =  ' . intval($this->_locale));
        $this->_translate[$this->_locale][$string] = $string;
        return $string;
    }

    /**
     * Choix de la langue utilisée.
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
    }

    /**
     * Choix de l'api utilisée.
     *
     * @param string $idApi
     */
    public function setApi($idApi)
    {
        $this->_api = $idApi;
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
        $translateData = $this->_db->query("SELECT cle, valeur FROM traduction WHERE id_api = " . intval($this->_api) . " AND id_version = " . intval($locale))->fetchAll(\PDO::FETCH_UNIQUE | \PDO::FETCH_COLUMN);
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
            throw new \Exception($Message, 0);
    }

}