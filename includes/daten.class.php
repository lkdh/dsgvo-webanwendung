<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 11.04.2018
 * Time: 16:36
 */

class daten{

    function getAdUser($username)
    {
        $mc = new mysql();
         return $mc->fetch_array("SELECT name,strasse,plz,ort,telefon,email FROM ad_personen WHERE ad_username = '".$username."'");
    }

    function ajax_get_person($data)
    {

        if(!is_super_admin()) {
            $orgas = array("organisation_id = 0");
            $groups = array("gruppe_id = 0");
            foreach ($_SESSION['user']->gruppen as $key => $value) {
                $groups[] = "gruppe_id = " . $value["gruppe_id"];
                $orgas[] = "organisation_id = " . $value["organisation_id"];
            }
            $sql = "SELECT * FROM personen WHERE (".implode(" OR ",$orgas).") AND (".implode(" OR ",$groups).") AND type = '".$data["data"]."' ORDER by name ASC";

        }
        else
        {
            $sql = "SELECT * FROM personen WHERE type = '".$data["data"]."' ORDER by name ASC";

        }
        $ms = new mysql();
        $res = $ms->query($sql);

        $ret = array();
        $ret[] = array("label" => "Nicht benannt","val" => 0);
        while($row = mysqli_fetch_array($res))
        {
            if(strlen($row["ad_username"]) > 2) {
                $ad_user = $this->getAdUser($row["ad_username"]);

                if(strlen($row["name"]) > 0)
                    $realname = $row["name"];
                else
                {
                    $realname = $ad_user["name"];
                }

                $ret[] = array("label" => $realname.", ".$ad_user["strasse"]." ".$ad_user["plz"]." ".$ad_user["ort"]." Telefon: ".$ad_user["telefon"]." Email: ".$ad_user["email"],"val" => $row["person_id"]);

            }
            else {
                $ret[] = array("label" => $row["name"].", ".$row["strasse"]." ".$row["plz"]." ".$row["ort"]." Telefon: ".$row["telefon"]." Email: ".$row["email"]."", "val" => $row["person_id"]);
            }
        }
        return json_encode(array("status" => "1","data" => $ret));
    }

    function ajax_get_fachabteilungen($data)
    {
        $orgas = array("organisation_id = 0");
        $groups = array("gruppe_id = 0");
        foreach($_SESSION['user']->gruppen as $key => $value)
        {
            $groups[] = "gruppe_id = ".$value["gruppe_id"];
            $orgas[] = "organisation_id = ".$value["organisation_id"];
        }

        $sql = "SELECT * FROM fachabteilung WHERE (".implode(" OR ",$orgas).") AND (".implode(" OR ",$groups).") ORDER by bezeichnung ASC";
        $ms = new mysql();
        $res = $ms->query($sql);
        $ret = array();
        $ret[] = array("label" => "Nicht benannt","val" => 0);
        while($row = mysqli_fetch_array($res))
        {
                $ret[] = array("label" => $row["bezeichnung"],"val" => $row["abteilung_id"]);
        }
        return json_encode(array("status" => "1","data" => $ret));
    }

    function ajax_add_persondata_inmodal($data)
    {
        $disabled = true;
        $form = new form("addperson");
        $ms = new mysql();
        $res = $ms->query("SELECT * FROM ad_personen ORDER by name ASC");
        $ret = array();
        $ret[0] =  "Bitte wählen ...";
        while($row = mysqli_fetch_array($res))
        {
            $ret[$row["ad_username"]] =  $row["name"];
        }

        $form->add_select("Benutzerkonto","",$ret,"",false,$disabled);
        $form->add_plaintext("ODER (FREITEXT EINGEBEN):");

        $form->add_header("Externe Stelle hinzufügen");

        $form->add_hidden("formcontrolname",$data["formcontrolname"]);
        $form->add_hidden("type",$data["data"]);
        $form->add_textbox("Name","","","","text",false,$disabled);
        $form->add_textbox("Straße","","","","text",false,$disabled);
        $form->add_textbox("PLZ","","","","text",false,$disabled);
        $form->add_textbox("Ort","","","","text",false,$disabled);
        $form->add_textbox("Telefon","","","","text",false,$disabled);
        $form->add_textbox("E-Mail","","","","text",false,$disabled);
        $form->add_textbox("Homepage","","","","text",false,$disabled);

        $form->add_infotext("Nach dem Speichern sehen Sie den neuen Eintrag in Ihrer Auswahlliste");

        $form->setTargetClassFunction("daten","add_persondata");

        return json_encode(array("status" => "1","content" => $form->getContent(),"header"=> "Neue Person hinzufügen"));
    }
    function ajax_add_fachabteilung_inmodal($data)
    {
        $form = new form("addfachabteilung");
        $form->add_hidden("formcontrolname",$data["formcontrolname"]);
        $form->add_textbox("Name der Abteilung","","","","text",false,$disabled);
        $form->add_textbox("Ansprechpartner","","","","text",false,$disabled);
        $form->add_textbox("Telefon","","","","text",false,$disabled);
        $form->add_textbox("E-Mail","","","","text",false,$disabled);
        $form->setTargetClassFunction("daten","add_fachabteilung");

        return json_encode(array("status" => "1","content" => $form->getContent(),"header"=> "Neue Fachabteilung anlegen"));
    }
    function add_fachabteilung($data)
    {
        $ms = new mysql();
        $fnnmae = "reloaddata".str_replace(array("-","_"),"",strtolower($data["addfachabteilung_formcontrolname"]));

        if($ms->query("INSERT into fachabteilung (bezeichnung,ansprechpartner,telefon,email,organisation_id,gruppe_id) VALUES 
                        ('".$data["addfachabteilung_namederabteilung"]."',
                        '".$data["addfachabteilung_ansprechpartner"]."',
                        '".$data["addfachabteilung_telefon"]."',
                        '".$data["addfachabteilung_email"]."',
                        '".$_SESSION["user"]->organisation_id."',
                        '".$_SESSION["user"]->gruppe_id."'
                        ) "))
        {
            return json_encode(array("status" => "1","msg" => "Neue Fachateilung wurde gespeichert!","callback" => "ajax_modal_callback","formcontrol" => $fnnmae));
        }
        else
        {
            return json_encode(array("status" => "0","msg" => $ms->getError()));
        }

    }


    function add_persondata($data)
    {
        $ms = new mysql();
        $fnnmae = "reloaddata".str_replace(array("-","_"),"",strtolower($data["addperson_formcontrolname"]));

        if($ms->query("INSERT into personen (name,strasse,plz,ort,telefon,email,internet,organisation_id,gruppe_id,type,ad_username) VALUES 
                        ('".$data["addperson_name"]."','".$data["addperson_strae"]."','".$data["addperson_plz"]."','".$data["addperson_ort"]."',
                        '".$data["addperson_telefon"]."','".$data["addperson_email"]."','".$data["addperson_homepage"]."','".$_SESSION["user"]->organisation_id."',
                        '".$_SESSION["user"]->gruppe_id."','".$data["addperson_type"]."','".$data["addperson_benutzerkonto"]."') "))
        {
            return json_encode(array("status" => "1","msg" => "Neue Person wurde gespeichert!","callback" => "ajax_modal_callback","formcontrol" => $fnnmae));
        }
        else
        {
            return json_encode(array("status" => "0","msg" => $ms->getError()));
        }

    }
}


function getArrayGruppenInThisOrga()
{
    $mc = new mysql();
    $res = $mc->query("SELECT * FROM gruppe WHERE organisation_id = '".$_SESSION["user"]->organisation_id."' ORDER BY bezeichnung ASC");
    $gruppen[0] = "Alle";
    while($row = mysqli_fetch_array($res))
    {
        $gruppen[$row["gruppe_id"]] = $row["bezeichnung"];
    }
    return $gruppen;
}

function getArrayUserByTypeandOrga($type)
{
   $dat = new daten();
   $ret = json_decode($dat->ajax_get_person(array("data" => $type)));

   $rets = array();
   foreach($ret->data as $elem)
   {
       $rets[$elem->val] = $elem->label;
   }

   return $rets;

}