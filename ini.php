<?php

namespace Slrfw\Library;

/** @todo faire la présentation du code */

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of econfig
 *
 * @author shin
 */
class Ini
{

    static function read_ini_file($f, &$r)
    {
        $null = "";
        $r = $null;
        $first_char = "";
        $sec = $null;
        $comment_chars = ";#";
        $num_comments = "0";
        $num_newline = "0";

        //Read to end of file with the newlines still attached into $f
        $f = @file($f);
        if ($f === false) {
            return -2;
        }
        // Process all lines from 0 to count($f)
        for ($i = 0; $i < @count($f); $i++) {
            $w = @trim($f[$i]);
            $first_char = @substr($w, 0, 1);
            if ($w) {
                if ((@substr($w, 0, 1) == "[") and (@substr($w, -1, 1)) == "]") {
                    $sec = @substr($w, 1, @strlen($w) - 2);
                    $num_comments = 0;
                    $num_newline = 0;
                } else if ((stristr($comment_chars, $first_char) == true)) {
                    $r[$sec]["Comment_" . $num_comments] = $w;
                    $num_comments = $num_comments + 1;
                } else {
                    // Look for the = char to allow us to split the section into key and value
                    $w = @explode("=", $w);
                    $k = @trim($w[0]);
                    unset($w[0]);
                    $v = @trim(@implode("=", $w));
                    // look for the new lines
                    if ((@substr($v, 0, 1) == "\"") and (@substr($v, -1, 1) == "\"")) {
                        $v = @substr($v, 1, @strlen($v) - 2);
                    }

                    $r[$sec][$k] = $v;
                }
            } else {
                $r[$sec]["Newline_" . $num_newline] = $w;
                $num_newline = $num_newline + 1;
            }
        }
        return 1;
    }

    static function write_ini_file($path, $assoc_arr)
    {
        $content = "";

        foreach ($assoc_arr as $key => $elem) {
            if (is_array($elem)) {
                if ($key != '') {
                    $content .= "[" . $key . "]\r\n";
                }

                foreach ($elem as $key2 => $elem2) {
                    if (self::beginsWith($key2, 'Comment_') == 1 && self::beginsWith($elem2, ';')) {
                        $content .= $elem2 . "\r\n";
                    } else if (self::beginsWith($key2, 'Newline_') == 1 && ($elem2 == '')) {
                        $content .= $elem2 . "\r\n";
                    } else {
                        if($elem2 == "true" || $elem2 == "false")
                            $content .= $key2 . " = " . $elem2 . "\r\n";
                        else
                            $content .= $key2 . " = \"" . $elem2 . "\"\r\n";
                    }
                }
            } else {
                $content .= $key . " = " . $elem . "\r\n";
            }
        }

        if (!$handle = fopen($path, 'w')) {
            return -2;
        }
        if (!fwrite($handle, $content)) {
            return -2;
        }
        fclose($handle);
        return 1;
    }

    static function beginsWith($str, $sub)
    {
        return ( substr($str, 0, strlen($sub)) === $sub );
    }

}

?>
