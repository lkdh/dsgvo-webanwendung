<?php
/**
 * Created by PhpStorm.
 * User: henrik
 * Date: 19.04.2018
 * Time: 22:56
 */

class subpage_verfahren_edit_empfaenger   extends subpage{

    function modal_emfaenger_fachdienst ($data)
    {

        $form = new form("emfaenger-fachdienst");
        $form->add_hidden("id",$data["data"]);
        $form->add_hidden("type","Fachdienst");
        $form->add_textbox("Name der Abteilung, des Fachdienstes, des Bereiches oder der Funktionen","","","Hat eine andere Stelle innerhalb der Organisation Zugriff auf Daten aus dieser Verarbeitungstätigkeit, so muss diese hier benannt werden.","textbox","emfaenger-fachdienst_namedesfachdienstes",true);
        $form->add_plaintext("ODER (AUS VORHANDENER LISTE AUSWÄHLEN):");

        $mc = new mysql();

        $orgas = array("organisation_id = 0");
        $groups = array("gruppe_id = 0");

        foreach($_SESSION['user']->gruppen as $key => $value)
        {
            $groups[] = "gruppe_id = ".$value["gruppe_id"];
            $orgas[] = "organisation_id = ".$value["organisation_id"];
        }

        $sql = "SELECT * FROM weitergabe WHERE (".implode(" OR ",$orgas).") AND (".implode(" OR ",$groups).") AND type = 'fachdienst' 
        AND weitergabe_id NOT IN (SELECT weitergabe_id FROM verfahren_weitergabe WHERE verfahren_id = '".$data["data"]."')  ORDER by name ASC";


        $res = $mc->query($sql);
        $fachdienste[0] = "Bitte wählen ...";

        while($row = mysqli_fetch_array($res)) {
            $fachdienste[$row["weitergabe_id"]] = $row["name"];
        }

        $form->add_select("Fachdienst aus Vorlage","",$fachdienste,"",false,true);
            $form->setTargetClassFunction("subpage_verfahren_edit_empfaenger","save_empfaenger");
        return json_encode(array("status" => "1","content" => $form->getContent(),"header"=> "Datenweitergabe an Fachdienst hinzufügen"));
    }
    function modal_emfaenger_organisation ($data)
    {

        $form = new form("emfaenger-fachdienst");
        $form->add_hidden("id",$data["data"]);
        $form->add_hidden("type","extern");

        $form->add_textbox("Name der Organisation","","","Bezeichnung der externen Organisation hier eingeben","text","emfaenger-fachdienst_namedesfachdienstes",true);
        $form->add_plaintext("ODER (AUS VORHANDENER LISTE AUSWÄHLEN):");

        $mc = new mysql();

        $orgas = array("organisation_id = 0");
        $groups = array("gruppe_id = 0");

        foreach($_SESSION['user']->gruppen as $key => $value)
        {
            $groups[] = "gruppe_id = ".$value["gruppe_id"];
            $orgas[] = "organisation_id = ".$value["organisation_id"];
        }

        $sql = "SELECT * FROM weitergabe WHERE (".implode(" OR ",$orgas).") AND (".implode(" OR ",$groups).") AND type = 'extern' 
        AND weitergabe_id NOT IN (SELECT weitergabe_id FROM verfahren_weitergabe WHERE verfahren_id = '".$data["data"]."')  ORDER by name ASC";


        $res = $mc->query($sql);
        $fachdienste[0] = "Bitte wählen ...";

        while($row = mysqli_fetch_array($res)) {
            $fachdienste[$row["weitergabe_id"]] = $row["name"];
        }

        $form->add_select("Externe Organisation aus Vorlage","",$fachdienste,"","emfaenger-fachdienst_fachdienstausvorlage",true);
        $form->setTargetClassFunction("subpage_verfahren_edit_empfaenger","save_empfaenger");
        return json_encode(array("status" => "1","content" => $form->getContent(),"header"=> "Datenweitergabe an externe Organisation hinzufügen"));
    }

    function modal_emfaenger_drittland ($data)
    {

        $form = new form("emfaenger-fachdienst");
        $form->add_hidden("id",$data["data"]);
        $form->add_hidden("type","drittland");
        $form->add_textbox("Name der Organisation in Drittland oder internationale Organisation","","","Bezeichnung der Organisation in Drittland oder internationalen Organisation hier eingeben","text","emfaenger-fachdienst_namedesfachdienstes",true);
        $form->add_plaintext("ODER (AUS VORHANDENER LISTE AUSWÄHLEN):");

        $mc = new mysql();

        $orgas = array("organisation_id = 0");
        $groups = array("gruppe_id = 0");

        foreach($_SESSION['user']->gruppen as $key => $value)
        {
            $groups[] = "gruppe_id = ".$value["gruppe_id"];
            $orgas[] = "organisation_id = ".$value["organisation_id"];
        }

        $sql = "SELECT * FROM weitergabe WHERE (".implode(" OR ",$orgas).") AND (".implode(" OR ",$groups).") AND type = 'drittland' 
        AND weitergabe_id NOT IN (SELECT weitergabe_id FROM verfahren_weitergabe WHERE verfahren_id = '".$data["data"]."')  ORDER by name ASC";


        $res = $mc->query($sql);
        $fachdienste[0] = "Bitte wählen ...";

        while($row = mysqli_fetch_array($res)) {
            $fachdienste[$row["weitergabe_id"]] = $row["name"];
        }

        $form->add_select("Organisation in Drittland oder internationale Organisation aus Vorlage","",$fachdienste,"","emfaenger-fachdienst_fachdienstausvorlage",true);
        $form->setTargetClassFunction("subpage_verfahren_edit_empfaenger","save_empfaenger");
        return json_encode(array("status" => "1","content" => $form->getContent(),"header"=> "Datenweitergabe an Drittland hinzufügen"));
    }


    function ajax_remove_weitergabe ($data)
    {
        $mc = new mysql();
        $sql ="DELETE FROM verfahren_weitergabe WHERE vw_id = '".$data["data"]."'";

        $olddata = $mc->fetch_array("SELECT * FROM verfahren_weitergabe WHERE vw_id = '".$data["data"]."'");
        add_protokoll("remove",$olddata["verfahren_id"],"verfahren",$olddata["weitergabe_id"],"","verfahren_weitergabe");

        if($mc->query($sql))
            return json_encode(array("status" => "1","msg" => "!","callback" => "location_reload","formcontrol" => ""));
        else
            return json_encode(array("status" => "0","msg" => "DB ERROR: ".$mc->getError()));
    }

    function save_empfaenger($data)
    {
        $mc = new mysql();
        if(strlen($data["emfaenger-fachdienst_namedesfachdienstes"]) < 1)
        {
            $sql = "INSERT into verfahren_weitergabe (verfahren_id,weitergabe_id) VALUES ('".$data["emfaenger-fachdienst_id"]."','".$data["emfaenger-fachdienst_fachdienstausvorlage"]."')";
            $newid = $data["emfaenger-fachdienst_fachdienstausvorlage"];
        }
        else
        {
            $mc->query("INSERT into weitergabe (name, type, organisation_id, gruppe_id) VALUES 
            ('".$data["emfaenger-fachdienst_namedesfachdienstes"]."','".$data["emfaenger-fachdienst_type"]."',   '".$_SESSION["user"]->organisation_id."','".$_SESSION["user"]->gruppe_id."')");
            $newid = $mc->getID();
            $sql = "INSERT into verfahren_weitergabe (verfahren_id,weitergabe_id) VALUES ('".$data["emfaenger-fachdienst_id"]."','".$mc->getID()."')";
        }
        add_protokoll("add",$data["emfaenger-fachdienst_id"],"verfahren","",$newid,"verfahren_weitergabe");

        if($mc->query($sql))
            return json_encode(array("status" => "1","msg" => "!","callback" => "location_reload","formcontrol" => ""));
        else
            return json_encode(array("status" => "0","msg" => "DB ERROR: ".$mc->getError()));

    }

        function getContent($page)
    {
        $id = (int)$_GET["id"];
        $verfahren = $this->sql->fetch_array("SELECT * FROM verfahren WHERE verfahren_id =".(int)$_GET["id"]);
        $this->title = $verfahren["bezeichnung"];


        ########
        if(verfahren_right_write($id)) {
            $c1 = "<a class='btn btn-primary' href='#' onclick=\"ajax_modal('subpage_verfahren_edit_empfaenger','modal_emfaenger_fachdienst','" . $_GET["id"] . "','')\">Datenweitergabe hinzufügen</a>";
        }
        else
            $c1 = "";
        $res = $this->sql->query("select * from weitergabe as w, verfahren_weitergabe as vw WHERE type = 'fachdienst' AND  w.weitergabe_id = vw.weitergabe_id AND vw.verfahren_id = '".(int)$_GET["id"]."'");

        $ret = "";
        $num = 0;
        while($row = mysqli_fetch_array($res))
        {
            $num++;
            if(verfahren_right_write($id)) {
                $removelnk = " <i class='lnk-remove'><a onclick=\"ajax_action_class('subpage_verfahren_edit_empfaenger','ajax_remove_weitergabe','location_reload','" . $row["vw_id"] . "')\" class=\"fas fa-times\"></a></i>";
            }
            else
                $removelnk = "";
           $ret .= "- ".$row["name"].$removelnk."<br>";
        }

        if($num == 0)
            $c1 .= "<br><b>Keine Datenweitergabe an Fachdienst hinterlegt!</b>";
        else
            $c1 .= "<br>".$ret;


        $result = $this->card($c1,"Zugriffsrechte an andere Abteilungen, Fachdienste, Bereiche oder Funktionen");

        ####
        if(verfahren_right_write($id)) {
            $c1 = "<a href='#' class='btn btn-primary' onclick=\"ajax_modal('subpage_verfahren_edit_empfaenger','modal_emfaenger_organisation','" . $_GET["id"] . "','')\">Datenweitergabe hinzufügen</a>";
        }
        else
            $c1 = "";
        $res = $this->sql->query("select * from weitergabe as w, verfahren_weitergabe as vw WHERE type = 'extern' AND  w.weitergabe_id = vw.weitergabe_id AND vw.verfahren_id = '".(int)$_GET["id"]."'");

        $ret = "";
        $num = 0;
        while($row = mysqli_fetch_array($res))
        {
            $num++;
            if(verfahren_right_write($id)) {
                $removelnk = " <i class='lnk-remove'><a onclick=\"ajax_action_class('subpage_verfahren_edit_empfaenger','ajax_remove_weitergabe','location_reload','" . $row["vw_id"] . "')\" class=\"fas fa-times\"></a></i>";
            }
            else
                $removelnk = "";
            $ret .= "- ".$row["name"].$removelnk."<br>";
        }

        if($num == 0)
            $c1 .= "<br><b>Keine Datenweitergabe an externe Organisation hinterlegt!</b>";
        else
            $c1 .= "<br>".$ret;


        $result .= $this->card($c1,"Datenweitergabe an externe Organisationen");

        ####
        if(verfahren_right_write($id)) {
            $c1 = "<a class='btn btn-primary' href='#' onclick=\"ajax_modal('subpage_verfahren_edit_empfaenger','modal_emfaenger_drittland','" . $_GET["id"] . "','')\">Datenweitergabe hinzufügen</a>";
        }
        else
            $c1 = "";
        $res = $this->sql->query("select * from weitergabe as w, verfahren_weitergabe as vw WHERE type = 'drittland' AND  w.weitergabe_id = vw.weitergabe_id AND vw.verfahren_id = '".(int)$_GET["id"]."'");

        $ret = "";
        $num = 0;
        while($row = mysqli_fetch_array($res))
        {
            $num++;
            if(verfahren_right_write($id)) {
                    $removelnk= " <i class='lnk-remove'><a onclick=\"ajax_action_class('subpage_verfahren_edit_empfaenger','ajax_remove_weitergabe','location_reload','".$row["vw_id"]."')\" class=\"fas fa-times\"></a></i>";
        }
    else
        $removelnk = "";

            $ret .= "- ".$row["name"].$removelnk."<br>";
        }

        if($num == 0)
            $c1 .= "<br><b>Keine Datenweitergabe an Organisation in Drittland oder internationale Organisation hinterlegt!</b>";
        else
            $c1 .= "<br>".$ret;


        $result .= $this->card($c1,"Datenweitergabe an Organisation in Drittland oder internationale Organisation");

        ####



        $result .= "<a class='btn btn-primary btn-sm btn btndefspace speichernbtnform'  href='#' onclick=\"location.href='index.php?s=verfaren_edit_technische_massnahmen&id=".(int)$_GET["id"]."'\">Nächster Schritt</a>";

        $wizzard = new wizzard();
        $wizzard->setHeader($verfahren["bezeichnung"]);

        $wizzard->setDefault();


        return $wizzard->getContent($result,4);
    }
}