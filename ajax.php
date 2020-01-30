<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 22.03.2018
 * Time: 13:42
 */

session_start();
include("config.php");

if(isset($_POST["method"])) {
    if (function_exists($_POST["method"])) {
        $fn = $_POST["method"];

        echo $fn($_POST);
    } else
        echo json_encode(array("status" => "0", "msg" => "Methode " . $_POST["method"] . " nicht gefunden!"));
}


if(isset($_POST["str_class"]) AND isset($_POST["str_function"]))
{
    $class = $_POST["str_class"];
    $fn = $_POST["str_function"];

    if(class_exists($class))
    {
        $cl = new $class();
        if(method_exists ($cl,$fn))
        {
            $dat = array();

            if(isset($_POST["extradata"])) {
                $dat["extradata"] = $_POST["extradata"];
            }

                if(isset($_POST["formdata"])) {
                $p = explode("&", $_POST["formdata"]);
                foreach ($p as $key => $value) {
                    $kv = explode("=", $value);
                    $dat[$kv[0]] = urldecode($kv[1]);
                }
            }
            else
                $dat = $_POST;

            echo $cl->$fn($dat);
        }
        else
            echo json_encode(array("status" => 0,"msg" => "Methode ".$fn." existiert nicht in Klasse ".$class));

    }
    else
    echo json_encode(array("status" => 0,"msg" => "Klasse ".$class." existiert nicht"));
}