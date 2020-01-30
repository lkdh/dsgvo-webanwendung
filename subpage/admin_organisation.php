<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 04.05.2018
 * Time: 08:58
 */

class subpage_admin_organisation extends subpage{

    function getContent($page)
    {
        $this->title = "Datenkategorien";
        $atable = new autotable();
        $atable->add_customrow("imagepathpng");


        if($page->is_super_admin())
            $where = "";
        else
            $where = "WHERE organisation_id = ".$_SESSION["user"]->organisation_id;

        $atable->init("organisation",array("organisation_id","ad_name","bezeichnung","pronomen","anschrift","plz","ort","beschwerde_email","adsb_id","default_verantwortliche_person"),$where);

        $atable->set_headername("organisation_id","ID#");
        $atable->set_headername("pronomen","Pronomen für Fließtexte: \"der\" Landkreis Diepholz:");

        $atable->set_headername("bezeichnung","Name:");
        $atable->set_headername("anschrift","Anschrift:");
        $atable->set_headername("plz","PLZ:");
        $atable->set_headername("ort","Ort:");
        $atable->set_headername("beschwerde_email","E-Mail Adresse für Beschwerden Z.b. datenschutz@xyz.de:");


        $atable->set_headername("imagepathpng","Logo (1000px x 600px):");
        $atable->set_fieldfunction("imagepathpng","dokumente");


        function dokumente($data){
            //get_doc_modal_link($dokument_typ,$objectid,$headername = "Headername nicht definiert!",$dokumentname = false)
            return get_doc_modal_link("logo", $data["organisation_id"]," Organisation ".$data["bezeichnung"],"Logo");
        }

        $atable->set_headername("ad_name","Kurzname der Organisation im AD:");
        $atable->set_disabled("ad_name");

        $atable->set_headername("default_verantwortliche_person","Verantwortliche Person der Organisation (Nur bei Neuanlage) Sollte der HvB:");
        $atable->set_fieldsource("default_verantwortliche_person",getArrayUserByTypeandOrga("verantwortlich"));

        $atable->set_headername("adsb_id","Standard ADSB der Organisation (Nur bei Neuanlage):");
        $atable->set_fieldsource("adsb_id",getArrayUserByTypeandOrga("adsb"));

        $atable->edit = true;

        $atable->id_row = "organisation_id";


        return $this->card($atable->getContent(),$this->title);
    }
}