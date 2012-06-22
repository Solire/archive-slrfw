<?php



Class User {

    private $id; //int(11)
    private $nom; //varchar(100)
    private $prenom; //varchar(100)
    private $societe; //varchar(100)
    private $adresse; //varchar(255)
    private $ville; //varchar(255)
    private $cp; //varchar(12)
    private $pays; //varchar(255)
    private $tel; //varchar(30)
    private $mail; //varchar(255)
    private $date_crea; //datetime
    private $activation; //varchar(15)
    private $password; //varchar(64)
    private $admin; //int(1)
    private $error;

    public function _($string) {
        return $string;
//            return $this->translate->_($string);
    }

    /**
     * New object to the class. Don�t forget to save this new object "as new" by using the function $class->Save_Active_Row_as_New(); 
     *
     */
    public function New_user($nom, $prenom, $societe, $adresse, $ville, $cp, $pays, $tel, $mail, $password, $checkpassword) {
        $this->setnom($nom);
        $this->setprenom($prenom);
        $this->setsociete($societe);
        $this->setadresse($adresse);
        $this->setville($ville);
        $this->setcp($cp);
        $this->setpays($pays);
        $this->settel($tel);
        $this->setmail($mail);
        $this->setdate_crea();
        $this->setpassword($password, $checkpassword);
    }

    /**
     * Load one row into var_class. To use the vars use for exemple echo $class->getVar_name; 
     *
     * @param key_table_type $key_row
     * 
     */
    public function Load_from_key($key_row) {
        $result = Registry::get("db")->query("Select * from user where id = \"$key_row\" ");
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $this->id = $row["id"];
            $this->nom = $row["nom"];
            $this->prenom = $row["prenom"];
            $this->societe = $row["societe"];
            $this->password = $row["password"];
            $this->adresse = $row["adresse"];
            $this->ville = $row["ville"];
            $this->cp = $row["cp"];
            $this->pays = $row["pays"];
            $this->tel = $row["tel"];
            $this->mail = $row["mail"];
            $this->date_crea = $row["date_crea"];
        }
    }

    /**
     * Delete the row by using the key as arg
     *
     * @param key_table_type $key_row
     *
     */
    public function Delete_row_from_key($key_row) {
        Registry::get("db")->query("DELETE FROM user WHERE id = $key_row");
    }

    /**
     * Update the active row table on table
     */
    public function Save_Active_Row() {
        Registry::get("db")->query("UPDATE user set nom = \"$this->nom\", prenom = \"$this->prenom\", societe = \"$this->societe\", adresse = \"$this->adresse\", ville = \"$this->ville\", cp = \"$this->cp\", pays = \"$this->pays\", tel = \"$this->tel\", mail = \"$this->mail\", date_crea = \"$this->date_crea\", password = \"$this->password\" where id = \"$this->id\"");
    }

    /**
     * Save the active var class as a new row on table
     */
    public function Save_Active_Row_as_New() {
        $this->setactivation();
        Registry::get("db")->query("Insert into user (nom, prenom, societe, adresse, ville, cp, pays, tel, mail, date_crea, activation, password) values (\"$this->nom\", \"$this->prenom\", \"$this->societe\", \"$this->adresse\", \"$this->ville\", \"$this->cp\", \"$this->pays\", \"$this->tel\", \"$this->mail\", \"$this->date_crea\", \"$this->activation\", \"$this->password\")");
        $this->MailActivation();
    }

    /**
     * Returns array of keys order by $column -> name of column $order -> desc or acs
     *
     * @param string $column
     * @param string $order
     */
    public function GetKeysOrderBy($column, $order) {
        $keys = array();
        $i = 0;
        $result = Registry::get("db")->query("SELECT id from user order by $column $order");
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $keys[$i] = $row["id"];
            $i++;
        }
        return $keys;
    }

    public function CheckUser($email, $pass, $hash = true, $root = false) {
        $db = Registry::get("db");

        if ($email == NULL AND $pass == NULL) {
            $this->error[] = "Adresse mail et mode passe vide";
            return false;
        }


        //On sécurise contre les injections etc ...
        $email = htmlentities($email, ENT_QUOTES, "UTF-8");
        $pass = htmlentities($pass, ENT_QUOTES, "UTF-8");

        //Hashage du mot de passe pour le comparé à celui dans la base
        if ($hash)
            $pass = md5(sha1($pass));


        $sql = "SELECT mail, activation, id FROM user WHERE mail=:mail AND password=:pass";
        $prepareQuery = $db->prepare($sql);
        $prepareQuery->bindParam("mail", $email);
        $prepareQuery->bindParam("pass", $pass);
        $prepareQuery->execute();
        $retour = $prepareQuery->fetchAll(PDO::FETCH_ASSOC);
        if (count($retour) == 1) {
            if ($retour[0]['activation'] != "" && !$root) {
                $this->error[] = "Erreur d'identification, un mail a du vous être envoyé vous proposant d'activer votre compte.";
                return false;
            }
        } else {
            $this->error[] = "Erreur d'identification";
            return false;
        }

        $this->Load_from_key($retour[0]['id']);
        return true;
    }

    public function MailActivation() {
        $db = Registry::get("db");

        //$this->creationDate = date("Y-m-d");
        //$sql="SELECT User_email FROM Need INNER JOIN User ON Need_User_ID = User_ID WHERE Need_ID = ?";

        $mailMessage = $this->_("Cher") . " " . $this->getprenom() . " " . $this->getnom() . ", \n\n
            " . $this->_("Un compte vient de vous etre créé sur shinbuntu.com.") . " \n
            " . $this->_("Merci de cliquer sur le lien suivant pour accéder au site web http://shinbuntu.com/player/ et confirmer ainsi la création de votre compte:") . " \n
                    " . Registry::get("url") . "auth/activation.html?activationcode=" . $this->activation . "
            \n\n
            " . $this->_("Si le lien ci-dessus ne fonctionne pas, merci de le copier dans une nouvelle fenêtre de votre navigateur") . ".
            \n\n
            " . $this->_("Si vous avez des questions, ou si vous avez besoin d’assistance, n’hésitez pas à nous contacter à l’adresse électronique ") . Registry::get("mailcontact") . "
            \n\n
            " . $this->_("Merci") . ",
            \n\n
            shintaro@shinbuntu.com";




        /**/
        $to = $this->mail;
        $subject = $this->_("Activation de votre compte");
        $message = $mailMessage;
        $headers = 'From: ' . Registry::get("mailnotification") . "\r\n" .
                'Reply-To: ' . Registry::get("mailnotification") . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
        Tools::mail_utf8($to, $subject, $message, $headers);
        /**/
    }

    public function AccountActivation($activation) {
        $db = Registry::get("db");
        if (strlen($activation) != 15) {
            $this->error[] = "Mauvais code";
            return false;
        }
        $sql = "SELECT * FROM user WHERE activation= :activation";
        $prepareQuery = $db->prepare($sql);
        $prepareQuery->bindParam(':activation', $activation, PDO::PARAM_STR);
        $prepareQuery->execute();
        if (count($prepareQuery->fetchAll()) == 1) {
            $sql = "UPDATE user SET activation='' WHERE activation=:activation";
            $prepareQuery = $db->prepare($sql);
            $prepareQuery->bindParam(':activation', $activation, PDO::PARAM_STR);
            $prepareQuery->execute();
            return true;
        } else {
            $this->error[] = "Mauvais code";
            return false;
        }
    }

    public function NewPassword($mail) {
        $db = Registry::get("db");

        //On verifie le mail
        if (!preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $mail)) {

            $this->error[] = "Mauvaise adresse mail";

            return false;
        }

        $sql = "SELECT id, activation FROM user WHERE mail=?";
        $prepareQuery = $db->prepare($sql);
        $prepareQuery->execute(array($mail));

        $mail_present = $prepareQuery->fetchAll(PDO::FETCH_ASSOC);

        if (count($mail_present) != 1) {
            $this->error = "Adresse mail introuvable";
            return false;
        } else {

            if ($mail_present[0]['activation'] != "") {
                $this->error = "Compte pas encore activé";
                return false;
            }

            $this->mail = $mail;
            $this->Load_from_key($mail_present[0]['id']);

            $newpass = $this->GeneratePassword(10);
            $this->password = md5(sha1($newpass));
            $this->SendNewPassword($newpass);
            $this->Save_Active_Row();
            return true;
        }
    }

    public function GeneratePassword($size) {
        // Initialisation des caractères utilisables
        $characters = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");
        $password = "";
        for ($i = 0; $i < $size; $i++) {
            $password .= ($i % 2) ? strtoupper($characters[array_rand($characters)]) : $characters[array_rand($characters)];
        }

        return $password;
    }

    public function SendNewPassword($npass) {
        $mailMessage = $this->_("Bonjour") . ", \n\n" . $this->_("Voici votre nouveau mot de passe :") . " " . $npass;
        //echo '#TEST : '.$mailMessage;
        //TODO FINIR ENVOI MAIL
        /**/
        $to = $this->mail;
        $subject = $this->_("Demande d'un nouveau mot de passe");
        $message = $mailMessage;
        $headers = 'From: ' . Registry::get("mailnotification") . "\r\n" .
                'Reply-To: ' . Registry::get("mailnotification") . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
        Tools::mail_utf8($to, $subject, $message, $headers);
        /**/
    }

    /**
     * @return id - int(11)
     */
    public function getid() {
        return $this->id;
    }

    public function setpassword($pass, $pass2) {

        if (strlen($pass) < 4) {

            $this->error[] = "Mot de passe trop court";

            return false;
        }

        if (strlen($pass) > 12) {

            $this->error[] = "Mot de passe trop long";

            return false;
        }

        if ($pass != $pass2) {

            $this->error[] = "Mot de passe et confirmation de mot de passe différents";

            return false;
        }

        $pass = htmlentities($pass, ENT_QUOTES, "UTF-8");

        $pass = md5(sha1($pass));

        $this->password = $pass;

        return true;
    }

    public function setactivation() {
        $db = Registry::get("db");
        $sql = "SELECT * FROM user WHERE activation= :activation";
        $uniqueActivation = false;
        while (!$uniqueActivation) {
            $this->activation = Tools::random(15);
            $prepareQuery = $db->prepare($sql);
            $prepareQuery->bindParam(':activation', $this->activation, PDO::PARAM_STR);
            $prepareQuery->execute();
            if (count($prepareQuery->fetchAll()) == 0)
                $uniqueActivation = true;
        }
    }

    /**
     * @return nom - varchar(100)
     */
    public function getnom() {
        return $this->nom;
    }

    /**
     * @return nom - varchar(200)
     */
    public function getnomprenom() {
        return ucfirst($this->nom) . ' ' . ucfirst($this->prenom);
    }

    /**
     * @return prenom - varchar(100)
     */
    public function getprenom() {
        return $this->prenom;
    }

    /**
     * @return societe - varchar(100)
     */
    public function getsociete() {
        return $this->societe;
    }

    /**
     * @return adresse - varchar(255)
     */
    public function getadresse() {
        return $this->adresse;
    }

    public function getAdresseComplete() {
        return $this->adresse . " " . $this->cp . " " . $this->ville;
    }

    /**
     * @return ville - varchar(255)
     */
    public function getville() {
        return $this->ville;
    }

    /**
     * @return cp - varchar(12)
     */
    public function getcp() {
        return $this->cp;
    }

    /**
     * @return pays - varchar(255)
     */
    public function getpays() {
        return $this->pays;
    }

    /**
     * @return tel - varchar(12)
     */
    public function gettel() {
        return $this->tel;
    }

    /**
     * @return mail - varchar(255)
     */
    public function getmail() {
        return $this->mail;
    }

    /**
     * @return date_crea - datetime
     */
    public function getdate_crea() {
        return $this->date_crea;
    }

    public function getAdmin() {
        return $this->admin;
    }

    /**
     * @param Type: int(11)
     */
    public function setid($id) {
        $this->id = $id;
    }

    /**
     * @param Type: varchar(100)
     */
    public function setnom($nom) {
        $nom = strip_tags($nom);
        if (strlen($nom) < 2) {
            $this->error[] = "Nom de famille trop court";
            return false;
        }
        $this->nom = $nom;
    }

    /**
     * @param Type: varchar(100)
     */
    public function setprenom($prenom) {
        $prenom = strip_tags($prenom);
        if (strlen($prenom) < 2) {
            $this->error[] = "Prénom trop court";
            return false;
        }
        $this->prenom = $prenom;
    }

    /**
     * @param Type: varchar(100)
     */
    public function setsociete($societe) {
        $societe = strip_tags($societe);
        if (strlen($societe) > 100) {
            $this->error[] = "Nom de société trop long (100 caractères maximum)";
            return false;
        }
        $this->societe = $societe;
    }

    /**
     * @param Type: varchar(255)
     */
    public function setadresse($adresse) {
        $adresse = strip_tags($adresse);
        $this->adresse = $adresse;
    }

    /**
     * @param Type: varchar(255)
     */
    public function setville($ville) {
        $ville = strip_tags($ville);
        if (strlen($ville) == 0) {
            $this->error[] = "Le champ ville est requis";
            return false;
        }

        $this->ville = $ville;
    }

    /**
     * @param Type: varchar(12)
     */
    public function setcp($cp) {
        $cp = strip_tags($cp);

        if (strlen($cp) == 0) {
            $this->error[] = "Le champ code postal est requis";
            return false;
        }

        if (strlen($cp) > 5) {
            $this->error[] = "Code postal trop long (5 caractères maximum)";
            return false;
        }

        if (!ctype_digit($cp)) {
            $this->error[] = "Code postal invalide, chiffres uniquement";
            return false;
        }

        $this->cp = $cp;
    }

    /**
     * @param Type: varchar(255)
     */
    public function setpays($pays) {
        $pays = strip_tags($pays);
        if (strlen($pays) == 0) {
            $this->error[] = "Le champ pays est requis";
            return false;
        }
        $this->pays = $pays;
    }

    /**
     * @param Type: varchar(30)
     */
    public function settel($tel) {
        $tel = strip_tags($tel);

        if (strlen($tel) == 0) {
            $this->error[] = "Le champ téléphone est requis";
            return false;
        }

        if (strlen($tel) > 30) {
            $this->error[] = "Téléphone trop long (30 caractères maximum)";
            return false;
        }

        if (!ctype_digit($tel)) {
            $this->error[] = "Téléphone invalide, chiffres uniquement";
            return false;
        }
        $this->tel = $tel;
    }

    /**
     * @param Type: varchar(255)
     */
    public function setmail($mail) {
        $db = Registry::get("db");

        $email = htmlentities($mail, ENT_QUOTES, "UTF-8");

        //On verifie le mail
        if (!preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $email)) {

            $this->error[] = "Mauvaise adresse mail";

            return false;
        }

//		$sql="SELECT User_email FROM UserBlackList WHERE User_email=?";
//		$prepareQuery = $db->prepare($sql);
//		$prepareQuery->execute(array($email));
//
//		$email_blacklist = $prepareQuery->fetchAll(PDO::FETCH_ASSOC);
//
//                if (count($email_blacklist)==1) {
//                    $this->error[] = "Adresse mail blacklistée";
//                    return false;
//                }

        $sql = "SELECT mail FROM user WHERE mail=?";
        $prepareQuery = $db->prepare($sql);
        $prepareQuery->execute(array($email));

        $email_present = $prepareQuery->fetchAll(PDO::FETCH_ASSOC);

        if (count($email_present) == 1) {
            $this->error[] = "Adresse mail déja utilisée";
            return false;
        }

        $this->mail = $email;

        return true;
    }

    /**
     * @param Type: datetime
     */
    public function setdate_crea($date_crea = null) {
        if ($date_crea == "")
            $date_crea = Date('Y-m-d H:i:s');

        $this->date_crea = $date_crea;
    }

    public function getError() {

        return $this->error;
    }

}