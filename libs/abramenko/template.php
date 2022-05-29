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
        if (!empty ($data['json'])) $htmlFile = false;
        if (!file_exists ($this->_folder.$htmlFile) || !$htmlFile) {
            $this->returnJSON ($data);
        }
        $this->returnHtml ($data, $this->_folder.$htmlFile);
    }

    protected function returnJSON ($data)
    {
        $this->makeHeader (true);
        echo json_encode ($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function returnHTML ($data, $templateFile)
    {
        $this->makeHeader ();
        $file = implode ('', file ($templateFile));
        if (!empty ($data)) {
            $file = $this->placeVariables ($data, $file);
        }
        $file = $this->clearNotSet ($file);
        echo $file;
        exit;
    }

    protected function makeHeader ($isJSON = false) {
        header ('Content-type: '.($isJSON ? 'application/json' : 'text/html').'; charset=utf-8');
        header ('Last-Modified: '.date ('Y-m-d H:i:s'));
    }

    protected function placeVariables ($variables, $html)
    {
        $variables = (array) $variables;
        foreach ($variables as $key => $value) {
            if (is_string ($value) || is_numeric ($value)) {
                $html = preg_replace("/\{\{${key}\}\}/uim", $value, $html);
            } else {
                if (is_array ($value) || is_object ($value)) {
                    $value = (array) $value;
                    $html = $this->placeArray ($key, $value, $html);
                }
            }
        }
        return $html;
    }

    protected function placeArray ($key, $value, $html)
    {
        if (empty ($value)) {
            $html = preg_replace ("/\{\{(${key})\}\}(.*?)\{\{\/${key}\}\}/uis", '', $html);
        } else if (preg_match_all ("/\{\{(${key})\}\}(.*?)\{\{\/${key}\}\}/uis", $html, $matches, PREG_SET_ORDER)){
            $_html = '';
            foreach ($matches as $matchStr) {
                foreach ($value as $_key => $_val) {
                    if (!empty ($matchStr[2])) {
                        $_html .= $this->placeVariables ([$_key => $_val], $matchStr[2]);
                    }
                }
            }
            preg_replace ("/\{\{(${key})\}\}(.*?)\{\{\/${key}\}\}/uis", $_html, $html);
        }
        return $html;
    }

    protected function clearNotSet ($html)
    {
        return preg_replace('/\{\{(\/?[\w\-]{2,})\}\}/uis', '', $html); 
    }
}
