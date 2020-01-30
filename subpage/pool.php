<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 22.03.2018
 * Time: 13:59
 */

class subpage_pool extends subpage {
    function getContent($page){
        $this->title = "Vorlagen";
        $ret = "<h1>Vorlagen / Pool</h1>";
        return $ret;
    }
}

