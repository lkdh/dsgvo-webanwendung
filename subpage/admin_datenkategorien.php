<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 04.05.2018
 * Time: 08:58
 */

class subpage_admin_datenkategorien  extends subpage{

    function getContent($page)
    {
        $this->title = "Datenkategorien";
        $atable = new autotable();
        $atable->init("datenkategorie",array("datenkategorie_id","name","beschreibung","gruppe_id"),"WHERE organisation_id = ".$_SESSION["user"]->organisation_id);

        $atable->set_headername("datenkategorie_id","ID#");
        $atable->set_headername("name","Kurzbezeichnung:");
        $atable->set_headername("beschreibung","Beschreibung der Enthaltenen Daten:");
        $atable->set_headername("gruppe_id","Berechtigt für:");


        $atable->set_fieldsource("gruppe_id",getArrayGruppenInThisOrga());

        $atable->edit = true;
        $atable->enable_delete("Sind sie sicher das Sie die Datenkategorie %s löschen wollen?","name");

        $atable->id_row = "datenkategorie_id";


        return $this->card($atable->getContent(),$this->title);
    }
}