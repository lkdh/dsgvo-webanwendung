<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 21.03.2018
 * Time: 15:48
 */


class subpage_konto extends subpage {
    function getContent($page){
        ini_set("display_errors","0");
        $this->title = "Mein Konto";
        $ret = "User ID: <b>".$page->user->userid."</b><br>";
        $ret .= "Anrede: <b>".$page->user->initials."</b><br>";
        $ret .= "Vorname: <b>".$page->user->givenname."</b><br>";
        $ret .= "Nachname: <b>".$page->user->sn."</b><br>";

        $ret .= "Benutzername: <b>".$page->user->userprincipalname."</b><br>";

        $ret .= "E-Mail: <b>".$page->user->mail."</b><br>";
        $ret .= "Telefon: <b>".$page->user->telephonenumber."</b><br>";
        $ret .= "Fax: <b>".$page->user->facsimiletelephonenumber."</b><br>";

        $ret .= "Abteilung: <b>".$page->user->description."</b><br>";
        $ret .= "Behörde: <b>".$page->user->company."</b><br>";

        $ret .= "Primäre Organisation ID: <b>".$page->user->organisation_id."</b><br>";
        $ret .= "Primäre Gruppe ID: <b>".$page->user->gruppe_id."</b><br>";


        $ret .= "<br>Gruppen:<br>";

        foreach($page->user->gruppen as $gruppe)
        {
            if(strlen( $gruppe["gruppen_name"]) > 1)
                $ret .= "- <b>" . $gruppe["gruppen_name"] . " (".$gruppe["orga_name"].")</b><br>";
            else
                $ret .= "- <b>ID: " . $gruppe["gruppe_ad_name"]. " (".$gruppe["orga_name"].")</b><br>";

        }
        return $this->card($ret,$this->title);
    }
}

