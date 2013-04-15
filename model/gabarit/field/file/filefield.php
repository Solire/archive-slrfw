<?php

namespace Slrfw\Model\Gabarit\Field\File;

/**
 * Description of text
 *
 * @author shin
 */
class FileField extends \Slrfw\Model\Gabarit\Field\GabaritField {

    public function start() {
        parent::start();
        $this->isImage = false;
        if ((isset($this->params["CROP.WIDTH.MIN"]) && intval($this->params["CROP.WIDTH.MIN"]) > 0 ) ||
                (isset($this->params["CROP.HEIGHT.MIN"]) && intval($this->params["CROP.HEIGHT.MIN"]) > 0)) {
            $this->champ["aide"] .= '<div style="display:inline-block">';
            if ((isset($this->params["CROP.WIDTH.MIN"]) && intval($this->params["CROP.WIDTH.MIN"]) > 0)) {
                $this->champ["aide"] .= '<dl class="dl-horizontal expected-width">
                                    <dt style="width: 180px;">Largeur</dt>
                                    <dd style="margin-left: 190px;"><span id="">' . $this->params["CROP.WIDTH.MIN"] . '</span>px</dd>
                                </dl>';
            }
            if ((isset($this->params["CROP.HEIGHT.MIN"]) && intval($this->params["CROP.HEIGHT.MIN"]) > 0)) {
                $this->champ["aide"] .= '<dl class="dl-horizontal expected-height">
                                    <dt style="width: 180px;">Hauteur</dt>
                                    <dd style="margin-left: 190px;"><span id="">' . $this->params["CROP.HEIGHT.MIN"] . '</span>px</dd>
                                </dl>';
            }

            $this->champ["aide"] .= '</div>';
        }

        $ext = strtolower(array_pop(explode(".", $this->value)));
        if (array_key_exists($ext, \Slrfw\Model\fileManager::$_extensions['image'])) {
            $this->isImage = true;
        }
    }

}

