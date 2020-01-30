<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 04.05.2018
 * Time: 08:58
 */

class subpage_admin_personen   extends subpage{


    function getContent($page)
    {
        $this->title = "Personen";
        $atable = new autotable();
        $atable->init("personen",array("person_id","name","strasse","plz","ort","telefon","email","internet","type","ad_username"),"WHERE organisation_id = ".$_SESSION["user"]->organisation_id);

        function checkEdit($data)
        {
            if(strlen($data["ad_username"]) > 2)
                return false;
            else
                return true;
        }

        $atable->set_rowenabledeletecheck("checkEdit");
        $atable->set_rowenableeditcheck("checkEdit");

        $atable->set_headername("person_id","ID#");
        $atable->set_headername("name","Name:");
        $atable->set_headername("strasse","Straße:");
        $atable->set_headername("plz","PLZ:");
        $atable->set_headername("ort","Ort:");
        $atable->set_headername("telefon","Telefon:");
        $atable->set_headername("email","E-Mail:");
        $atable->set_headername("internet","Homepage:");
        $atable->set_headername("type","Typ:");
        $atable->set_disabled ("type");

        $atable->set_headername("ad_username","AD Person:");
        $atable->set_disabled ("ad_username");


        $atable->enable_delete("Sind sie sicher das Sie die Person %s löschen wollen?","name");

        $atable->id_row = "person_id";


        return $this->card($atable->getContent(),$this->title);
    }
}