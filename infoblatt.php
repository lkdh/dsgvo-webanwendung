<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 02.05.2018
 * Time: 13:17
 */
include("config.php");
$verfahren_id = (int)$_GET["id"];

infoblatt($verfahren_id);
?>