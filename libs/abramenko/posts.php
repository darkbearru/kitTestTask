<?php

namespace abramenko;

/**
 * Класс работы с постами
 */
class Posts
{
    private $_db;

    public function __construct ()
    {
        $this->_db = new DB;
    }

    /**
     * Добавление нового поста
     */
    public function Insert ($params)
    {
        $results = $this->_db->Query (
            "INSERT into posts (upid, name, description, changed) values('{$params->upid}', '{$params->name}', '{$params->description}', now())"
        );
        if (!$this->_db->isError ()){
            $results = [
                "result" => "ok",
                "id"    => $this->_db->insertID (),
                "upid"  => (!empty($params->upid) ? $params->upid : 0)
            ];
        } else {
            $results = ["error" => $db->errorsList ()];
        }
        return $results;
    }

    /**
     * Обновление данных поста
     */
    public function Update ($params)
    {
        $results = $this->_db->Query (
            "UPDATE posts set upid='{$params->upid}', name='{$params->name}', description='{$params->description}', changed=now() where id='{$params->id}'"
        );
        if (!$this->_db->isError ()){
            $results = [
                "result" => "ok",
                "upid"  => (!empty($params->upid) ? $params->upid : 0)
            ];
        } else {
            $results = ["error" => $this->_db->errorsList ()];
        }
        return $results;
    }

    /**
     * Удаления поста и всех связанных с ним детей
     */
    public function Delete ($id)
    {
        $recs = $this->_db->Query ("select id from posts where upid='{$id}'");
        foreach ($recs as $rec) {
            $this->Delete ($rec->id);
        }

        $this->_db->Query ("delete from posts where id='{$id}'");

        if (!$this->_db->isError ()){
            $results = [
                "result" => "ok",
            ];
        } else {
            $results = ["error" => $this->_db->errorsList ()];
        }
        return $results;
    }

    /**
     * Формируем полное дерево постов
     * Поскольку в задаче нет предпосылок того что дерево разрастётся до больших размеров, то
     * сначала получаем все записи одним запросом, а потом сворачиваем в дерево.
     */
    public function getTree ()
    {
        $results = $this->_db->Query ('select id, upid, name, description from posts order by id');
        if ($results) {
            $results = $this->collapseTree ($results);
        } else {
            if ($this->_db->isError ()) {
                $results = [
                    'error' => $this->_db->errorsList ()
                ];
            }
        }
        return $results;

    }


    /**
     * Поскольку мы работаем без нормального шаблонизатора,
     * то HTML дерево формируем отдельным кодом.
     */
    public function htmlTree ($tree)
    {
        $html = "";
        foreach ($tree as $item)
        {
            $_name = htmlspecialchars ($item->name);
            $_description = htmlspecialchars ($item->description);
            $html .= "<li>";
            $html .= "<span data-id=\"{$item->id}\" data-upid=\"{$item->upid}\" data-name=\"{$_name}\" data-description=\"{$_description}\">";
            $html .= "<i></i><a href=\"\">{$item->name}</a>";
            $html .= "</span>";
            if (!empty ($item->childs)) {
                $html .= "<ul>";
                $html .= $this->htmlTree ($item->childs);
                $html .= "</ul>";
            }
            $html .= "</li>";
        }
        return $html;
    }


    /**
     * Свор
     */
    private function collapseTree ($tree, $upid = 0)
    {
        $result = [];
        foreach ($tree as $item) {
            if ($item->upid == $upid) {
                $childs = $this->collapseTree ($tree, $item->id);
                if (!empty ($childs)) {
                    $item->childs = $childs;
                }
                $result[] = (object) $item;
            }
        }
        return $result;
    }

}