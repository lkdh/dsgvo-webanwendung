<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 11.04.2018
 * Time: 17:32
 */


function add_protokoll($action,$object_id,$object_typ,$old_value = "",$new_value = "",$value_key = "")
{

    if(!isset($_SESSION["user"]->userid))
    {
        $sid = -1;
    }
    else
        $sid = $_SESSION["user"]->userid;


    $sql = "INSERT into protokoll (time_created,action,object_id,object_typ,old_value,new_value,user_id,value_key) VALUES 
        ('".time()."','".$action."','".$object_id."','".$object_typ."','".$old_value."','".$new_value."','".$sid."','".$value_key."')";
    $ms = new mysql();
    $ms->query($sql);
}