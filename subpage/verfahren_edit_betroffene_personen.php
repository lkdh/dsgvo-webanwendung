<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 19.04.2018
 * Time: 15:51
 */

class subpage_verfahren_edit_betroffene_personen extends subpage
{

    function ajax_update_datenkategorie($data)
    {
        if(isset($data["datenkategorie_besonderekategoriegemartabsdsgvo"]))
            $data["datenkategorie_besonderekategoriegemartabsdsgvo"] = 1;
        else
            $data["datenkategorie_besonderekategoriegemartabsdsgvo"] = 0;

        $updates = array(
            "name" => $data["datenkategorie_bezeichnungderdatenkategorie"],
            "beschreibung" => $data["datenkategorie_inhaltderdatenkategorie"],
            "loeschfristen" => $data["datenkategorie_lschfristen"],
            "besondere_kategorie" => $data["datenkategorie_besonderekategoriegemartabsdsgvo"],
        );
        $mc = new mysql();
        $mc->updateRow("datenkategorie",$updates,"datenkategorie_id",$data["datenkategorie_verfahrenid"]);
        return json_encode(array("status" => "1", "msg" => "", "callback" => "ajax_modal_callback", "formcontrol" => "loadmatrix"));
    }

    function ajax_update_personengruppe ($data)
    {
        $updates = array(
            "bezeichnung" => $data["betroffene-personen_bezeichnungderpersonengruppe"],
            "anzahl_personen" => $data["betroffene-personen_anzahlpersoneninpersonengruppe"],
        );
        $mc = new mysql();
        $mc->updateRow("personengruppen",$updates,"personengruppe_id",$data["betroffene-personen_verfahrenid"]);
        return json_encode(array("status" => "1", "msg" => "", "callback" => "ajax_modal_callback", "formcontrol" => "loadmatrix"));
    }

    function modal_add_betrofene_personen($data)
    {
        $id = $data["data"];
        $form = new form("betroffene-personen");
        $form->add_textbox("Bezeichnung der Personengruppe","","","Z.b. Auszubildene, Mitarbeiter, Kunden, Schüler, Eltern","text",false,verfahren_right_write($id));
        $form->add_textbox("Anzahl Personen in Personengruppe","","","Geben Sie hier die ungefähre Größe der Personengruppe an,<br> Beispiele: >1000, ca. 200, <5000, ca. 220.000, >40000","text",false,verfahren_right_write($id));
        $form->add_hidden("verfahren_id",$data["data"]);
        $form->setTargetClassFunction("subpage_verfahren_edit_betroffene_personen","ajax_add_new_personengruppe");
        return json_encode(array("status" => "1","content" => $form->getContent(),"header"=> "Personenkreis hinzufügen"));

    }

    function modal_datenkat_info($data){
        $id = $data["formcontrolname"];
        $mc = new mysql();
        $mdata = $mc->fetch_array("SELECT * FROM datenkategorie WHERE datenkategorie_id = '".$data["data"]."'");

        $form = new form("datenkategorie",verfahren_right_write($id));
        $form->add_textbox("Bezeichnung der Datenkategorie",$mdata["name"],"","Kurzbezeichnung für die Datenkategorie <b>Beispiel: Bewerbung, Betriebsarztuntersuchung, Arbeitszeugnisse, Abmahnungen, Stammdaten, Gewerbeanmeldung</b>","text",false,verfahren_right_write($id));
        $form->add_textbox("Inhalt der Datenkategorie",$mdata["beschreibung"],"","Hier bitte genau Angeben welche Art von personenbezogenen Daten in dieser Kategorie gespeichert werden. <b>Beispiel: Geburtsdatum, Bankverbindung, Steuermerkmale, Lohngruppe, Stundenplan, Bonitätsdaten</b>","text",false,verfahren_right_write($id));
        $form->add_textbox("Löschfristen",$mdata["loeschfristen"],"","Wann werden Daten dieser Kategorie gelöscht? Gibt es gesetzliche Grundlagen die Lösch bzw. Aufbewahrungsfristen definieren?","text",false,verfahren_right_write($id));

        if($mdata["besondere_kategorie"] == 1)
            $checked = true;
        else
            $checked = false;

        $form->add_checkbox("Besondere Kategorie gem. Art. 9 Abs 1 DS-GVO","Besondere Datenkategorien gem. DS-GVO Art. 9 Abs. 1 sind Daten, aus denen die rassische und ethnische Herkunft, politische Meinungen, religiöse oder weltanschauliche Überzeugungen oder die Gewerkschaftszugehörigkeit hervorgehen.Ebenso die Verarbeitung von genetischen Daten, biometrischen Daten zur eindeutigen Identifizierung einer natürlichen Person, Gesundheitsdaten oder Daten zum Sexualleben oder der sexuellen Orientierung einer natürlichen Person abgeleitet werden können.",$checked);
        $form->add_hidden("verfahren_id",$data["data"]);
        $form->setTargetClassFunction("subpage_verfahren_edit_betroffene_personen","ajax_update_datenkategorie");

        return json_encode(array("status" => "1","content" => $form->getContent(),"header"=> "Datenkategorie bearbeiten"));

    }
    function modal_personkat_info($data){
        $id = $data["formcontrolname"];

        $mc = new mysql();
        $mdata = $mc->fetch_array("SELECT * FROM personengruppen WHERE personengruppe_id = '".$data["data"]."'");

        $form = new form("betroffene-personen",verfahren_right_write($id));
        $form->add_textbox("Bezeichnung der Personengruppe",$mdata["bezeichnung"],"","Z.b. Auszubildene, Mitarbeiter, Kunden, Schüler, Eltern","text",false,verfahren_right_write($id));
        $form->add_textbox("Anzahl Personen in Personengruppe",$mdata["anzahl_personen"],"","Geben Sie hier die ungefähre Größe der Personengruppe an","text",false,verfahren_right_write($id));
        $form->add_hidden("verfahren_id",$data["data"]);
        $form->setTargetClassFunction("subpage_verfahren_edit_betroffene_personen","ajax_update_personengruppe");

        return json_encode(array("status" => "1","content" => $form->getContent(),"header"=> "Personenkreis bearbeiten"));

    }

    function ajax_add_new_personengruppe($data)
    {
        $mc = new mysql();
        if($data["betroffene-personen_ausvorlageauswhlen"] > 0 OR strlen($data["betroffene-personen_bezeichnungderpersonengruppe"]) > 0) {
            if ($data["betroffene-personen_ausvorlageauswhlen"] == 0) {
                $mc->query("INSERT into personengruppen (bezeichnung,anzahl_personen,organisation_id,gruppe_id) VALUES 
            ('" . $data["betroffene-personen_bezeichnungderpersonengruppe"] . "',
             '" . $data["betroffene-personen_anzahlpersoneninpersonengruppe"] . "',
              '" . $_SESSION["user"]->organisation_id . "',
                        '" . $_SESSION["user"]->gruppe_id . "')");

                $pg_id = $mc->getID();
            } else {
                $pg_id = $data["betroffene-personen_ausvorlageauswhlen"];
            }
            $mc->query("insert into verfahren_personengruppe (verfahren_id,personengruppe_id) VALUES ('" . $data["betroffene-personen_verfahrenid"] . "','" . $pg_id . "')");

            add_protokoll("add",  $data["betroffene-personen_verfahrenid"], "verfahren","",$pg_id,"verfahren_personengruppe");

            $this->check_default($data["betroffene-personen_verfahrenid"], $mc->getID());

            return json_encode(array("status" => "1", "msg" => "Neue Fachateilung wurde gespeichert!", "callback" => "ajax_modal_callback", "formcontrol" => "loadmatrix"));
        }
        else
        {
            return json_encode(array("status" => "0", "msg" => "Bitte Personengruppe angeben ODER Personengruppe aus Vorlage auswählen"));
        }
    }

    function check_default($verfahren_id, $verfahren_personengruppe_id = 0, $verfahren_datenkategorie_id = 0)
    {
        $mc = new mysql();

        if($verfahren_datenkategorie_id != 0)
        {
            $res = $mc->query("SELECT * FROM verfahren_personengruppe WHERE verfahren_id = '".$verfahren_id."'");
            while($row = mysqli_fetch_array($res) )
            {
                $mc->query("INSERT into datenkategorie_personengruppe (verfahren_datenkategorie_id,verfahren_personengruppe_id,vorgang_id) VALUES 
                                (".$verfahren_datenkategorie_id.",'".$row["vp_id"]."','".$verfahren_id."')");
            }

        }

        if($verfahren_personengruppe_id != 0)
        {
            $res = $mc->query("SELECT * FROM verfahren_datenkategorie WHERE verfahren_id = '".$verfahren_id."'");
            while($row = mysqli_fetch_array($res) )
            {
                $mc->query("INSERT into datenkategorie_personengruppe (verfahren_datenkategorie_id,verfahren_personengruppe_id,vorgang_id) VALUES 
                                (".$row["vd_id"].",'".$verfahren_personengruppe_id."','".$verfahren_id."')");
            }
        }

    }

    function ajax_add_neue_datenkategorie($data)
    {
        $mc = new mysql();
        if($data["datenkategorie_datenkategorieausvorlage"] > 0 OR strlen($data["datenkategorie_bezeichnungderdatenkategorie"]) > 0 )
        {

            if(isset($data["datenkategorie_besonderekategoriegemartabsdsgvo"]))
                $data["datenkategorie_besonderekategoriegemartabsdsgvo"] = 1;
            else
                $data["datenkategorie_besonderekategoriegemartabsdsgvo"] = 0;

            if ($data["datenkategorie_datenkategorieausvorlage"] == 0) {
                $mc->query("INSERT into datenkategorie (name,beschreibung,organisation_id,gruppe_id,loeschfristen,besondere_kategorie) VALUES 
            ('" . $data["datenkategorie_bezeichnungderdatenkategorie"] . "',
             '" . $data["datenkategorie_inhaltderdatenkategorie"] . "',
              '" . $_SESSION["user"]->organisation_id . "',
                        '" . $_SESSION["user"]->gruppe_id . "',
                        '".$data["datenkategorie_lschfristen"]."',
                        '".$data["datenkategorie_besonderekategoriegemartabsdsgvo"]."')");

                echo $mc->getError();
                $pg_id = $mc->getID();
            } else {
                $pg_id = $data["datenkategorie_datenkategorieausvorlage"];
            }
            $mc->query("insert into verfahren_datenkategorie (verfahren_id,datenkategorie_id) VALUES ('" . $data["datenkategorie_verfahrenid"] . "','" . $pg_id . "')");
            add_protokoll("add",  $data["datenkategorie_verfahrenid"], "verfahren","",$pg_id,"verfahren_datenkategorie");

            $this->check_default($data["datenkategorie_verfahrenid"],0, $mc->getID());

            return json_encode(array("status" => "1", "msg" => "", "callback" => "ajax_modal_callback", "formcontrol" => "loadmatrix"));
        }
        else
        {
            return json_encode(array("status" => "0", "msg" => "Bitte Name der Datenkategorie angeben ODER Datenkategorie aus Vorlage auswählen"));

        }
    }
    function modal_add_datenkategorie($data)
    {
        $id = $data["data"];

        $form = new form("datenkategorie");
        $form->add_textbox("Bezeichnung der Datenkategorie","","","Kurzbezeichnung für die Datenkategorie <b>Beispiel: Bewerbung, Betriebsarztuntersuchung, Arbeitszeugnisse, Abmahnungen, Stammdaten, Gewerbeanmeldung</b>","text",false,verfahren_right_write($id));
        $form->add_textbox("Inhalt der Datenkategorie","","","Hier bitte genau Angeben welche Art von personenbezogenen Daten in dieser Kategorie gespeichert werden. <b>Beispiel: Geburtsdatum, Bankverbindung, Steuermerkmale, Lohngruppe, Stundenplan, Bonitätsdaten</b>","text",false,verfahren_right_write($id));
        $form->add_textbox("Löschfristen","","","Wann werden Daten dieser Kategorie gelöscht? Gibt es gesetzliche Grundlagen die Lösch bzw. Aufbewahrungsfristen definieren?","text",false,verfahren_right_write($id));


        $form->add_checkbox("Besondere Kategorie gem. Art. 9 Abs 1 DS-GVO","Besondere Datenkategorien gem. DS-GVO Art. 9 Abs. 1 sind Daten, aus denen die rassische und ethnische Herkunft, politische Meinungen, religiöse oder weltanschauliche Überzeugungen oder die Gewerkschaftszugehörigkeit hervorgehen.Ebenso die Verarbeitung von genetischen Daten, biometrischen Daten zur eindeutigen Identifizierung einer natürlichen Person, Gesundheitsdaten oder Daten zum Sexualleben oder der sexuellen Orientierung einer natürlichen Person abgeleitet werden können.");
        $form->add_hidden("verfahren_id",$data["data"]);

        $form->setTargetClassFunction("subpage_verfahren_edit_betroffene_personen","ajax_add_neue_datenkategorie");

        return json_encode(array("status" => "1","content" => $form->getContent(),"header"=> "Datenkategorie hinzufügen"));

    }

    function ajax_toggle_state($data)
    {
        $jdata = json_decode(base64_decode($data["data"]));

        if(verfahren_right_write($jdata->vorgang_id)) {

            if ($jdata->state == "add") {
                add_protokoll("check",  $jdata->vorgang_id, "verfahren" ,$jdata->verfahren_datenkategorie_id ,$jdata->verfahren_personengruppe_id,"datenkategorie_personengruppe");
                $sql = "INSERT INTO `datenkategorie_personengruppe` (`dp_id`, `verfahren_datenkategorie_id`, `verfahren_personengruppe_id`, `vorgang_id`) VALUES (NULL, '" . $jdata->verfahren_datenkategorie_id . "', '" . $jdata->verfahren_personengruppe_id . "', '" . $jdata->vorgang_id . "')";
                $this->sql->query($sql);
            }
            if ($jdata->state == "remove") {
                add_protokoll("uncheck",  $jdata->vorgang_id, "verfahren" ,$jdata->verfahren_datenkategorie_id ,$jdata->verfahren_personengruppe_id,"datenkategorie_personengruppe");

                $sql = "DELETE FROM datenkategorie_personengruppe WHERE verfahren_datenkategorie_id = '" . $jdata->verfahren_datenkategorie_id . "' AND verfahren_personengruppe_id = '" . $jdata->verfahren_personengruppe_id . "' AND vorgang_id = '" . $jdata->vorgang_id . "'";
                $this->sql->query($sql);
            }
            echo json_encode(array("status" => 1));
        }
        else
            echo json_encode(array("status" => 0,"msg" => "nicht berechtigt!"));

    }

    function ajax_remove_datakat ($data)
    {

        if(verfahren_right_write($data["data1"])) {
            $mc = new mysql();

            add_protokoll("remove",$data["data1"],"verfahren",$data["data"],"","verfahren_datenkategorie");

            if ($mc->query("DELETE from verfahren_datenkategorie WHERE  vd_id = '" . $data["data"] . "'")) {
                echo json_encode(array("status" => 1));

            }
        }
        else
            echo json_encode(array("status" => 0,"msg" => "nicht berechtigt!"));

    }
    function ajax_remove_perskat($data)
    {
        if(verfahren_right_write($data["data1"])) {
            $mc = new mysql();
            add_protokoll("remove",$data["data1"],"verfahren",$data["data"],"","verfahren_personengruppe");

            if($mc->query("DELETE from verfahren_personengruppe WHERE  vp_id = '".$data["data"]."'"))
        {
            echo json_encode(array("status" => 1) );

        }
        }
        else
            echo json_encode(array("status" => 0,"msg" => "nicht berechtigt!"));
    }

    function ajax_getmatrix($data)
    {
        $id = $data["data"];
        if(verfahren_right_write($id)) {
            $btn = "<a href='#' onclick='ajax_modal(\"subpage_verfahren_edit_betroffene_personen\",\"modal_add_betrofene_personen\",\"" . $id . "\",\"\")' class='btn btn-primary btndefspace'>Personenkreis hinzufügen</a> ";
            $btn .= "<a href='#' onclick='ajax_modal(\"subpage_verfahren_edit_betroffene_personen\",\"modal_add_datenkategorie\",\"" . $id . "\",\"\")' class='btn btn-primary btndefspace'>Datenkategorie hinzufügen</a> ";
        }
        else {
            $btn = "";
        }
        $res = $this->sql->query("SELECT * from personengruppen as pg, verfahren_personengruppe as vpg WHERE vpg.personengruppe_id = pg.personengruppe_id AND vpg.verfahren_id = '".$id."'");
        $personengruppen = array();
        while($row = mysqli_fetch_array($res,MYSQLI_ASSOC))
        {
            $personengruppen[] = $row;
        }

        $res = $this->sql->query("SELECT * from datenkategorie as pg, verfahren_datenkategorie as vpg WHERE vpg.datenkategorie_id = pg.datenkategorie_id AND vpg.verfahren_id = '".$id."'");

        $datenkategorie = array();
        while($row = mysqli_fetch_array($res,MYSQLI_ASSOC))
        {
            $datenkategorie[] = $row;
        }

        $content = "
        <table class='table table-bordered table-responsive'>
            <thead class='tableheadrow'>
                <tr>
                    <td></td>
                   ";
        foreach($datenkategorie as $kat)
        {
            $editlnk = "<i class='lnk-blue'><a onclick='ajax_modal(\"subpage_verfahren_edit_betroffene_personen\",\"modal_datenkat_info\",\"".$kat["datenkategorie_id"]."\",\"".$id."\")' class=\"fas fa-edit lnk\"></a></i>";
            if(verfahren_right_write($id)) {
                $removelnk = "<i class='lnk-remove'><a onclick=\"ajax_action_class('subpage_verfahren_edit_betroffene_personen','ajax_remove_datakat','loadmatrix','" . $kat["vd_id"] . "','".$id."')\" class=\"fas fa-times lnk\"></a></i>";
            }
            else {
                $removelnk = "";
            }
            $content .="<td><b>".$kat["name"]."</b> ".$editlnk." ".$removelnk."</td>";
        }

        $content .="</tr>
            </thead>
            <tbody>";
        foreach($personengruppen as $persg)
        {
            $editlnk = "<i class='lnk-blue'><a onclick='ajax_modal(\"subpage_verfahren_edit_betroffene_personen\",\"modal_personkat_info\",\"".$persg["personengruppe_id"]."\",\"".$id."\")' class=\"fas fa-edit lnk\"></a></i>";
            if(verfahren_right_write($id)) {
                $removelnk = "<i class='lnk-remove'><a  onclick=\"ajax_action_class('subpage_verfahren_edit_betroffene_personen','ajax_remove_perskat','loadmatrix','" . $persg["vp_id"] . "','".$id."')\" class=\"fas fa-times\"></a></i>";
            }
            else
                $removelnk = "";

            $content .=" <tr>
            <td class='tableheadrow'><b>".$persg["bezeichnung"]."</b> ".$editlnk." ".$removelnk."</td>";

            foreach($datenkategorie as $kat)
            {

                $data =$this->sql->fetch_array("SELECT * FROM datenkategorie_personengruppe WHERE 
                vorgang_id = '".$id."' AND 
                verfahren_datenkategorie_id = '".$kat["vd_id"]."' AND 
                verfahren_personengruppe_id = '".$persg["vp_id"]."'");

                if(isset($data["dp_id"]))
                        $content .="<td class='matrixtablerow' onclick=\"ajax_action_class('subpage_verfahren_edit_betroffene_personen','ajax_toggle_state','loadmatrix','".base64_encode(json_encode(array("state" => "remove","vorgang_id" => $id,"verfahren_datenkategorie_id" => $kat["vd_id"],"verfahren_personengruppe_id" => $persg["vp_id"])))."')\"><a class=\"fas fa-check\"></a></td>";
                    else
                        $content .="<td class='matrixtablerow' onclick=\"ajax_action_class('subpage_verfahren_edit_betroffene_personen','ajax_toggle_state','loadmatrix','".base64_encode(json_encode(array("state" => "add","vorgang_id" => $id,"verfahren_datenkategorie_id" => $kat["vd_id"],"verfahren_personengruppe_id" => $persg["vp_id"])))."')\"><a class=\"fas fa-minus\"></a></td>";
            }
            $content .="</tr>";

        }

        $content .="
            </tbody>
        </table>";

        if(count($datenkategorie) == 0 OR count($personengruppen) == 0) {
            $content = "";
            if (count($datenkategorie) == 0)
                $content .= alert("Bitte Datenkategorie hinzufügen!", "Es ist noch keine Datenkategorie vorhanden!", "warning");

            if (count($personengruppen) == 0)
                $content .= alert("Bitte Personenkreis hinzufügen!", "Es ist noch kein Personenkreis vorhanden!", "warning");
            $infotext = "";
        }
        else
        {
            if(verfahren_right_write($id)) {
                $infotext = "
            <div class=\"alert alert-warning\" role=\"alert\">
            <li>Bitte klicken Sie innerhalb der Tabelle auf das Icon <span class=\"fas  fa-minus\"></span> oder <span class=\"fas fa-check\"></span>, um den Status eines Eintrages zu verändern</li>
             <li>Bitte klicken Sie auf Icon <span class=\"fas lnk fa-edit\"></span>, um einen Personenkreis oder eine Datenkategorie zu bearbeiten</li>

            <li>Bitte klicken Sie auf Icon <span class=\"fas lnk-remove fa-times\"></span>, um einen Personenkreis oder eine Datenkategorie zu entfernen</li>
            </div>";
            }
            else
                $infotext = "";
        }

    echo json_encode(array("status" => 1,"dat" => "asd","content" => $btn.$infotext.$content) );
    }

    function getContent($page)
    {
        $verfahren = $this->sql->fetch_array("SELECT * FROM verfahren WHERE verfahren_id =".(int)$_GET["id"]);
        $content ="<script> function loadmatrix(){ajax_action_class(\"subpage_verfahren_edit_betroffene_personen\",\"ajax_getmatrix\",\"show_datamatrix\",\"".(int)$_GET["id"]."\")}loadmatrix();</script><div id='contentmatrix'></div>";

        $btn = "<a class='btn btn-primary btn-sm btn btndefspace speichernbtnform' href='#' onclick=\"location.href='index.php?s=verfahren_edit_empfaenger&id=".(int)$_GET["id"]."'\">Nächster Schritt</a>";
        $ret = $this->card($content,"Betroffene Personen und betroffene Daten","Von wem (betroffene Personen) werden welche Daten verarbeitet?").$btn;

        $wizzard = new wizzard();

        $wizzard->setHeader($verfahren["bezeichnung"]);
        $wizzard->setDefault();

        return $wizzard->getContent($ret,3);


        return $ret;
    }


}