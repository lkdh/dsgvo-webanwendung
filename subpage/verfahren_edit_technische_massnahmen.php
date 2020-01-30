<?php
/**
 * Created by PhpStorm.
 * User: henrik
 * Date: 19.04.2018
 * Time: 22:56
 */

class subpage_verfaren_edit_technische_massnahmen   extends subpage{

    function modal_info($data)
    {
        $data = json_decode(base64_decode($data["data"]));

        $sql = "SELECT s.name FROM software_massnahmen as sm, verfahren_software as vs, software as s WHERE
                vs.verfahren_id = ".$data->verfahren_id." AND s.software_id = vs.software_id AND sm.massnahme_id = '".$data->massnahme_id."' AND sm.software_id = vs.software_id GROUP BY s.name ASC";

        $res22 = $this->sql->query($sql);
        echo $this->sql->getError();
        $rq = array();

        $removelnk = "";
        while($row_ass = mysqli_fetch_array($res22))
        {
            $removelnk .= "<li>".$row_ass["name"]."</li>";
        }

        return json_encode(array("status" => "1","content" => $removelnk,"header"=> "Zuordnung über EDV-Anwendung"));
    }


    function modal_add_technische_m ($data)
    {
        $form = new form("massnahmen");
        $id = $data["data"];
        $form->add_textbox("Bezeichnung der technischen Maßnahme","","","","text",false,verfahren_right_write($id));
        $form->add_plaintext("ODER (AUS VORHANDENER LISTE AUSWÄHLEN):","","","","text",false,verfahren_right_write($id));


        $form->add_hidden("id",$id);
        $mc = new mysql();

        $orgas = array("organisation_id = 0");
        $groups = array("gruppe_id = 0");

        foreach($_SESSION['user']->gruppen as $key => $value)
        {
            $groups[] = "gruppe_id = ".$value["gruppe_id"];
            $orgas[] = "organisation_id = ".$value["organisation_id"];
        }

        $sql = "SELECT * FROM massnahmen WHERE (".implode(" OR ",$orgas).") AND (".implode(" OR ",$groups).") AND type = 'technisch' 
        AND massnahme_id NOT IN (SELECT massnahme_id FROM verfahren_massnahmen WHERE verfahren_id = '".$data["data"]."') AND massnahme_id NOT IN(SELECT massnahme_id FROM software_massnahmen as sm, verfahren_software as vs WHERE vs.verfahren_id = '".(int)$data["data"]."' AND sm.software_id = vs.software_id) ORDER by name ASC";


        $res = $mc->query($sql);
        $fachdienste[0] = "Bitte wählen ...";

        while($row = mysqli_fetch_array($res)) {
            $fachdienste[$row["massnahme_id"]] = $row["name"];
        }

        $form->add_select("Technische Maßnahme aus Vorlage","",$fachdienste,"",false,verfahren_right_write($id));




        $form->setTargetClassFunction("subpage_verfaren_edit_technische_massnahmen","save_massnahme");
        return json_encode(array("status" => "1","content" => $form->getContent(),"header"=> "Technische Maßnahme hinzufügen"));
    }

    function ajax_remove_massnahme ($data)
    {
        $mc = new mysql();
        $sql ="DELETE FROM verfahren_massnahmen WHERE vm_id = '".$data["data"]."'";

        $olddata = $mc->fetch_array("SELECT * FROM verfahren_massnahmen WHERE vm_id = '".$data["data"]."'");

        add_protokoll("remove",$olddata["verfahren_id"],"verfahren",$olddata["massnahme_id"],"","verfahren_tom_technisch");

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
        $massnahme_id = $data["massnahmen_technischemanahmeausvorlage"];
            if(strlen($data["massnahmen_bezeichnungdertechnischenmanahme"]) != 0)
            {
                $mc->query("INSERT into massnahmen (name, organisation_id,gruppe_id,type) VALUES ('".$data["massnahmen_bezeichnungdertechnischenmanahme"]."','".$orga_id."','".$gruppe_id."','technisch')");
                $massnahme_id = $mc->getID();
            }

        add_protokoll("add",$data["massnahmen_id"],"verfahren","",$massnahme_id,"verfahren_tom_technisch");

                $sql = "INSERT into verfahren_massnahmen (verfahren_id,massnahme_id) VALUES ('" . $data["massnahmen_id"] . "','" . $massnahme_id . "')";
                if ($mc->query($sql))
                    return json_encode(array("status" => "1", "msg" => "!", "callback" => "location_reload", "formcontrol" => ""));
                else
                    return json_encode(array("status" => "0", "msg" => "DB ERROR: " . $mc->getError()));

    }
    function getContent($page)
    {
        $id = (int)$_GET["id"];
        $verfahren = $this->sql->fetch_array("SELECT * FROM verfahren WHERE verfahren_id =".(int)$_GET["id"]);
        $this->title = $verfahren["bezeichnung"];

        if(verfahren_right_write($id)) {
            $c1 = "<a class='btn btn-primary' href='#' onclick=\"ajax_modal('subpage_verfaren_edit_technische_massnahmen','modal_add_technische_m','" . $_GET["id"] . "','')\">Technische Maßnahmen hinzufügen</a>";
        }
        else
            $c1 = "";

            $sql = "
        select * from massnahmen WHERE type = 'technisch' AND (massnahme_id IN (SELECT massnahme_id FROM verfahren_massnahmen WHERE verfahren_id = '".(int)$_GET["id"]."') 
                                OR massnahme_id IN (SELECT massnahme_id FROM software_massnahmen as sm, verfahren_software as vs WHERE vs.verfahren_id = '".(int)$_GET["id"]."' AND sm.software_id = vs.software_id)) GROUP BY massnahme_id";

        $res = $this->sql->query($sql);

        $ret = "";
        $num = 0;
        while($row = mysqli_fetch_array($res))
        {
            $num++;

            $data = $this->sql->fetch_array("SELECT * FROM verfahren_massnahmen WHERE verfahren_id = '".(int)$_GET["id"]."' AND massnahme_id = '".$row["massnahme_id"]."'");

            if(isset($data["vm_id"]))

                if(verfahren_right_write($id))
                    $removelnk= " <i class='lnk-remove'><a onclick=\"ajax_action_class('subpage_verfaren_edit_technische_massnahmen','ajax_remove_massnahme','location_reload','".$data["vm_id"]."')\" class=\"fas fa-times\"></a></i>";
                else
                    $removelnk = "";
            else
            {
                $infolnk= " <i class='lnk-blue'>
                <a onclick=\"ajax_modal('subpage_verfaren_edit_technische_massnahmen','modal_info','".base64_encode(json_encode(array("verfahren_id" => $_GET["id"],"massnahme_id" => $row["massnahme_id"])))."')\" class=\"fas fa-info\"></a></i> ";

                $removelnk = $infolnk."<span class='fas lnk-grey fa-times'></span>";
            }

            $ret .= "<li>".$row["name"].$removelnk."</li>";
        }





        if($num == 0)
            $c1 .= "<br><b>Keine technischen Maßnahmen hinterlegt!</b>";
        else
            $c1 .= "<br>".$ret;



        $result = $this->card($c1,"Technische Maßnahmen zur Sicherung der Verarbeitung");
#####################################
        if(verfahren_right_write($id)) {

            $c1 = "<a class='btn btn-primary' href='#' onclick=\"ajax_modal('subpage_verfaren_edit_organisatorische_massnahmen','modal_add_technische_m','" . $_GET["id"] . "','')\">Organisatorische Maßnahmen hinzufügen</a>";
        }
        else
            $c1 = "";

        $sql = "select * from massnahmen WHERE type = 'organisatorisch' AND 
                (massnahme_id IN (SELECT massnahme_id FROM verfahren_massnahmen WHERE verfahren_id = '".(int)$_GET["id"]."') 
                OR massnahme_id IN (SELECT massnahme_id FROM software_massnahmen as sm, verfahren_software as vs WHERE vs.verfahren_id = '".(int)$_GET["id"]."' AND sm.software_id = vs.software_id)) GROUP BY massnahme_id";

        $res = $this->sql->query($sql);

        $ret = "";
        $num = 0;
        while($row = mysqli_fetch_array($res))
        {
            $num++;

            $data = $this->sql->fetch_array("SELECT * FROM verfahren_massnahmen WHERE verfahren_id = '".(int)$_GET["id"]."' AND massnahme_id = '".$row["massnahme_id"]."'");

            if(isset($data["vm_id"]))
                if(verfahren_right_write($id))
                    $removelnk = " <i class='lnk-remove'><a onclick=\"ajax_action_class('subpage_verfaren_edit_organisatorische_massnahmen','ajax_remove_massnahme','location_reload','".$data["vm_id"]."')\" class=\"fas fa-times\"></a></i>";
                else
                    $removelnk = "";
            else
            {
                $infolnk= " <i class='lnk-blue'>
            <a onclick=\"ajax_modal('subpage_verfaren_edit_technische_massnahmen','modal_info','".base64_encode(json_encode(array("verfahren_id" => $_GET["id"],"massnahme_id" => $row["massnahme_id"])))."')\" class=\"fas fa-info\"></a></i> ";

                $removelnk = $infolnk."<span class='fas lnk-grey fa-times'></span>";
            }


            $ret .= "<li>".$row["name"].$removelnk."</li>";
        }

        if($num == 0)
            $c1 .= "<br><b>Keine organisatorischen Maßnahmen hinterlegt!</b>";
        else
            $c1 .= "<br>".$ret;

        $result .= $this->card($c1,"Organisatorische Maßnahmen zur Sicherung der Verarbeitung");


#######################################
        $result .= "<a class='btn btn-primary btn-sm btn btndefspace speichernbtnform' href='#' onclick=\"location.href='index.php?s=verfaren_edit_gefahrenanalyse&id=".(int)$_GET["id"]."'\">Nächster Schritt</a>";


        $wizzard = new wizzard();
        $wizzard->setHeader($verfahren["bezeichnung"]);

        $wizzard->setDefault();


        return $wizzard->getContent($result,5);
        }
}