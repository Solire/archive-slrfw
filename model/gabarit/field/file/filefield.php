<?php

require_once 'gabarit/field/gabaritfield.php';

/**
 * Description of text
 *
 * @author shin
 */
class FileField extends GabaritField
{
    public function start() {
        parent::start();
        $this->isImage = false;
        $ext = strtolower(array_pop(explode(".", $this->value)));
        if (array_key_exists($ext, \Slrfw\Model\fileManager::$_extensions['image'])) {
            $this->isImage = true;
        }
    }
}

