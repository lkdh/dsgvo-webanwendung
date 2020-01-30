<?php
/**
 * Created by PhpStorm.
 * User: henrik
 * Date: 19.04.2018
 * Time: 22:56
 */

class subpage_verfahren_edit_software extends subpage{

    function modal_add_software ($data)
    {

        $form = new form("massnahmen");
        $form->add_hidden("id",$data["data"]);

        $mc = new mysql();

        $orgas = array("organisation_id = 0");
        $groups = array("gruppe_id = 0");

        foreach($_SESSION['user']->gruppen as $key => $value)
        {
            $groups[] = "gruppe_id = ".$value["gruppe_id"];
            $orgas[] = "organisation_id = ".$value["organisation_id"];
        }

        $sql = "SELECT * FROM software WHERE software_id IN(SELECT software_id FROM software_gruppe WHERE ".implode(" OR ",$groups).") AND software_id NOT IN (SELECT software_id FROM verfahren_software WHERE verfahren_id = '".$data["data"]."')  ORDER by name ASC";


        $res = $mc->query($sql);
        $fachdienste[0] = "Bitte wählen ...";

        while($row = mysqli_fetch_array($res)) {
            $fachdienste[$row["software_id"]] = $row["name"];
        }

        $form->add_select("Aus vorhandener Softwareliste auswählen","",$fachdienste,"Ist Ihre vorhandene Software nicht in der Auswahlliste zu sehen, kontaktieren Sie bitte Ihren Administrator",false,true);
        $form->add_plaintext("ODER (FREITEXT EINGEBEN):");

        $form->add_textbox("Name der EDV-Anwendung","","","Bitte benennen Sie die EDV-Anwendung welche Sie verwenden um die Verarbeitungstätigkeit durchzuführen","text",false,true);

        $form->setTargetClassFunction("subpage_verfahren_edit_software","save_software");
        return json_encode(array("status" => "1","content" => $form->getContent(),"header"=> "EDV-Anwendung hinzufügen"));
    }

    function ajax_remove_software ($data)
    {
            $mc = new mysql();
            $sql = "DELETE FROM verfahren_software WHERE vs_id = '" . $data["data"] . "'";

            $olddata = $mc->fetch_array("SELECT * FROM verfahren_software WHERE vs_id = '".$data["data"]."'");

            add_protokoll("remove",  $olddata["verfahren_id"], "verfahren",$olddata["software_id"],"","verfahren_software");

        if ($mc->query($sql))
                return json_encode(array("status" => "1", "msg" => "!", "callback" => "location_reload", "formcontrol" => ""));
            else
                return json_encode(array("status" => "0", "msg" => "DB ERROR: " . $mc->getError()));
    }

    function save_software($data)
    {
            $mc = new mysql();
            if ($data["massnahmen_ausvorhandenersoftwarelisteauswhlen"] == 0) {
                if (strlen($data["massnahmen_namederedvanwendung"]) > 0) {
                    $mc->query("INSERT into software (name) VALUES ('" . $data["massnahmen_namederedvanwendung"] . "')");
                    $software_id = $mc->getID();
                    $mc->query("INSERT into software_gruppe (software_id,gruppe_id) VALUES ('" . $software_id . "','" . $_SESSION["user"]->gruppe_id . "')");

                    $sql = "INSERT into verfahren_software (verfahren_id,software_id) VALUES ('" . $data["massnahmen_id"] . "','" . $software_id . "')";
                } else {
                    return json_encode(array("status" => "0", "msg" => "Bitte \"Name der EDV-Anwendung\" ausfüllen!"));

                }

            } else {
                $software_id = $data["massnahmen_ausvorhandenersoftwarelisteauswhlen"];
                $sql = "INSERT into verfahren_software (verfahren_id,software_id) VALUES ('" . $data["massnahmen_id"] . "','" . $data["massnahmen_ausvorhandenersoftwarelisteauswhlen"] . "')";

            }


            if ($mc->query($sql)) {
                add_protokoll("edit",  $data["massnahmen_id"], "verfahren","",$software_id,"verfahren_software");
                return json_encode(array("status" => "1", "msg" => "!", "callback" => "location_reload", "formcontrol" => ""));
            }
            else
                return json_encode(array("status" => "0", "msg" => "DB ERROR: " . $mc->getError()));
    }
    function getContent($page)
    {
        $verfahren = $this->sql->fetch_array("SELECT * FROM verfahren WHERE verfahren_id =".(int)$_GET["id"]);
        $this->title = $verfahren["bezeichnung"];

        if(verfahren_right_write()) {
            $c1 = "<a class='btn btn-primary' href='#' onclick=\"ajax_modal('subpage_verfahren_edit_software','modal_add_software','" . $_GET["id"] . "','')\">EDV-Anwendung hinzufügen</a>";
        }
        else
            $c1 = "";
        $res = $this->sql->query("select * from software as w, verfahren_software as vw WHERE  w.software_id = vw.software_id AND vw.verfahren_id = '".(int)$_GET["id"]."'");

        $ret = "";
        $num = 0;
        while($row = mysqli_fetch_array($res))
        {
            $num++;
            if(verfahren_right_write()) {
                $removelnk = " <i class='lnk-remove'><a onclick=\"ajax_action_class('subpage_verfahren_edit_software','ajax_remove_software','location_reload','" . $row["vs_id"] . "')\" class=\"fas fa-times\"></a></i>";
            }
            else
                $removelnk = "";

            $ret .= "<li>".$row["name"].$removelnk."</li>";
        }

        if($num == 0) {
            $nosoft = true;
            $c1 .= "<br><b>Keine EDV-Anwendungen hinterlegt!</b>";
        }
        else {
            $nosoft = false;
            $c1 .= "<br>" . $ret;
        }



        $result = $this->card($c1,"EDV-Unterstützung","Welche EDV-Anwendungen werden für die Verarbeitungstätigkeit verwendet?<br> Ggfs. mehrere EDV-Anwendungen hinzufügen.",$nosoft,"addsoftwarecard");

        if($nosoft) {
            $cb = "<input type='checkbox' onclick=\"$('#addsoftwarecard').show();$('#checksoftwarecard').hide();\"> Die Verarbeitungstätigkeit wird mithilfe einer EDV-Anwendung durchgeführt";
            $result .= $this->card($cb, "EDV-Unterstützung", "",false,"checksoftwarecard");
        }

        $result .= "<a class='btn btn-primary btn-sm btn btndefspace speichernbtnform' href='#' onclick=\"location.href='index.php?s=verfahren_edit_betroffene_personen&id=".(int)$_GET["id"]."'\">Nächster Schritt</a>";

        $wizzard = new wizzard();
        $wizzard->setHeader($verfahren["bezeichnung"]);

        $wizzard->setDefault();


        return $wizzard->getContent($result,2);
    }
}