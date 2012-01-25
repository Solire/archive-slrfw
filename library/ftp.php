<?php

/**
 * FTP
 *
 * Gestion FTP
 *
 * @author ShevAbam
 * @version 1.0 - 02 dec 2010
 */
class FTP
{

    private $_host;
    private $_user;
    private $_password;
    private $_port;
    private $_timeout;
    private $_ssl;
    private $_conn;

    /**
     * Constructeur
     */
    public function __construct($host, $user = 'anonymous', $pass = '', $port = 21, $timeout = 90, $ssl = false)
    {
        $this->setHost($host);
        $this->setUser($user);
        $this->setPassword($pass);
        $this->setPort($port);
        $this->setTimeout($timeout);
        $this->setSsl($ssl);
        $this->_connection();
        $this->_login();
        return $this;
    }

    /**
     * Retourne le serveur
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * Définit le serveur
     */
    public function setHost($new)
    {
        if (trim(!empty($new)))
            $this->_host = $new;
        return $this;
    }

    /**
     * Retourne l'utilisateur
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Définit l'utilisateur
     */
    public function setUser($new)
    {
        if (trim(!empty($new)))
            $this->_user = $new;
        return $this;
    }

    /**
     * Retourne le mot de passe
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Définit le mot de passe
     */
    public function setPassword($new)
    {
        $this->_password = $new;
        return $this;
    }

    /**
     * Retourne le port
     */
    public function getPort()
    {
        return $this->_port;
    }

    /**
     * Définit le port
     */
    public function setPort($new)
    {
        if (trim(!empty($new)))
            $this->_port = $new;
        return $this;
    }

    /**
     * Retourne le timeout
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * Définit le timeout
     */
    public function setTimeout($new)
    {
        if (trim(!empty($new)) && is_numeric($new))
            $this->_timeout = $new;
        return $this;
    }

    /**
     * Retourne la valeur du mode SSL
     */
    public function getSsl()
    {
        return $this->_ssl;
    }

    /**
     * Définit le mode SSL
     */
    public function setSsl($new)
    {
        if (trim(!empty($new)) && is_bool($new))
            $this->_ssl = $new;
        return $this;
    }

    /**
     * Connexion en SSL ou non
     */
    private function _connection()
    {
        if ($this->_ssl == true) {
            $this->_conn = ftp_ssl_connect($this->_host, $this->_port, $this->_timeout);
        } elseif ($this->_ssl == true || $this->_conn == false) {
            $this->_conn = ftp_connect($this->_host, $this->_port, $this->_timeout);
        }
    }

    /**
     * Identification
     */
    private function _login()
    {
        if ($this->_conn != false)
            ftp_login($this->_conn, $this->_user, $this->_password);
    }

    /**
     * Active le mode passif
     */
    public function enablePassive()
    {
        if ($this->_conn != false) {
            ftp_pasv($this->_conn, true);
        }
        return $this;
    }

    /**
     * Ferme la connexion
     */
    public function close()
    {
        if ($this->_conn != false)
            return ftp_close($this->_conn);
    }

    /**
     * Alias de close()
     */
    public function quit()
    {
        $this->close();
    }

    /**
     * Liste le contenu d'un dossier avec les détails ou non
     */
    public function ls($dir = '.', $full = false)
    {
        if ($this->_conn != false) {
            if ($full === false) {
                $content = ftp_nlist($this->_conn, $dir);
                // On supprime les dossiers "." et ".."
                unset($content[0], $content[1]);
            } else {
                $content = ftp_rawlist($this->_conn, $dir);
            }
            return $content;
        }
    }

    /**
     * Retourne le nom du dossier courant
     */
    public function pwd()
    {
        if ($this->_conn != false)
            return ftp_pwd($this->_conn);
    }

    /**
     * Création d'un répertoire
     */
    public function mkdir($dir = '')
    {
        if ($this->_conn != false) {
            if (trim(!empty($dir))) {
                if (!ftp_mkdir($this->_conn, $dir))
                    throw new Exception('FTP :: Impossible de créer le répertoire "' . $dir . '" (existant ou erreur)');
            }
        }
        return $this;
    }

    /**
     * Suppression d'un répertoire
     */
    public function rmdir($dir = '')
    {
        if ($this->_conn != false) {
            if (trim(!empty($dir))) {
                if (!ftp_rmdir($this->_conn, $dir))
                    throw new Exception('FTP :: Impossible de supprimer le répertoire "' . $dir . '" (inexistant ou erreur)');
            }
        }
        return $this;
    }

    /**
     * Changement de répertoire
     */
    public function cd($dir = '')
    {
        if ($this->_conn != false) {
            if (trim(!empty($dir))) {
                if (!ftp_chdir($this->_conn, $dir))
                    throw new Exception('FTP :: Impossible de changer de répertoire ("' . $dir . '")');
            }
        }
        return $this;
    }

    /**
     * Remonte au dossier parent
     */
    public function cdup()
    {
        if ($this->_conn != false) {
            if (!ftp_cdup($this->_conn))
                throw new Exception('FTP :: Impossible de changer de répertoire ("' . $dir . '")');
        }
        return $this;
    }

    /**
     * Renomme un fichier ou un dossier
     */
    public function rename($oldname, $newname)
    {
        if ($this->_conn != false) {
            if (trim(!empty($oldname)) && trim(!empty($newname))) {
                if (!ftp_rename($this->_conn, $oldname, $newname))
                    throw new Exception('FTP :: Impossible de renommer "' . $oldname . '" en "' . $newname . '" (inexistant ou erreur)');
            }
        }
        return $this;
    }

    /**
     * Supprime un fichier
     */
    public function del($file = '')
    {
        if ($this->_conn != false) {
            if (trim(!empty($file))) {
                if (!ftp_delete($this->_conn, $file))
                    throw new Exception('FTP :: Impossible de supprimer le fichier "' . $file . '" (inexistant ou erreur)');
            }
        }
        return $this;
    }

    /**
     * Retourne la taille d'un fichier en octet
     */
    public function size($file = '')
    {
        if ($this->_conn != false) {
            if (trim(!empty($file))) {
                $size = ftp_size($this->_conn, $file);
                if ($size == -1)
                    throw new Exception('FTP :: Impossible de récupérer la taille du fichier "' . $file . '" (inexistant ou erreur)');
                else
                    return $size;
            }
        }
    }

    /**
     * Modifie les droits d'un fichier ou d'un dossier
     */
    public function chmod($file, $mode)
    {
        if ($this->_conn != false) {
            if (trim(!empty($file)) && trim(!empty($mode))) {
                $mode = str_pad($mode, 4, '0', STR_PAD_LEFT);
                if (!ftp_chmod($this->_conn, $mode, $file))
                    throw new Exception('FTP :: Impossible de modifier les droits du fichier/dossier "' . $file . '"');
            }
        }
        return $this;
    }

    /**
     * Upload un fichier
     */
    public function put($local, $remote, $mode = FTP_BINARY)
    {
        if ($this->_conn != false) {
            if (trim(!empty($local)) && trim(!empty($remote))) {
                if (!ftp_put($this->_conn, $remote, $local, $mode))
                    throw new Exception('FTP :: Impossible d\'uploader le fichier "' . $local . '"');
            }
        }
        return $this;
    }

    /**
     * Récupère un fichier
     */
    public function get($remote, $local, $mode = FTP_BINARY)
    {
        if ($this->_conn != false) {
            if (trim(!empty($local)) && trim(!empty($remote))) {
                if (!ftp_get($this->_conn, $local, $remote, $mode))
                    throw new Exception('FTP :: Impossible de récupérer le fichier "' . $local . '"');
            }
        }
        return $this;
    }

}
?>

