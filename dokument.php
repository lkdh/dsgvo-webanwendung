<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 06.11.2018
 * Time: 16:10
 */
session_start();
include("config.php");
ini_set("display_errors","1");
$mc = new mysql();
$doc = $mc->fetch_array("SELECT * FROM dokumente WHERE dokument_id = '".(int)$_GET["id"]."' AND deleted = 0");
if(isset($doc["dokument_id"]))
{
    if(file_exists(UPLOAD_FOLDER . $doc["dokument_id"] . "." . $doc["extension"])) {
        header('Content-Type: ' . mime_content_type(UPLOAD_FOLDER . $doc["dokument_id"] . "." . $doc["extension"]));
     #   echo  mime_content_type(UPLOAD_FOLDER . $doc["dokument_id"] . "." . $doc["extension"]);
        $pdf = file_get_contents(UPLOAD_FOLDER . $doc["dokument_id"] . "." . $doc["extension"]);
       echo $pdf;
    }
    else
        echo "File not found!";
}