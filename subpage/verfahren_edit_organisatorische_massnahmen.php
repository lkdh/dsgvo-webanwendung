<?php
/**
 * Created by PhpStorm.
 * User: henrik
 * Date: 19.04.2018
 * Time: 22:56
 */

class subpage_verfaren_edit_organisatorische_massnahmen   extends subpage{

    function modal_add_technische_m ($data)
    {

        $form = new form("massnahmen");
        $id = $data["data"];

        $form->add_textbox("Bezeichnung der organisatorischen Maßnahme","","","","text",false,verfahren_right_write($id));
        $form->add_plaintext("ODER (AUS VORHANDENER LISTE AUSWÄHLEN):");

        $form->add_hidden("id",$data["data"]);
        $mc = new mysql();

        $orgas = array("organisation_id = 0");
        $groups = array("gruppe_id = 0");

        foreach($_SESSION['user']->gruppen as $key => $value)
        {
            $groups[] = "gruppe_id = ".$value["gruppe_id"];
            $orgas[] = "organisation_id = ".$value["organisation_id"];
        }

        $sql = "SELECT * FROM massnahmen WHERE (".implode(" OR ",$orgas).") AND (".implode(" OR ",$groups).") AND type = 'organisatorisch' 
        AND massnahme_id NOT IN (SELECT massnahme_id FROM verfahren_massnahmen WHERE verfahren_id = '".$data["data"]."') AND massnahme_id NOT IN(SELECT massnahme_id FROM software_massnahmen as sm, verfahren_software as vs WHERE vs.verfahren_id = '".(int)$data["data"]."' AND sm.software_id = vs.software_id) ORDER by name ASC";


        $res = $mc->query($sql);
        $fachdienste[0] = "Bitte wählen ...";

        while($row = mysqli_fetch_array($res)) {
            $fachdienste[$row["massnahme_id"]] = $row["name"];
        }

        $form->add_select("Organisatorische Maßnahme aus Vorlage","",$fachdienste,"",false,verfahren_right_write($id));
        $form->setTargetClassFunction("subpage_verfaren_edit_organisatorische_massnahmen","save_massnahme");
        return json_encode(array("status" => "1","content" => $form->getContent(),"header"=> "Maßnahmen zur Sicherung der Verarbeitung hinterlegen"));
    }

    function ajax_remove_massnahme ($data)
    {
        $mc = new mysql();
        $sql ="DELETE FROM verfahren_massnahmen WHERE vm_id = '".$data["data"]."'";
        if($mc->query($sql))
            return json_encode(array("status" => "1","msg" => "!","callback" => "location_reload","formcontrol" => ""));
        else
            return json_encode(array("status" => "0","msg" => "DB ERROR: ".$mc->getError()));
    }

    function save_massnahme($data)
    {

        $mc = new mysql();
        $gruppe_id = $_SESSION["user"]->gruppe_id ;
        $orga_id =  $_SESSION["user"]->organisation_id;
        $massnahme_id = $data["massnahmen_organisatorischemanahmeausvorlage"];
        if(strlen($data["massnahmen_bezeichnungderorganisatorischenmanahme"]) != 0)
        {
            $mc->query("INSERT into massnahmen (name, organisation_id,gruppe_id,type) VALUES ('".$data["massnahmen_bezeichnungderorganisatorischenmanahme"]."','".$orga_id."','".$gruppe_id."','organisatorisch')");
            $massnahme_id = $mc->getID();
        }

        $sql = "INSERT into verfahren_massnahmen (verfahren_id,massnahme_id) VALUES ('".$data["massnahmen_id"]."','".$massnahme_id."')";

        if($mc->query($sql))
            return json_encode(array("status" => "1","msg" => "!","callback" => "location_reload","formcontrol" => ""));
        else
            return json_encode(array("status" => "0","msg" => "DB ERROR: ".$mc->getError()));

    }
}