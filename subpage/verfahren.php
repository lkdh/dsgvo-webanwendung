<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 22.03.2018
 * Time: 13:58
 */

class subpage_verfahren extends subpage {

    function modal_delete_verfahren($page){
        $mc = new mysql();
        $name = $mc->fetch_array("SELECT * FROM verfahren WHERE verfahren_id = '".$page["data"]."'");
        return json_encode(array("status" => "1", "header" => "Verarbeitungstätigkeit löschen?",
            "content" => "Sind sie sicher das sie die Verarbeitungstätigkeit <b>".$name["bezeichnung"]."</b> löschen wollen?<br><br><a class='btn btn-info' onclick='ajax_action_class(\"subpage_verfahren\",\"ajax_delete_verfahren\",\"location_reload\",\"".$page["data"]."\");' href='#'>Ja, jetzt löschen</a>"));
    }

    function ajax_delete_verfahren($data){

        $mc = new mysql();
        $mc->query("DELETE FROM verfahren WHERE verfahren_id = '".$data["data"]."'");
        $mc->query("DELETE FROM datenkategorie_personengruppe WHERE vorgang_id = '".$data["data"]."' ");
        $mc->query("DELETE FROM verfahren_datenkategorie WHERE verfahren_id = '".$data["data"]."' ");
        $mc->query("DELETE FROM verfahren_massnahmen WHERE verfahren_id = '".$data["data"]."' ");
        $mc->query("DELETE FROM verfahren_personengruppe WHERE verfahren_id = '".$data["data"]."' ");
        $mc->query("DELETE FROM verfahren_software WHERE verfahren_id = '".$data["data"]."' ");
        $mc->query("DELETE FROM verfahren_weitergabe WHERE verfahren_id = '".$data["data"]."' ");

        return json_encode(array("status" => "1"));
    }

    function getContent($page){
            $this->title = "Meine Verarbeitungstätigkeiten";

        foreach($_SESSION["user"]->gruppen as $gruppe)
            {
                $where[] = "v.gruppe_id = '".$gruppe["gruppe_id"]."'";
            }


            if($page->is_super_admin())
                $sql = "SELECT v.upload_enabled,v.beispiel,v.vollstaendig,o.bezeichnung as organisation_name, v.bezeichnung as verfahren_name, g.bezeichnung as gruppe_name,v.verfahren_id,v.gruppe_id FROM verfahren as v,gruppe as g,organisation as o WHERE o.organisation_id = g.organisation_id AND v.gruppe_id = g.gruppe_id ORDER BY verfahren_name ASC";
            else
            {
                if($page->is_admin())
                {
                    $sql = "SELECT  v.upload_enabled,v.beispiel,v.vollstaendig,o.bezeichnung as organisation_name, v.bezeichnung as verfahren_name, g.bezeichnung as gruppe_name,v.verfahren_id,v.gruppe_id FROM verfahren as v,gruppe as g,organisation as o WHERE o.organisation_id = g.organisation_id AND v.gruppe_id = g.gruppe_id AND o.organisation_id = '".$_SESSION["user"]->organisation_id."' ORDER BY verfahren_name ASC";

                }
                else
                    $sql = "SELECT  v.upload_enabled,v.beispiel,v.vollstaendig, o.bezeichnung as organisation_name, v.bezeichnung as verfahren_name, g.bezeichnung as gruppe_name,v.verfahren_id,v.gruppe_id FROM verfahren as v,gruppe as g,organisation as o WHERE o.organisation_id = g.organisation_id AND v.gruppe_id = g.gruppe_id AND (".implode(" OR ",$where).") ORDER BY verfahren_name ASC";
            }

        $table = new table();

        $table->addHeader("Status");

        if(has_ftp_upload())
        $table->addHeader("Internet");

        $table->addHeader("Bezeichnung der Verarbeitungstätigkeit");
        $table->addHeader("Organisationseinheit");
        $table->addHeader("Beispiel");
        $table->addHeader("Bearbeiten");


        $res = $page->sql->query($sql);

        echo $page->sql->getError();

            while ($row = mysqli_fetch_array($res)) {
                $row_data = array();
                $errors = verfahrengetErrors($row["verfahren_id"]);

                if($row["vollstaendig"] == 1) {
                    if(count($errors) > 0)
                    {
                        $row_data[] = "<span style=\"display:none;\">0</span><img src='images/orange.png' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Verarbeitungstätigkeit ist freigegeben, aber Dateneingabe noch nicht vollständig\">";
                    }
                    else
                    {
                        $row_data[] = "<span style=\"display:none;\">0</span><img src='images/gruen.png' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Verarbeitungstätigkeit ist freigegeben\">";
                    }

                }else
                    {
                    $row_data[] = "<span style=\"display:none;\">1</span><img src='images/rot.png' alt='val1' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Verarbeitungstätigkeit befindet sich im Entwurf\">";
                }

                if(has_ftp_upload()) {
                    if ($row["upload_enabled"] == 1) {
                        if ($row["vollstaendig"] == 0) {
                            $table->addStyle(1, "text-align:center;background-color:#FBDDE1;");
                            $row_data[] = "<a href='?s=verfahren_edit_abschluss&id=" . $row["verfahren_id"] . "'>ausstehend</a>";
                        } else {
                            $table->addStyle(1, "text-align:center;background-color:#DCE4DC;");
                            $row_data[] = "ja";
                        }
                    } else {
                        $table->addStyle(1, "text-align:center;");

                        $row_data[] = "nein";
                    }
                }


                $row_data[] = "<a class='anodeco' href='?s=verfahren_edit&id=" . $row["verfahren_id"] . "'>" . $row["verfahren_name"] . "</a>";

                $row_data[] = $row["organisation_name"] . " - " . $row["gruppe_name"];

                if($row["beispiel"] == 0)
                    $row_data[] = "Nein";
                else
                    $row_data[] = "Ja";



                $ret ="
                     <a href='?s=verfahren_edit&id=" . $row["verfahren_id"] . "' data-toggle=\"tooltip\" data-placement=\"top\" title=\"bearbeiten\">
                        <span class=\"fas fa-cog lnk-blue\"></span>
                      </a>
                  ";


                if($page->is_super_admin()) {
                    $ret .= "
                     <a href='?s=verfahren_historie&id=" . $row["verfahren_id"] . "' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Historie\">
                        <span class=\"fas fa-history lnk-blue\"></span>
                      </a>
                     ";
                }

                if($page->is_admin() OR  $_SESSION["user"]->data["right_can_delete"] == 1) {
                    $ret .= "
                         <a onclick=\"ajax_modal('subpage_verfahren','modal_delete_verfahren','" . $row["verfahren_id"] . "','');\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"löschen\">
                        <span class=\"fas fa-times lnk-remove\"></span>
                      </a>";
                }

                $row_data[] = $ret;

                $table->addRow($row_data);
            }

        $ret = "<a class='btn btn-primary btn-sm btndefspace' href=\"?s=verfahren_add\">Neue Verarbeitungstätigkeit anlegen</a> ";
        $ret .= "<a class='btn btn-warning btn-sm btndefspace' target='_blank' href=\"/files/static/Hinweise_zum_Verzeichnis_von_Verarbeitungsttigkeiten.pdf\"><span class='far fa-file-pdf'></span> Ausfüllhinweise anzeigen</a> ";
        $ret .= "<a class='btn btn-warning btn-sm btndefspace' target='_blank' href=\"/files/static/Anwenderhandbuch_Webanwendung_EU-DSGVO.pdf\"><span class='far fa-file-pdf'></span> Anwenderhandbuch anzeigen</a>";


        return $this->card($ret.$table->getContent(),$this->title);
    }
}

