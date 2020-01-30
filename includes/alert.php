<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 20.04.2018
 * Time: 08:59
 */

function alertAJAX($title,$message,$type)
{
    return "<script>addAlert('".$title."','".$message."','".$type."');</script>";
}



function alert($title,$message,$type = "danger")
{
    return "<div class=\"alert alert-".$type." alert-dismissible fade show\" role=\"alert\">  
<strong>".$title."</strong><br>".$message."</div>";
}