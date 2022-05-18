<?php

namespace abramenko;

/**
 * Простая реализация шаблонизатора
 */
class Template
{
    private $_folder;

    public function __construct ($folder = 'assets/templates')
    {
        $this->_folder = $_SERVER['DOCUMENT_ROOT']."/{$folder}/";
    }

    public function show ($data, $htmlFile = false)
    {
        if (!file_exists ($this->_folder.$htmlFile) || !$htmlFile) {
            $this->returnJSON ($data);
        }
        $this->returnHtml ($data, $this->_folder.$htmlFile);
    }

    private function returnJSON ($data)
    {
        $this->makeHeader (true);
        echo json_encode ($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function returnHTML ($data, $templateFile)
    {
        $this->makeHeader ();
        $_file = implode ('', file ($templateFile));
        echo $_file;
        exit;
    }

    private function makeHeader ($isJSON = false) {
        header ('Content-type: '.($isJSON ? 'application/json' : 'text/html').'; charset=utf-8');
        header ('Last-Modified: '.date ('Y-m-d H:i:s'));
    }
}
