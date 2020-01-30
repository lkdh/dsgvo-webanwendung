<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 04.05.2018
 * Time: 08:58
 */

class subpage_admin_tom  extends subpage{

    function getTable()
    {
        $this->title = "Technische / Organisatorische Maßnahmen";
        $atable = new autotable();

        $atable->add_customrow("num_verwendung");
        $atable->add_customrow("dokumente");


        $atable->init("massnahmen",array("massnahme_id","adv_vertrag","name","type","shortcode","gruppe_id"),"WHERE organisation_id = ".$_SESSION["user"]->organisation_id);
        $atable->set_headername("massnahme_id","ID#");
        $atable->set_headername("adv_vertrag","AD-Vertrag?");
        $atable->set_fieldsource("adv_vertrag",array("1" => "Ja","0" => "Nein"));

        $atable->set_headername("type","Art");
        $atable->set_headername("shortcode","Kurzbezeichnung (Import)");
        $atable->set_headername("gruppe_id","Berechtigt für");

        $atable->set_fieldsource("type",array("technisch" => "Technisch","organisatorisch" => "Organisatorisch"));

        $atable->set_fieldsource("gruppe_id",getArrayGruppenInThisOrga());

        $atable->set_headername("type","Art");

        $atable->set_headername("num_verwendung","Anzahl Verknüpfungen");
        $atable->set_fieldfunction("num_verwendung","num_verwendung");

        $atable->set_headername("dokumente","Dokumente");
        $atable->set_fieldfunction("dokumente","dokumente");


        function dokumente($data){
            return get_doc_modal_link("tom", $data["massnahme_id"],$data["name"]);
        }

        function num_verwendung($data){
            $mc = new mysql();
            $msoftware = $mc->fetch_array("SELECT COUNT(*) as anz from software_massnahmen WHERE massnahme_id = '".$data["massnahme_id"]."'");
            $mverfahren = $mc->fetch_array("SELECT COUNT(*) as anz from verfahren_massnahmen WHERE massnahme_id = '".$data["massnahme_id"]."'");
            return "Direkt: ".$mverfahren["anz"]." / Über Software: ".$msoftware["anz"];
        }

        $atable->edit = true;
        $atable->enable_delete("Sind sie sicher das Sie die TOM %s löschen wollen?","name");

        $atable->id_row = "massnahme_id";


        return $this->card($atable->getContent(),$this->title);
    }
    function getContent($page){
        return $this->getAsyncContent("subpage_admin_tom","getTable");
    }
}