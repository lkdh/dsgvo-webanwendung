<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 01.11.2018
 * Time: 14:08
 */

include("config.php");
$verfahren_id = (int)$_GET["id"];
einwilligung($verfahren_id);