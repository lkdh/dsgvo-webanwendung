<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 20.04.2018
 * Time: 08:30
 */


function getWhereVorlagen()
{
    $orgas = array("organisation_id = 0");
    $groups = array("gruppe_id = 0");
    foreach ($_SESSION['user']->gruppen as $key => $value) {
        $groups[] = "gruppe_id = " . $value["gruppe_id"];
        $orgas[] =  "organisation_id = " . $value["organisation_id"];
    }

    $sql = "(" . implode(" OR ", $orgas) . ") AND (" . implode(" OR ", $groups) . ")";
    return $sql;
}


function checked($value,$key)
{
    if($value == $key)
    {
        return " checked ";
    }
    else
        return "";
}
function disabled($value)
{
    if($value)
    {
        return " disabled ";
    }
    else
        return "";
}

function get_organisation_logo($orga_id)
{
    $mc = new mysql();
    $logo = $mc->fetch_array("SELECT * FROM dokumente where typ = 'logo' AND object_id = '".$orga_id."' AND deleted = 0");
    if(!isset($logo["dokument_id"]))
        return "";
    else
        return UPLOAD_FOLDER.$logo["dokument_id"].".".$logo["extension"];
}