<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 04.05.2018
 * Time: 08:58
 */

class subpage_admin_gruppen  extends subpage{

    function getContent($page)
    {
        $atable = new autotable();

        $atable->add_customrow("num_taetigkeiten");


        $this->title = "Gruppen";

        if(is_super_admin())
        {
            $where = "";

        }
        else
        {
            $where = "WHERE organisation_id = ".$_SESSION["user"]->organisation_id." AND ad_name != 'admin' and ad_name != 'superadmin'";
        }

        $atable->init("gruppe",array("gruppe_id","bezeichnung","alt_bezeichnung"),$where);

        $atable->set_headername("gruppe_id","ID#");
        $atable->set_headername("bezeichnung","Bezeichnung der Gruppe:");
        $atable->set_headername("alt_bezeichnung","Alternative Bezeichnung für Berichte:");
        $atable->table->default_sort_col_id = 1;

        $atable->set_headername("num_taetigkeiten","Anzahl Verarbeitungstätigkeiten");
        $atable->set_fieldfunction("num_taetigkeiten","gen_anz_taetigkeinten");

        function gen_anz_taetigkeinten($data){
            $mc = new mysql();
            $anz = $mc->fetch_array("SELECT count(*) as anz from verfahren WHERE gruppe_id = '".$data["gruppe_id"]."' ");
            return $anz["anz"];
        }


        $atable->edit = true;
        $atable->enable_delete("Sind sie sicher das Sie die Organisation %s löschen wollen?","bezeichnung");

        $atable->id_row = "gruppe_id";

        $info = "Gruppen werden automatisch durch das Active Directory angelegt. Wurde eine AD Gruppe neu angelegt, muss sich erst ein Benutzer der Gruppe einloggen, damit die Gruppe hier erscheint.";

        return $this->card($atable->getContent()."<i>".$info."</i>",$this->title);
    }
}