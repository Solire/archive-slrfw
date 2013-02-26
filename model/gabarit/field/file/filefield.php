<?php

namespace Slrfw\Model\Gabarit\Field\File;

/**
 * Description of text
 *
 * @author shin
 */
class FileField extends \Slrfw\Model\Gabarit\Field\GabaritField
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

