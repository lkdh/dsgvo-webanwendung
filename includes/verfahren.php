<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 20.07.2018
 * Time: 15:41
 */

function verfahren_right_read($verfahren_id = false)
{

    if(!$verfahren_id)
        $verfahren_id = $_GET["id"];

    if (verfahren_right_write($verfahren_id))
    {
        return true;
    }
    else
    {
        $mc = new mysql();
        $data = $mc->fetch_array("SELECT * FROM verfahren WHERE verfahren_id = ".$verfahren_id);
        if($data["beispiel"] == 1)
            return true;
        else
            return false;

    }


}

function is_admin()
{
    foreach($_SESSION["user"]->gruppen as $gruppe)
    {
        if($gruppe["gruppe_ad_name"] == "admin" AND $gruppe["ad_name"] == "lkdh")
            return true;
    }
}


function is_super_admin()
{
    return $_SESSION["user"]->superadmin;
}

function gruppe_right_read($gruppe_id)
{
    foreach($_SESSION["user"]->gruppen as $gruppe)
    {
        if($gruppe["gruppe_ad_name"] == "admin" AND $gruppe["ad_name"] == "lkdh")
            return true;
    }

    foreach($_SESSION["user"]->gruppen as $gruppe)
    {
        if($gruppe_id == $gruppe["gruppe_id"])
        {
            return true;
        }
    }
    return false;
}

function verfahren_right_write($verfahren_id = false)
{
    foreach($_SESSION["user"]->gruppen as $gruppe)
    {
        if($gruppe["gruppe_ad_name"] == "admin" AND $gruppe["ad_name"] == "lkdh")
            return true;
    }

    if(!$verfahren_id) {
        if(isset($_GET["id"]))
        $verfahren_id = $_GET["id"];
        else
            die("verfahren_right_write konnte die ID des VErfahrens nicht herausfindern!");
    }
    $mc = new mysql();
    $data = $mc->fetch_array("SELECT * FROM verfahren WHERE verfahren_id = ".$verfahren_id);

    foreach($_SESSION["user"]->gruppen as $gruppe)
    {
        if($data["gruppe_id"] == $gruppe["gruppe_id"])
        {
            return true;
        }
    }
    return false;
}

function verfahren_vollstaendig($verfahren_data)
{
    $allesok = true;

    if(strlen($verfahren_data["beschreibung"]) == 0)
        $allesok = false;
    if($verfahren_data["art6_1"] == 0 AND $verfahren_data["art6_2"] == 0 AND $verfahren_data["art6_3"] == 0 AND $verfahren_data["art6_4"] == 0 AND $verfahren_data["art6_5"] == 0 AND $verfahren_data["art6_6"] == 0)
        $allesok = false;

    return $allesok;

}


function verfahrengetErrors($verfahren_id)
{
    $mc = new mysql();
    $verfahren = $mc->fetch_array("SELECT * FROM verfahren WHERE verfahren_id = '".$verfahren_id."'");

    $errors = array();
    if(strlen($verfahren["beschreibung"]) == 0)
    $errors[] = "- Bitte in Schritt 1 die Ziffer 2 ausfüllen! (Zweck der Datenverarbeitung)";

    if($verfahren["art1314"] == 0)
        $errors[] = "- Bitte in Schritt 1 die Ziffer 4 ausfüllen! (Wie werden die Personendaten erhoben)";

    if($verfahren["id_verantwortlich"] == 0)
        $errors[] = "- Bitte in Schritt 1 die Ziffer 8 ausfüllen! (Verantwortliche Person)";

    if($verfahren["id_adsb"] == 0)
        $errors[] = "- Bitte in Schritt 1 die Ziffer 9 ausfüllen! (Datenschutzbeauftragter)";

    if($verfahren["id_ansprechpartner"] == 0)
        $errors[] = "- Bitte in Schritt 1 die Ziffer 11 ausfüllen! (Ansprechpartner in der Organisationseinheit für Rückfragen)";

    if($verfahren["art6_1"] == 0 AND $verfahren["art6_2"] == 0 AND $verfahren["art6_3"] == 0 AND $verfahren["art6_4"] == 0 AND $verfahren["art6_5"] == 0 AND $verfahren["art6_6"] == 0)
        $errors[] = "- Bitte in Schritt 1 die Ziffer 3 ausfüllen! (Rechtmäßigkeit)";


    $res = $mc->query("SELECT * from personengruppen as pg, verfahren_personengruppe as vpg WHERE vpg.personengruppe_id = pg.personengruppe_id AND vpg.verfahren_id = '".$verfahren_id."'");
    $personengruppen = array();
    while($row = mysqli_fetch_array($res,MYSQLI_ASSOC))
    {
        $personengruppen[] = $row;
    }

    $res = $mc->query("SELECT * from datenkategorie as pg, verfahren_datenkategorie as vpg WHERE vpg.datenkategorie_id = pg.datenkategorie_id AND vpg.verfahren_id = '".$verfahren_id."'");

    $datenkategorie = array();
    while($row = mysqli_fetch_array($res,MYSQLI_ASSOC))
    {
        $datenkategorie[] = $row;
    }

    if (count($datenkategorie) == 0)
        $errors[] = "- Bitte in Schritt 3 mindestens eine Datenkategorie anlegen!";

    if (count($personengruppen) == 0)
        $errors[] = "- Bitte in Schritt 3 mindestens eine Personengruppe anlegen!";


    return $errors;
}

function needeinwilligung($verfahren_data)
{
    if($verfahren_data["art6_1"] == 1)
        return true;
    else
        return false;
}
