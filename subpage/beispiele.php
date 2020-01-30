<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 22.03.2018
 * Time: 13:58
 */

class subpage_beispiele extends subpage {

    function getContent($page){
        $this->title = "Beispiele";



        $sql = "SELECT  v.beispiel,v.vollstaendig, o.bezeichnung as organisation_name, v.bezeichnung as verfahren_name, g.bezeichnung as gruppe_name,v.verfahren_id,v.gruppe_id FROM verfahren as v,gruppe as g,organisation as o WHERE o.organisation_id = g.organisation_id AND v.gruppe_id = g.gruppe_id AND v.beispiel = 1 ORDER BY verfahren_name ASC";

        $table = new table();

        $table->addHeader("Status");
        $table->addHeader("Bezeichnung der Verarbeitungstätigkeit");
        $table->addHeader("Organisationseinheit");
        $table->addHeader("Bearbeiten");


        $res = $page->sql->query($sql);

        echo $page->sql->getError();

        while ($row = mysqli_fetch_array($res)) {
            $row_data = array();

            if($row["vollstaendig"] == 1) {
                $row_data[] = "<span style=\"display:none;\">0</span><img src='images/gruen.png' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Die Verarbeitungstätigkeit ist vollständig bearbeitet\">";
            }
            if($row["vollstaendig"] == 0) {
                $row_data[] = "<span style=\"display:none;\">1</span><img src='images/rot.png' alt='val1' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Die Verarbeitungstätigkeit ist noch nicht vollständig bearbeitet\">";
            }

            $row_data[] = "<a class='anodeco' href='?s=verfahren_edit&id=" . $row["verfahren_id"] . "'>" . $row["verfahren_name"] . "</a>";

            $row_data[] = $row["organisation_name"] . " - " . $row["gruppe_name"];


            if(verfahren_right_write($row["verfahren_id"])) {
                $ret = "
                     <a href='?s=verfahren_edit&id=" . $row["verfahren_id"] . "' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Anzeigen\">
                        <span class=\"fas fa-cog lnk-blue\"></span>
                      </a>
                  ";
            }
            else
            {
                $ret = "
                     <a href='?s=verfahren_edit&id=" . $row["verfahren_id"] . "' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Anzeigen\">
                        <span class=\"fas fa-play lnk-blue\"></span>
                      </a>
                  ";
            }


            $row_data[] = $ret;

            $table->addRow($row_data);
        }

        return $this->card($table->getContent(),$this->title);
    }
}

