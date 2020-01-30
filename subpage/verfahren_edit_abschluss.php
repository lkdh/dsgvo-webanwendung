<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 25.04.2018
 * Time: 14:23
 */

class subpage_verfahren_edit_abschluss extends  subpage{

    function update_vollstaendig_state($data)
    {
        if(verfahren_right_write($data["data1"])) {
            $mc = new mysql();
            if ($mc->query("UPDATE verfahren SET vollstaendig = " . (int)$data["data"] . " WHERE verfahren_id = '" . (int)$data["data1"] . "'"))
                return json_encode(array("status" => 1));
            else
                return json_encode(array("status" => 1, "msg" => $mc->getError()));
        }
    }

    function release_all_new()
    {
        $mc = new mysql();
        $res = $mc->query("SELECT * FROM verfahren where upload_enabled = 1 AND vollstaendig = 1");
        while($row = mysqli_fetch_array($res))  {
            $this->upload_documents(array("data1" => $row["verfahren_id"]));
            echo "new: " .$row["verfahren_id"];
        }
    }

    function upload_documents($data)
    {
        $mc = new mysql();
        $dcheck = $mc->fetch_array("SELECT * FROM verfahren where verfahren_id = '".(int)$data["data1"]."'");
        if($dcheck["upload_enabled"] == 1 AND $dcheck["vollstaendig"] == 1)
        return $this->upload_verfahren(array("data" => (int)$data["data1"]));
        else
            return false;
    }

    function update_beispiel_state($data)
    {
        if(verfahren_right_write($data["data1"]))
        {
         $mc = new mysql();
        if ($mc->query("UPDATE verfahren SET beispiel = " . (int)$data["data"] . " WHERE verfahren_id = '" . (int)$data["data1"] . "'"))
            return json_encode(array("status" => 1));
        else
            return json_encode(array("status" => 1, "msg" => $mc->getError()));
          }
    }
    function update_internet($data)
    {
        if(verfahren_right_write($data["data1"]))
        {
            $mc = new mysql();
            if ($mc->query("UPDATE verfahren SET upload_enabled = " . (int)$data["data"] . " WHERE verfahren_id = '" . (int)$data["data1"] . "'")) {
                return $this->upload_verfahren(array("data" => (int)$data["data1"]));
            }
            else
                return json_encode(array("status" => 1, "msg" => $mc->getError()));
        }
    }


    function getTable($data)
    {
        $id = (int)$data["data"];
        if(verfahren_right_write($id))
            $ctl_disabled = "";
        else
            $ctl_disabled = "disabled";

        $verfahren = $this->sql->fetch_array("SELECT * FROM verfahren WHERE verfahren_id =".$id);
        $orga = $this->sql->fetch_array("SELECT * FROM gruppe as g, organisation as o WHERE g.gruppe_id = '".$verfahren["gruppe_id"]."' AND o.organisation_id = g.organisation_id");

        $this->title = $verfahren["bezeichnung"];

        if($verfahren["vollstaendig"] == 1) {
            $v1 = "selected";
            $v0 = "";
        }
        else
        {
            $v1 = "";
            $v0 = "selected";
        }

        $result = "";

        $errors = verfahrengetErrors($id);

        if(count($errors) > 0 AND $verfahren["vollstaendig"] == 1)
            $result .= alert("Warnung, folgende Dateneingabe ist noch nicht vollständig",implode("<br>",$errors),"warning");

        if($verfahren["upload_enabled"] == 1 AND $verfahren["vollstaendig"] == 0) {
            $result .= alert("Veröffentlichung Internet gestoppt", "Warnung, derzeit keine Veröffentlichung im Internet! Bitte zunächst den Bearbeitungsstand von „Entwurf“ auf „Freigabe“ ändern", "danger");
        }

        $content = "Eine Verarbeitungstätigkeit mit Entwurfsstatus wird nicht veröffentlicht. Für die Aufnahme in das Verzeichnis aller Verarbeitungstätigkeiten, muss die Bearbeitung freigegeben sein.<br>";
        $content .= "<select ".$ctl_disabled." onchange=\"loadingindicator();ajax_action_class('subpage_verfahren_edit_abschluss','update_vollstaendig_state','loadmatrix',this.value,'".$id."');\"><option ".$v0." value='0'>Entwurf</option><option ".$v1." value='1'>Freigabe</option></select>";
        $result .= $this->card($content,"Mein Bearbeitungsstand");

        if(has_ftp_upload()) {
            if ($orga["upload_enabled"] == 1) {
                $content = "";
                if ($verfahren["upload_enabled"] == 1) {
                    $v1 = "selected";
                    $v0 = "";
                } else {
                    $v1 = "";
                    $v0 = "selected";
                }

                $content .= "Soll die Verarbeitungstätigkeit für Bürgerinnen, Bürger und Unternehmen auf der Internetseite ".get_preview_url()." angezeigt werden?<br>";
                $content .= "<select " . $ctl_disabled . " onchange=\"loadingindicator();ajax_action_class('subpage_verfahren_edit_abschluss','update_internet','loadmatrix',this.value,'" . $id . "');\"><option " . $v0 . " value='0'>NEIN</option><option " . $v1 . " value='1'>JA</option></select>";

                if ($verfahren["upload_enabled"] == 1 AND $verfahren["vollstaendig"] == 1 AND verfahren_right_write($id)) {
                    $content .= "<br><br><a href='#' class='btn btn-primary btn-sm btn btndefspace' onclick=\"loadingindicator();ajax_action_class('subpage_verfahren_edit_abschluss','upload_documents','loadmatrix','','" . $id . "')\";>Informationsblatt erneut hochladen</a>";
                }

                $result .= $this->card($content, "Veröffentlichung im Internet");
            }
        }
        if($verfahren["beispiel"] == 1) {
            $v1 = "selected";
            $v0 = "";
        }
        else
        {
            $v1 = "";
            $v0 = "selected";
        }
            $content = "Eine Verarbeitungstätigkeit ist für Kolleginnen und Kollegen anderer Organisationseinheiten grundsätzlich als Beispiel einsehbar. Ist das nicht gewünscht, kann die Veröffentlichung hier ausgeschaltet werden.<br>";
            $content .= "<select ".$ctl_disabled." onchange=\"loadingindicator();ajax_action_class('subpage_verfahren_edit_abschluss','update_beispiel_state','loadmatrix',this.value,'".$id."');\"><option ".$v0." value='0'>NEIN</option><option ".$v1." value='1'>JA</option></select>";
            $result .= $this->card($content,"Veröffentlichung als Beispiel");


        $content =  "";
        if($verfahren["docs_auto"] == 1) {

            $content .= "<a target='_blank' href='infoblatt.php?id=" . $id . "'>Bitte hier klicken,</a> um das Informationsblatt als PDF-Datei anzuschauen<br>";
            }

            if(needeinwilligung($verfahren))
            $content .= "<a target='_blank' href='einwilligung.php?id=".$id."'>Bitte hier klicken,</a> um die Einwilligung als PDF-Datei anzuschauen<br>";

        if($verfahren["docs_manual"] == 1) {

            $resqq = $this->sql->query("SELECT * FROM dokumente WHERE typ = 'einversta' AND object_id = '" .$id . "' AND deleted = 0");
            while ($row = mysqli_fetch_array($resqq)) {
                $content .= "<a target='_blank' href='dokument.php?id=" . $row["dokument_id"] . "'>Bitte hier klicken,</a> um die fremde PDF-Datei (" . $row["name"] . ") anzuschauen<br>";
            }

        }

        if(strlen($content) > 0)
            $result .= $this->card($content, "Vorschau anzeigen");





        $result .= "<a class='btn btn-primary btn-sm btn btndefspace speichernbtnform' href='#' onclick=\"location.href='index.php?s=verfahren'\">Fertig</a>";

        echo json_encode(array("status" => 1,"dat" => "","content" => $result) );

    }

    function upload_verfahren($data)
    {
       return upload_verfahren($data);
    }

    function getContent($data)  
    {
        $verfahren = $this->sql->fetch_array("SELECT * FROM verfahren WHERE verfahren_id =".(int)$_GET["id"]);
        $content ="<script> function loadmatrix(){ajax_action_class(\"subpage_verfahren_edit_abschluss\",\"getTable\",\"show_datamatrix\",\"".(int)$_GET["id"]."\")}loadmatrix();</script><div id='loadingindicator'> Bitte warten ... <img src='images/loading.gif'></div><div id='contentmatrix'></div>";

        $wizzard = new wizzard();

        $wizzard->setHeader($verfahren["bezeichnung"]);
        $wizzard->setDefault();

        return $wizzard->getContent($content,8);



    }
}