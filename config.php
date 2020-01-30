<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 21.03.2018
 * Time: 12:17
 */


include("../config.php");

define("FPDF_FONTPATH","fonts/");
foreach(scandir("includes/",SCANDIR_SORT_ASCENDING ) as $file)
{
    if($file != "." AND $file != "..")
    {
        if(is_file("includes/".$file))
        include("includes/".$file);
    }
}

foreach(scandir("subpage/") as $file)
{
    if($file != "." AND $file != "..")
    {
        include("subpage/".$file);
    }
}

$mc = new mysql();
if(!$mc->connected)
{
   echo alert("Datenbankfehler!","Es konnte keine Verbindung zur Datenbank <b>".MYSQL_DATENBANK."</b> hergestellt werden.","error");
   die();
}

include("ftpconfig/".$_SESSION["user"]->organisation_adname.".php");