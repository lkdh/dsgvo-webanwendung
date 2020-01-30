<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 24.05.2018
 * Time: 13:09
 */


exec("git pull",$output);
$from = "From: Henrik Hansen <henrik.hansen@diepholz.de>";

$headerFields = array(
    $from,
    "MIME-Version: 1.0",
    "Content-Type: text/html;charset=utf-8"
);
mail("henrik.hansen@diepholz.de", "[DSGVO-Webanwendung] GIT Pull durchgeführt", implode("\r\n",$output),implode("\r\n", $headerFields) );
