<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 25.04.2018
 * Time: 14:23
 */

class subpage_verfaren_edit_gefahrenanalyse  extends  subpage{

    function getContent($page)
    {
        $verfahren = $this->sql->fetch_array("SELECT * FROM verfahren WHERE verfahren_id =".(int)$_GET["id"]);
        $this->title = $verfahren["bezeichnung"];


        $content = "Die Gefahrenanalyse bzw. Datenschutzfolgeabschätzung (DSFA) gem. Artikel 35 EU-DSGVO kann aus technischen Gründen derzeit noch nicht vorgenommen werden.<br> Sie erhalten eine Benachrichtigung, sobald diese Funktion bereitsteht!";

        $result = $this->card($content,"Risikobewertung");
        $result .= "<a class='btn btn-primary btn-sm btn btndefspace speichernbtnform'  href='#' onclick=\"location.href='index.php?s=verfahren_edit_abschluss&id=".(int)$_GET["id"]."'\">Nächster Schritt</a>";

        $wizzard = new wizzard();
        $wizzard->setHeader($verfahren["bezeichnung"]);

        $wizzard->setDefault();

        return $wizzard->getContent($result,7);
    }
}