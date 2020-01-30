<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 25.04.2018
 * Time: 14:23
 */

class subpage_verfahren_edit_dokumente  extends  subpage{

    function setData($data)
    {
        $id = $data["data"];
        $mc = new mysql();

        $verfahren = $this->sql->fetch_array("SELECT * FROM verfahren WHERE verfahren_id =".$id);
        if($data["data1"] == "automatisch")
        {
            if($verfahren["docs_auto"] == "1")
                $mc->query("UPDATE verfahren set docs_auto = 0 WHERE verfahren_id = '".$id."'");
            else
                $mc->query("UPDATE verfahren set docs_auto = 1 WHERE verfahren_id = '".$id."'");
        }

        if($data["data1"] == "manual")
        {
            if($verfahren["docs_manual"] == "1")
                $mc->query("UPDATE verfahren set docs_manual = 0 WHERE verfahren_id = '".$id."'");
            else
                $mc->query("UPDATE verfahren set docs_manual = 1 WHERE verfahren_id = '".$id."'");
        }

        if($data["data1"] == "einverstaendnis")
        {
            if($verfahren["art6_1"] == "1")
                $mc->query("UPDATE verfahren set art6_1 = 0 WHERE verfahren_id = '".$id."'");
            else
                $mc->query("UPDATE verfahren set art6_1 = 1 WHERE verfahren_id = '".$id."'");
        }
        if($data["data1"] == "infoschreiben")
        {
            if($verfahren["infoschreiben"] == "1")
                $mc->query("UPDATE verfahren set infoschreiben = 0 WHERE verfahren_id = '".$id."'");
            else
                $mc->query("UPDATE verfahren set infoschreiben = 1 WHERE verfahren_id = '".$id."'");
        }

        $this->getTable($data);
    }


    function getTable($data)
    {
        $id = $data["data"];
        $verfahren = $this->sql->fetch_array("SELECT * FROM verfahren WHERE verfahren_id =".$id);
        $this->title = $verfahren["bezeichnung"];

        if(!verfahren_right_write($id))
            $disabled = true;
        else
            $disabled = false;

        if($verfahren["docs_auto"] == "1")
            $prvlnk = "<a target='_blank'  href='infoblatt.php?id=".$id."'><i class=\"far fa-file-pdf\"></i> Vorschau anzeigen</a>";
        else
            $prvlnk = "";

            $result = "<div id='controlbton'>
            <div class=\"licontform\">
                <input ".disabled(!verfahren_right_write($id)).checked($verfahren["docs_auto"],"1")." class=\"form-check-input\" type='checkbox' onclick='ajax_action_class(\"subpage_verfahren_edit_dokumente\",\"setData\",\"show_datamatrix\",\"".$id."\",\"automatisch\")' id=".$id." name=".$id.">
                Informationsblatt automatisch generieren ".$prvlnk."
            </div>";

        if($verfahren["art6_1"] == "1")
            $prvlnk = "<a target='_blank'  href='einwilligung.php?id=".$id."'><i class=\"far fa-file-pdf\"></i> Vorschau anzeigen</a>";
        else
        {
            $prvlnk = "";
        }

                $result .= "
            <div class=\"licontform\">
                <input ".disabled(!verfahren_right_write($id)).checked($verfahren["art6_1"],"1")." class=\"form-check-input\" type='checkbox' onclick='ajax_action_class(\"subpage_verfahren_edit_dokumente\",\"setData\",\"show_datamatrix\",\"".$id."\",\"einverstaendnis\")' id=" . $id . " name=" . $id . ">
                Einverständniserklärung automatisch generieren  ".$prvlnk."
            </div>";


            $result .="
            <div class=\"licontform\">
                <input ".disabled(!verfahren_right_write($id)).checked($verfahren["docs_manual"],"1")." class=\"form-check-input\" type='checkbox' onclick='ajax_action_class(\"subpage_verfahren_edit_dokumente\",\"setData\",\"show_datamatrix\",\"".$id."\",\"manual\")' id=".$id." name=".$id.">Fremde PDF-Dateien hochladen
            </div>
            ";

        if($verfahren["docs_manual"] == 1) {

            $result .= "<div class='spacingpre'>".get_doc_modal_link_inline("einversta", $id, "Einverständniserklärungen " . $this->title, "Einverständniserklärungen", $disabled,verfahren_right_write($id))."</div>";

        }
        echo json_encode(array("status" => 1,"dat" => "","content" => $result) );
    }

    function getContent($page)
    {
        $verfahren = $this->sql->fetch_array("SELECT * FROM verfahren WHERE verfahren_id =".(int)$_GET["id"]);
        $content ="<script> function loadmatrix(){ajax_action_class(\"subpage_verfahren_edit_dokumente\",\"getTable\",\"show_datamatrix\",\"".(int)$_GET["id"]."\")}loadmatrix();</script><div id='contentmatrix'></div>";

        $btn = "<a class='btn btn-primary btn-sm btn btndefspace speichernbtnform' href='#' onclick=\"location.href='index.php?s=verfahren_edit_empfaenger&id=".(int)$_GET["id"]."'\">Nächster Schritt</a>";
        $ret = $this->card($content,"Datenschutzunterlagen erstellen","Mit den erfassten Daten werden die Unterlagen automatisch erzeugt. Bestehen rechtliche Vorgaben, dass beispielsweise Informationsblätter oder Merkblätter des Bundes oder des Landes für eine Verarbeitungstätigkeit zwingend anzuwenden sind, können solche Dokumente als fremde PDF-Datei hinzugefügt werden.");


        $mc = new mysql();

        $res = $mc->query("select d.name, m.massnahme_id from dokumente as d, massnahmen as m,verfahren_massnahmen as vm WHERE vm.verfahren_id =  '".(int)$_GET["id"]."' AND vm.massnahme_id = m.massnahme_id AND m.massnahme_id = d.object_id AND d.deleted = 0");

        if(mysqli_num_rows($res) > 0) {
            $vertraege = "";
            while ($row = mysqli_fetch_array($res)) {


                if(verfahren_right_write((int)$_GET["id"])) {
                    $infolnk = " <i class='lnk-blue'>
                <a onclick=\"ajax_modal('subpage_verfaren_edit_technische_massnahmen','modal_info','" . base64_encode(json_encode(array("dokument_id", "verfahren_id" => (int)$_GET["id"], "massnahme_id" => $row["massnahme_id"]))) . "')\" class=\"fas fa-info\"></a></i> ";
                }
                else
                    $infolnk = "";

                $vertraege .= "<li>".$row["name"]."<i class=\"lnk-blue\">
                <a onclick=\"ajax_modal('subpage_verfaren_edit_technische_massnahmen','modal_info','eyJ2ZXJmYWhyZW5faWQiOiIxMTMiLCJtYXNzbmFobWVfaWQiOiI1In0=')\" class=\"fas fa-info\"></a>
                </i> <span class=\"fas lnk-grey fa-times\"></span></li>";

            }
        }
        else
            $vertraege = "<b>Kein ADV-Vertrag hinterlegt.</b>";

        $ret .= $this->card($vertraege,"Verträge zur Auftragsdatenverarbeitung","Wenn ein Vertrag zur Auftragsdatenverarbeitung geschlossen wurde, ist dies eine organisatorische Maßnahme zur Sicherung der Verarbeitung (vgl. TOM, Schritt 5). Für alle Verarbeitungstätigkeiten werden die ADV-Verträge zentral von der EDV-Abteilung hochgeladen. Bei Rückfragen bitte Kontakt mit der EDV-Abteilung aufnehmen.").$btn;

        $wizzard = new wizzard();

        $wizzard->setHeader($verfahren["bezeichnung"]);
        $wizzard->setDefault();

        return $wizzard->getContent($ret,6);


        return $ret;
    }
}