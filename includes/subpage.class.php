<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 21.03.2018
 * Time: 17:27
 */

class subpage{
    var $title;
    var $sql;

    function subpage(){
        $this->sql = new mysql();
    }

    function card($content, $title = false, $subtitle = false,$hidden = false,$id = false)
    {
        $controlname = preg_replace("/[^a-zA-Z]/", "", $title);


        if(!$id)
            $id = strtolower($controlname);


        if($hidden)
            $str_hidden = "style= \"display:none;\"";
        else
            $str_hidden = "";

        $ret = "<div id=\"".$id."\" class=\"card\" ".$str_hidden.">
                    <div class=\"card-body\">";
        if ($title)
            $ret .= "<h5 class=\"card-title\">" . $title . "</h5>";
        if ($subtitle)
            $ret .= "<p class=\"card-subtitle mb-2\">" . $subtitle . "</p>";
        $ret .= $content;
        $ret .= "</div></div>";
        return $ret;
    }

    function getAsyncContent($str_class,$str_function,$data = false)
    {
        return "<script>
            function reloadpage(){
            ajax_getasync_Content('" . $str_class . "','" . $str_function . "','".$data."');
            }
            reloadpage();
        </script><div id='subcontentarea'></div>";
    }

}