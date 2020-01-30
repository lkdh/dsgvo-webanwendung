<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 04.05.2018
 * Time: 08:58
 */

class subpage_admin_edv_anwendungen  extends subpage{

    function getContent($page)
    {
        $this->title = "EDV-Anwendungen";
        $atable = new autotable();
        $atable->datatables_enabled = true;
        $atable->init("software",array("software_id","name","anbieter","software_id_extern"));
        $atable->set_headername("software_id","ID#");
        $atable->set_headername("name","Bezeichnung");
        $atable->set_headername("anbieter","Hersteller");
        $atable->set_headername("organisation","Hersteller");

        $atable->set_headername("software_id_extern","Import ID");

        $atable->edit = true;
        $atable->delete = true;

        $atable->id_row = "software_id";

        $uploader = new uploader();

        return $this->card($atable->getContent(),"EDV-Anwendungen").$this->card($uploader->getContent("subpage_admin_edv_anwendungen","import_xlsx"),"XLS Import");
    }


    function import_xlsx($data)
    {
        $mc = new mysql();
        if(file_exists($_FILES["file"]["tmp_name"]))
        {
            $xlsx = new XLSXReader($_FILES["file"]["tmp_name"]);
            $sheets = $xlsx->getSheetNames();
            $data = $xlsx->getSheetData('Softwarekataster');
            $ret = "";
            $ordereddata = array();

            $header = array();
            foreach($data[0] as $h)
            {
                $header[] = $h;
            }


            foreach($data as $anwendung)
            {
                $colpos = 0;
                $t = array();
                foreach($anwendung as $col)
                {

                    $t[$header[$colpos]] = $col;
                    $colpos++;
                }
                $ordereddata[] = $t;
            }
            unset($ordereddata[0]);

            foreach($ordereddata as $anwendung)
            {


                if(strtolower($anwendung["DSGVO"]) == "ja")
                {
                    $dbdata = $mc->fetch_array("SELECT * FROM software WHERE software_id_extern = '".$anwendung["Software-ID"]."'");

                    if(!isset($dbdata["software_id"]))
                    {
                        $mc->query("insert into software (software_id_extern) VALUES ('".$anwendung["Software-ID"]."')");
                        $dbdata = $mc->fetch_array("SELECT * FROM software WHERE software_id_extern = '".$anwendung["Software-ID"]."'");
                    }

                    $fachdienste = explode(",",$anwendung["Fachdienst"]);
                    $hausweit = false;

                    foreach($fachdienste as $fachdienst)
                    {
                        if(strtolower($fachdienst) == "hausweit")
                        {
                            $hausweit = true;
                        }

                        $gruppe_ids = array();
                        $gruppe = $mc->fetch_array("SELECT * FROM gruppe WHERE ad_name = '".strtolower($fachdienst)."' AND organisation_id = '".$_SESSION["user"]->organisation_id."'");
                        if(isset($gruppe["gruppe_id"]))
                        {
                            $gruppe_ids[] = $gruppe["gruppe_id"];
                        }

                    }

                    $toms = explode(",",$anwendung["TOM"]);
                    $tom_ids = array();
                    foreach($toms as $tom)
                    {
                        $massnahme = $mc->fetch_array("SELECT * FROM massnahmen WHERE shortcode = '".strtolower($tom)."' AND organisation_id = '".$_SESSION["user"]->organisation_id."'");
                        $tom_ids[] = $massnahme["massnahme_id"];
                    }

                    $mc->updateRelation("software_massnahmen","software_id",$dbdata["software_id"],"massnahme_id",$tom_ids);

                    if($hausweit)
                    {
                        $hw_id = array();
                        $res = $mc->query("SELECT *FROM gruppe WHERE organisation_id = '".$_SESSION["user"]->organisation_id."'");
                        while($row = mysqli_fetch_array($res))
                        {
                            $hw_id[] = $row["gruppe_id"];
                            $mc->updateRelation("software_gruppe","software_id",$dbdata["software_id"],"gruppe_id",$hw_id);

                        }
                    }
                    else
                    {
                        $mc->updateRelation("software_gruppe","software_id",$dbdata["software_id"],"gruppe_id",$gruppe_ids);

                    }


                    $updates = array(
                        "name" => $anwendung["Software"]." (".$anwendung["Kurzbeschreibung"].")",
                        "anbieter" => $anwendung["Anbieter/Hersteller"],
                    );

                    if($mc->updateRow("software",$updates,"software_id",$dbdata["software_id"]))
                            $ret .= "Anwendung ".$anwendung["Software"]." wurde aktualisiert!<br>";
                        else
                            $ret .= "Anwendung ".$anwendung["Software"]." wurde NICHT aktualisiert!<br>";





                }
                else
                    $ret .= "Anwendung ".$anwendung["Software"]." soll nicht importiert werden.<br>";
            }

            echo json_encode(array("status" => 1, "header" => "Upload von EDV Anwendung Liste","content" =>$ret));
        }
        else
        {
            echo json_encode(array("status" => 0, "msg" => "Fehler beim Upload!"));

        }
    }

}