<?php
/**
 * Traduction des textes statiques
 *
 * @package    Slrfw
 * @author     Stéphane <smonnot@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Slrfw;

/**
 * Traduction des textes statiques
 *
 * @package    Slrfw
 * @author     Stéphane <smonnot@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class TranslateMysql
{
    /**
     *
     * @var TranslateMysql
     */
    protected static $self;

    protected $_translate = array();
    protected $_locale = false;
    protected $_api = 1;
    protected $_versions = array();

    /**
     *
     * @var MyPDO
     */
    protected $_db = null;

    /**
     * Langue par défaut
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
     * Traduit un message
     *
     * @param string $string message à traduire
     *
     * @return string message traduit
     */
    public function _($string, $aide = '')
    {
        if (isset($this->_translate[$this->_locale][$string])) {
            return $this->_translate[$this->_locale][$string];
        }

        if (!self::DEBUG) {
            return $string;
        }

        if (count($this->_versions) == 0) {
            $query  = 'SELECT id FROM version WHERE id_api =' . intval($this->_api);
            $this->_versions = $this->_db->query($query)->fetchAll(\PDO::FETCH_COLUMN);
        }

        foreach ($this->_versions as $versionId) {
            $query  = 'INSERT INTO traduction SET'
                    . ' id_version =  ' . $versionId . ','
                    . ' id_api =  ' . intval($this->_api) . ','
                    . ' cle = ' . $this->_db->quote($string) . ','
                    . ' valeur = ' . $this->_db->quote($string) . ','
                    . ' aide = ' . $this->_db->quote($aide);
            $this->_db->exec($query);
        }

        $this->_translate[$this->_locale][$string] = $string;

        return $string;
    }

    /**
     * Choix de la langue utilisée.
     *
     * @param string $locale
     *
     * @return void
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
    }

    /**
     * Choix de l'api utilisée.
     *
     * @param string $idApi
     *
     * @return void
     */
    public function setApi($idApi)
    {
        $this->_api = $idApi;
    }

    /**
     * charge les translations de la base
     *
     * @return void
     */
    public function addTranslation()
    {
        $this->_loadTranslationData($this->_locale);
    }

    /**
     * Chargement des traductions d'une version donnée
     *
     * @param int $locale identifiant de la version
     *
     * @return void
     */
    protected function _loadTranslationData($locale)
    {
        $query  = 'SELECT cle, valeur'
                . ' FROM traduction'
                . ' WHERE id_api = ' . $this->_api
                . ' AND id_version = ' . $locale;
        $this->_translate[$locale] = $this->_db->query($query)->fetchAll(
            \PDO::FETCH_UNIQUE | \PDO::FETCH_COLUMN);
    }
}
