<?

$config = array(
    "extra" => array(
        "copy" => false,
        "print" => false,
        "pdf" => false,
        "csv" => false,
        "hide_columns" => false,
    ),
    "table" => array(
        "name" => "gab_page",
        "title" => "Liste des contenus",
        "title_item" => "contenu",
        "suffix_genre" => "",
        "fixedheader" => false,
    ),
    "columns" => array(
        /* Champs requis pour les actions*/
        array(
            "name" => "id_gabarit",
        ),
        array(
            "name" => "id_version",
        ),
        array(
            "name" => "id",
        ),
        array(
            "name" => "visible",
        ),
        array(
            "name" => "rewriting",
        ),
        /* ****************************** */
        array(
            "name" => "titre",
            "index" => true,
            "show" => true,
            "filter_field" => "text",
            "title" => "Titre",
        ),
        array(
            "name" => "id_gabarit",
            "from" => array(
                "table" => "gab_gabarit",
                "columns" => array(
                    array(
                        "name" => "label",
                    ),
                ),
                "index" => array(
                    "id" => "THIS",
                )
            ),
            "show" => true,
            "filter_field" => "select",
            "title" => "Type de contenu",
        ), array(
            "name" => "date_crea",
            "php_function" => array(
                "Tools::RelativeTimeFromDate"
            ),
            "index" => true,
            "show" => true,
            "filter_field" => "text",
            "title" => "Créé",
        ),
        array(
            "name" => "date_modif",
            "php_function" => array(
                "Tools::RelativeTimeFromDate"
            ),
            "index" => true,
            "show" => true,
            "filter_field" => "text",
            "title" => "Édité",
            "default_sorting" => true,
            "default_sorting_direction" => "desc",
        ),
        array(
            "name" => "id_version",
            "index" => true,
            "filter" => BACK_ID_VERSION,
        ),
        array(
            "special" => "buildAction",
            "show" => true,
            "title" => "Actions",
        ),
        array(
            "name" => "suppr",
            "filter" => 0,
        ),
    ),
);