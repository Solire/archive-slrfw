
<?php

/*
---  ---  ---  ---  ---  ---  ---  ---  ---  ---  --- ---  ---  ---  ---  ---  ---  ---  ---  ---  ---  --- ---  ---  ---  ---  

oooooooooo.         .o.       ooooooooooooo       .o.       ooooooooooooo       .o.       oooooooooo.  ooooo        oooooooooooo 
`888'   `Y8b       .888.      8'   888   `8      .888.      8'   888   `8      .888.      `888'   `Y8b `888'        `888'     `8 
 888      888     .8"888.          888          .8"888.          888          .8"888.      888     888  888          888         
 888      888    .8' `888.         888         .8' `888.         888         .8' `888.     888oooo888'  888          888oooo8    
 888      888   .88ooo8888.        888        .88ooo8888.        888        .88ooo8888.    888    `88b  888          888    "    
 888     d88'  .8'     `888.       888       .8'     `888.       888       .8'     `888.   888    .88P  888       o  888       o 
o888bood8P'   o88o     o8888o     o888o     o88o     o8888o     o888o     o88o     o8888o o888bood8P'  o888ooooood8 o888ooooood8 
 
 
---  ---  ---  ---  ---  ---  ---  ---  ---  ---  --- ---  ---  ---  ---  ---  ---  ---  ---  ---  ---  --- ---  ---  ---  ---  
 */
        
        
        
        
/**
    _______   __ ________  _________ _      _____ 
    |  ___\ \ / /|  ___|  \/  || ___ \ |    |  ___|
    | |__  \ V / | |__ | .  . || |_/ / |    | |__  
    |  __| /   \ |  __|| |\/| ||  __/| |    |  __| 
    | |___/ /^\ \| |___| |  | || |   | |____| |___ 
    \____/\/   \/\____/\_|  |_/\_|   \_____/\____/ 
        
 */

$config = array(
    "extra" => array( 
        "copy"              => false, //bool Activer la fonctionnalité de copie des données
        "print"             => false, //bool Activer la fonctionnalité de copie des données'impression
        "pdf"               => false, //bool Activer la fonctionnalité d'export pdf
        "csv"               => false, //bool Activer la fonctionnalité d'export csv
        "hide_columns"      => false, //bool Permettre de caché des colonnes
        "highlightedSearch" => true,  //bool mise en surbrillance des termes de recherche
    ),
    "table" => array(
        "name"          => "table_name",            //string Nom de la table à lister
        "title"         => "Liste des contenus",    //string Titre de la page
        "title_item"    => "contenu",               //string Nom des items listés
        "suffix_genre"  => "",                      //string Suffixe genre (exemple: e)
        "fixedheader"   => false,                   //bool header de tableau fixed
    ),
    "columns" => array(  //Définition des colonnes
        //Colonne simple
        array(
            "name"          => "id",    //string Nom de la colonne
            "index"         => true,    //bool Champs indexé (Clé primaire)
            "show"          => true,    //bool Afficher dans le tableau
            "filter_field"  => "text",  //string Type champs de filtre (text/select/date-range)
            "title"         => "Titre", //string Titre affiché dans le header du tableau pour cette colonne
        ),
        //Colonne 1..1 sur autre table
        array(
            "name"  => "id_client",         //string Nom de la colonne
            "from"  => array( 
                "table"   => "gab_gabarit", //string Nom de la table jointe
                "columns" => array(
                    array(
                        "name" => "label",  //string Nom de la colonne dans la table jointe
                    ),
                ),
                "index" => array(
                    "id" => "THIS",         //string Nom de la colonne sur laquelle on joins
                )
            ),
            "show"         => true,
            "filter_field" => "select",     //string Type champs de filtre (text/select/date-range)
            "title"        => "Type de contenu",
        ),
        //Colonne simple formaté
        array(
            "name" => "date_crea",
            "php_function" => array(
                "Tools::RelativeTimeFromDate" //string Fonction statique php à appeler pour chaque valeur
            ),
            "show" => true,
            "filter_field" => "date-range",   //string Type champs de filtre (text/select/date-range)
            "filter_field_date_past" => true, //bool date seulement passé pour le filtre sur la date 
            "title" => "Créé",
        ),
        //Colonne simple (non affichée) avec filtre général
        array(
            "name" => "id_version",
            "index" => true,
            "filter" => BACK_ID_VERSION,    //mixed Permet de filtrer tous les résultats
        ),
        //Colonne avancée générée par une fonction + SQL avancé (Permet le filtre dans ce cas de figure)
        array(
            "special" => "buildAction", 
            "sql" => "IF(`gab_page`.`visible` = 0, '&#10005; Non visible', '&#10003; Visible')",
            "filter_field" => "select",
            "show" => true,
            "title" => "Actions",
            "name" => "visible",
        ),
    ),
);


 
/**
 *  
    ___  ___  ___    _____ _____ _   _ ______ _____ _____ _   _______  ___ _____ _____ _____ _   _ 
    |  \/  | / _ \  /  __ \  _  | \ | ||  ___|_   _|  __ \ | | | ___ \/ _ \_   _|_   _|  _  | \ | |
    | .  . |/ /_\ \ | /  \/ | | |  \| || |_    | | | |  \/ | | | |_/ / /_\ \| |   | | | | | |  \| |
    | |\/| ||  _  | | |   | | | | . ` ||  _|   | | | | __| | | |    /|  _  || |   | | | | | | . ` |
    | |  | || | | | | \__/\ \_/ / |\  || |    _| |_| |_\ \ |_| | |\ \| | | || |  _| |_\ \_/ / |\  |
    \_|  |_/\_| |_/  \____/\___/\_| \_/\_|    \___/ \____/\___/\_| \_\_| |_/\_/  \___/ \___/\_| \_/

        |\ | _  _   . 
        | \|(_)|||  . utilisateur
                                                                 
 */

$config = array(
    'table' => array(
        'title' => 'Liste des utilisateurs',
        'title_item' => 'utilisateur',
        'suffix_genre' => '',
        'fixedheader' => false,
        'detail' => true,
        'name' => 'utilisateur',
    ),
    'extra' => array(
        'copy' => false,
        'print' => false,
        'pdf' => false,
        'csv' => false,
        'hide_columns' => false,
        'highlightedSearch' => false,
        'creable' => true,
        'editable' => true,
        'deletable' => true,
    ),
    'style' => array(
        'form' => 'bootstrap',
    ),
    'columns' => array(
        array(
            'name' => 'id',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Id',
            'index' => true,
        ),
        array(
            'name' => 'civilite',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Civilite',
            'creable_field' => array(
                "type" => "select",
                "options" => array(
                    array(
                        "value" => "M.",
                        "text"  => "M.",
                    ),
                    array(
                        "value" => "Mme",
                        "text"  => "Mme",
                    ),
                )
            ),
        ),
        array(
            'name' => 'nom',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Nom',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'prenom',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Prenom',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'societe',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Societe',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'fonction',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Fonction',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'email',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Email',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'pass',
            'show' => false,
            'filter_field' => 'text',
            'title' => 'Pass',
            'creable_field' => array(
                "type"       => "password",
                "encryption" => "SHA1",
            ),
        ),
        array(
            'name' => 'adresse',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Adresse',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'cp',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Cp',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'ville',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Ville',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'pays',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Pays',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'tel',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Tel',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'fax',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Fax',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'site',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Site',
        ),
        array(
            'name' => 'description',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Description',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'photo',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Photo',
        ),
        array(
            'name' => 'niveau',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Niveau',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'actif',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Actif',
            'creable_field' => array(
                "type" => "checkbox",
            ),
        ),
        array(
            'name' => 'lat',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Lat',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'lng',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Lng',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'date_crea',
            'show' => true,
            'filter_field' => 'text',
            'title' => 'Date_crea',
            'creable_field' => array(
                "type" => "text",
            ),
        ),
        array(
            'name' => 'suppr',
            'show_detail' => true,
            'filter_field' => 'text',
            'title' => 'Suppr',
            'creable_field' => array(
                "type" => "checkbox",
            ),
        ),
    ),
);