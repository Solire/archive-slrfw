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
        "detail"    =>  true,
        "name" => "traduction",
        "title" => "Edition des traductions",
        "title_item" => "traduction",
        "suffix_genre" => "e",
        "fixedheader" => true,
    ),
    "columns" => array(
        array(
            "name" => "cle",
            "index" => true,
            "show" => true,
            "filter_field" => "text",
            "title" => "Texte initial",
        ),
        array(
            "name" => "valeur",
            "editable" => true,
            "show_detail" => true,
            "filter_field" => "text",
            "title" => "Traduction",
        ),
        array(
            "name" => "id_version",
            "index" => true,
            "filter" => BACK_ID_VERSION,
        ),
    ),
);