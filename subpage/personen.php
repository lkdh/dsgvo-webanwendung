<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 30.11.2018
 * Time: 12:24
 */
class subpage_personen   extends subpage{


    //TODO: Bei userrn die aus dem AD kommen, sind alle felder leer.
    function getContent($page)
    {
        $this->title = "Personen";
        $atable = new autotable();


        foreach($_SESSION["user"]->gruppen as $gruppe)
        {
            $where[] = "v.gruppe_id = '".$gruppe["gruppe_id"]."'";
        }


        if($page->is_super_admin())
            $where = "";
        else
        {
            if($page->is_admin())
            {
                $sql = " AND v.gruppe_id = g.gruppe_id AND o.organisation_id = '".$_SESSION["user"]->organisation_id."' ORDER BY verfahren_name ASC";

            }
            else
                $sql = "SELECT  v.upload_enabled,v.beispiel,v.vollstaendig, o.bezeichnung as organisation_name, v.bezeichnung as verfahren_name, g.bezeichnung as gruppe_name,v.verfahren_id,v.gruppe_id FROM verfahren as v,gruppe as g,organisation as o WHERE o.organisation_id = g.organisation_id AND v.gruppe_id = g.gruppe_id AND (".implode(" OR ",$where).") ORDER BY verfahren_name ASC";
        }

        $atable->init("personen",array("person_id","name","strasse","plz","ort","telefon","email","internet","type"),"WHERE group = ".$_SESSION["user"]->organisation_id);

        $atable->set_headername("person_id","ID#");
        $atable->set_headername("name","Name:");
        $atable->set_headername("strasse","Straße:");
        $atable->set_headername("plz","PLZ:");
        $atable->set_headername("ort","Ort:");
        $atable->set_headername("telefon","Telefon:");
        $atable->set_headername("email","E-Mail:");
        $atable->set_headername("internet","Homepage:");
        $atable->set_headername("type","Typ:");


        $atable->edit = true;
        $atable->enable_delete("Sind sie sicher das Sie die Person %s löschen wollen?","name");

        $atable->id_row = "person_id";


        return $this->card($atable->getContent(),$this->title);
    }
}