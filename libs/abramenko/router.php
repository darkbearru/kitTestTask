<?php

namespace abramenko;

/**
 * class Router
 * 
 * Реализация REST API
 */
class Router 
{
    private $_routerPaths;
    private $_parent;
    private $_default;

    /**
     * Конструктор
     * Передаётся, родительский класс чьи методы вызываются
     * А также метод вызываемый по умолчанию
     */
    public function __construct ($parent, $default = false) 
    {
        $this->_routerPaths = [];
        if (!is_object ($parent)) return false;
        
        $this->_parent = $parent;

        if (!empty ($default)) {
            if (method_exists ($parent, $default)) {
                $this->_default = $default;
            }
        }
    }


    /**
     * Метод PUT,
     * Используется для изменения существующих данных
     */
    public function put ($path, $action) 
    {
        $this->addPath ('PUT', $path, $action);
    }

    /**
     * Метод GET
     * Используется для получаения каких либо данных
     */
    public function get ($path, $action) 
    {
        $this->addPath ('GET', $path, $action);
    }

    /**
     * Метод POST
     * Используется для создания / внесения данных
     */
    public function post ($path, $action) 
    {
        $this->addPath ('POST', $path, $action);
    }

    /**
     * Метод Delete
     * Используется для удаления данных
     */
    public function delete ($path, $action) 
    {
        $this->addPath ('DELETE', $path, $action);
    }

    /**
     * Запускаем роутинг и вызываем в случае необходимости нужный метод
     */
    public function run ()
    {
        $method = !empty ($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : 'GET';
        $url = !empty ($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $params = explode ('?', $url);
        $url = $params[0];
        $params = count ($params) > 1 ? explode ('&', $params[1]) : [];

        $result = $this->processPath ($method, $url, $params);
        if ($result) return $result;

        return $this->_parent->{$this->_default} ($params);
    }

    /**
     * Добавляем нужный путь, обработки необходимого метода, а также наименование своего метода
     */
    protected function addPath ($method, $path, $action)
    {
        if (!empty ($this->_routerPaths[$method])) $this->_routerPaths[$method] = [];

        $this->_routerPaths[$method][$path] = $action;
    }

    /**
     * Вызов нужного метода для необходимого пути
     */
    protected function processPath ($method, $url, $params) 
    {
        if (empty ($this->_routerPaths[$method])) return false;
        if (empty ($this->_routerPaths[$method][$url])) return false;

        $action = $this->_routerPaths[$method][$url];

        return  $this->_parent->{$action} ($params);
    }
}