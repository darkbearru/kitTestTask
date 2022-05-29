<?php

namespace abramenko;

class Posts
{
    private $_db;

    public function __construct ()
    {
        $this->_db = new DB;
    }

    public function Insert ($upid, $name, $description)
    {
        $results = $this->_db->Query ("insert into posts (upid, name, text, changed) values('{$upid}', '{$name}', '{$text}', now())");
        if (!$db->isError ()){
            $results = [
                "result" => "ok",
                "id"    => $db->insertID (),
                "upid"  => (!empty($upid) ? $upid : 0)
            ];
        } else {
            $results = ["error" => $db->errorsList ()];
        }
        return $results;
    }

    public function Update ($id, $upid, $name, $description)
    {
        $results = $this->_db->Query ("update posts set upid='{$upid}', name='{$name}', text='{$text}', changed=now() where id='{$id}'");
        if ($db->isError ()) {
            $results = ["error" => $db->errorsList ()];
        }
        return $results;
    }

    public function Delete ($id)
    {
        $recs = $this->_db->Query ("select id from posts where upid='{$id}'");
        foreach ($recs as $rec) {
            $this->Delete ($rec->id);
        }

        $this->_db->Query ("delete from posts where id='{$id}'");

        if ($db->isError ()) {
            $results = ["error" => $db->errorsList ()];
        }

    }

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

    public function htmlTree ($tree)
    {
        $html = "";
        foreach ($tree as $item)
        {
            $_description = addslashes ($item->description);
            $html .= "<li>";
            $html .= "<span data-id=\"{$item->id}\" data-upid=\"{$item->upid}\" data-name=\"{$item->name}\" data-description=\"{$_description}\">";
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