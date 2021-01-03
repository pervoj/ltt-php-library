<?php
/*
 * Copyright (C) 2020 Vojtěch Perník <v.pernik@centrum.cz>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @author Vojtěch Perník <v.pernik@centrum.cz>
 */
class LTTGetter {
    private $trDir;
    private $translation;
    private $code;
    private $lang;
    private $author;
    private $pattern;
    private $translations = array();
    private $patterns = array();

    /**
     * Constructor of the official LTT library for PHP
     * @param $trDir string Path to the directory, where are translation located
     * @param $code string Language code
     */
    public function __construct($trDir, $code) {
        $this->trDir = $trDir;
        $this->code = $code;
        $this->translation = $trDir . "/" . $code . ".ltt";

        if (!file_exists($this->translation)) {
            foreach (glob($trDir . "/*.lttp") as $file) {
                $this->pattern = $file;
                break;
            }
            foreach (explode("\n", file_get_contents($this->pattern)) as $s) {
                if (substr($s, 0, strlen("; Author:")) === "; Author:") {
                    if (sizeof(explode(":", $s)) >= 2) {
                        $this->author = explode(":", $s)[1];
                    }
                } elseif (substr($s, 0, strlen(";")) !== ";") {
                    $singlecode = explode("=", $s)[0];
                    $singlepattern = "";
                    if (sizeof(explode("=", $s)) >= 2) {
                        $singlepattern = explode("=", $s)[1];
                    }
                    $this->translations[$singlecode] = "";
                    $this->patterns[$singlecode] = $singlepattern;
                }
            }
        } else {
            $translations = array();
            $patterns = array();

            foreach (explode("\n", file_get_contents($this->translation)) as $s) {
                if (substr($s, 0, strlen("; Language:")) === "; Language:") {
                    if (sizeof(explode(":", $s)) >= 2) {
                        $this->lang = explode(":", $s)[1];
                    }
                } elseif (substr($s, 0, strlen("; Author:")) === "; Author:") {
                    if (sizeof(explode(":", $s)) >= 2) {
                        $this->lang = explode(":", $s)[1];
                    }
                } elseif (substr($s, 0, strlen("; Pattern:")) === "; Pattern:") {
                    if (sizeof(explode(":", $s)) >= 2) {
                        $this->pattern = $trDir . "/" . explode(":", $s)[1];
                    }
                } elseif (substr($s, 0, strlen(";")) !== ";") {
                    $translations[] = $s;
                }
            }

            if (!empty($this->pattern)) {
                foreach (explode("\n", file_get_contents($this->pattern)) as $s) {
                    if (substr($s, 0, strlen(";")) !== ";") {
                        $patterns[] = $s;
                    }
                }

                foreach ($patterns as $pattern) {
                    $singlecode = explode("=", $pattern)[0];

                    $singlepattern = "";
                    if (sizeof(explode("=", $pattern)) >= 2) {
                        $singlepattern = explode("=", $pattern)[1];
                    }

                    $singletranslation = "";
                    foreach ($translations as $translation) {
                        if (explode("=", $translation)[0] == $singlecode && sizeof(explode("=", $translation)) >= 2) {
                            $singletranslation = explode("=", $translation)[1];
                            break;
                        }
                    }

                    $this->patterns[$singlecode] = $singlepattern;
                    $this->translations[$singlecode] = $singletranslation;
                }
            } else {
                foreach ($translations as $translation) {
                    $singlecode = explode("=", $translation)[0];
                    $singletranslation = "";
                    if (sizeof(explode("=", $translation)) >= 2) {
                        $singletranslation = explode("=", $translation)[1];
                    }
                    $this->patterns[$singlecode] = "";
                    $this->translations[$singlecode] = $singletranslation;
                }
            }
        }
    }

    /**
     * Getter of one pattern or translation
     * @param $code string Translation code
     * @return string Pattern or translation
     */
    public function getText($code) {
        if (!empty($this->translations[$code])) {
            $text = $this->translations[$code];
        } else {
            $text = $this->patterns[$code];
        }
        return $this->replace($text);
    }

    private function replace($text) {
        $text = str_replace("{[equal sign]}", "=", $text);
        $text = str_replace("{[semicolon]}", ";", $text);
        $text = str_replace("{[opening curly brace]}", "{", $text);
        $text = str_replace("{[closing curly brace]}", "}", $text);
        return $text;
    }
}