<?php
/**
 * Created by PhpStorm.
 * User: henrik
 * Date: 19.04.2018
 * Time: 22:56
 */

class subpage_object_list  extends subpage{

    function getContent($page)
    {
        $this->title = "Datenübersicht";
        $page->setHeader($this->title);

        $c1 = "...";

        $result = $this->card($c1,"Organisatorische Maßnahmen zur Sicherung der Verarbeitung");


        return $result;
    }
}