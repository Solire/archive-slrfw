<?php
/**
 * Gestionnaire de rapport d'erreur Marvin
 * @author Adrien <aimbert@solire.fr>
 * @package Library
 * @subpackage Error
 * @filesource
 */

include 'geshi/geshi.php';

/**
 * Marvin est une methode de rapport d'erreur
 *
 * @author Adrien <aimbert@solire.fr>
 * @package Library
 * @subpackage Error
 */
class Marvin
{
    /**
     * Chemin de configuration du fichier de configuration
     */
    const CONFIG_PATH = 'config/marvin.ini';

    /**
     * Génère un rapport d'alerte
     * @uses Config
     * @param string $title Titre du rapport
     * @param Exception $error Exception à exploiter
     */
    public function __construct($title, $error)
    {
        $this->_config = new Config(self::CONFIG_PATH);
        if (method_exists($error, 'getPrevious') && $error->getPrevious())
            $this->exc = $error->getPrevious();
        else
            $this->exc = $error;
        $this->contact = $this->_config->get('mail', 'contact');
        $this->headers = 'Content-type: text/html; charset=utf-8' . "\r\n"
                       . 'From: Marvin <marvin@solire.fr>' . "\r\n";

        /* = Couleurs :
          ------------------------------- */
        $colors = $this->_config->get('color');
        foreach ($colors as $key => $value) {
            $this->{'color' . $key} = $value;
        }

        $this->title = $title;
        if (isset($_SERVER['SERVER_NAME']))
            $this->title = '[' . $_SERVER['SERVER_NAME'] . '] ' . $this->title;


        /* = Chargement des données passées en paramètre de la page
          ------------------------------------------------- */
        if (!empty($_REQUEST)) {
            foreach ($_REQUEST as $key => $value) {
                $loc = array();

                if (isset($_GET[$key]))
                    $loc[] = 'GET';

                if (isset($_COOKIE[$key]))
                    $loc[] = 'COOKIE';

                if (isset($_POST[$key]))
                    $loc[] = 'POST';

                $req = array();
                $req['loc'] = implode(' | ', $loc);
                $req['key'] = $key;
                $req['value'] = $this->varDump($value);
                $this->request[] = $req;
            }
        }

        $traces = $this->exc->getTrace();
        foreach ($traces as $trace) {

            foreach ($trace['args'] as $key => $arg) {
                $trace['args'][$key] = $this->varDump($arg);
            }

            $trace['showFile'] = $this->readLines(
                $trace['file'], $trace['line']
            );
            $this->trace[] = $trace;
        }
    }

    /**
     * Renvois la chaine contenant le var_dump() de la variable
     *
     * @param mixed $var
     * @return string
     */
    public final function varDump($var)
    {
        ob_start();
        var_dump($var);
        $str = ob_get_clean();

        return $str;
    }

    /**
     * Renvois une chaine contenant les lignes du fichiers formatées pour
     * l'affichage
     *
     * @uses GeSHi
     * @param string $fileName
     * @param int $line
     * @return string
     */
    protected function readLines($fileName, $line)
    {
        $file = file($fileName);
        $strFile = '';
        for ($i = $line - 6; $i < $line + 2; $i++) {
            if ($i < 0 || $i >= count($file))
                continue;
            $strFile .= $file[$i];
        }
        $geshi = new GeSHi($strFile, 'php');
        $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
        $geshi->start_line_numbers_at($line - 6);
        $geshi->set_highlight_lines_extra_style('background: #497E7E;');
        $geshi->highlight_lines_extra(6);
        return $geshi->parse_code();
    }

    /**
     * Envois le rapport
     */
    public function send()
    {
        $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        ob_start();
        include $dir . 'marvin.phtml';
        $str = ob_get_clean();
        mail($this->contact, $this->title, $str, $this->headers);
    }

    /**
     * Affiche le rapport
     */
    public function display()
    {
        $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        include $dir . 'marvin.phtml';
        die();
    }
}