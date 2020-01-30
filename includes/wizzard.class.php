<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 24.04.2018
 * Time: 17:17
 */

class wizzard{


    var $stages = array();
    var $verfahren_id = false;
    function addStage($name,$class)
    {
        $this->stages[] = array("name" => $name,"class" => $class);
    }

    function setHeader($text)
    {
        $this->header = $text;
    }
    var $header;
    function getContent($text,$step  = 0)
    {
                if ($this->verfahren_id == false)
                $verfahren_id = (int)$_GET["id"];
                else
                    $verfahren_id = $this->verfahren_id;

                $ret = "<div id=\"smartwizard\" class=\"sw-main sw-theme-arrows\">
                        <ul class=\"nav nav-tabs step-anchor\">";

                $pos = 1;
                foreach($this->stages as $stage)
                {
                    if($step == $pos)
                        $active = "active";
                    else
                        $active = "";

                    $ret .= "
                    <li class=\"nav-item ".$active."\">
                    <a href=\"?s=".$stage["class"]."&id=".$verfahren_id."\" class=\"nav-link\">
                    Schritt ".$pos."<br>
                    <div class='wiz_nav_subline'>".$stage["name"]."</div>
                    </a>
                    </li>";
                    $pos++;
                }

                        $ret .= "
                        </ul>
                        <div class=\"sw-container tab-content\" style=\"min-height: 24px;\">
                        <div id=\"step-1\" class=\"tab-pane step-content\" style=\"display: block;\">
                        <h1 class='wizzard_header'>".$this->header."</h1>
                        ".$text."
                        </div>
  
                        </div>
                       ";
            return $ret;
    }

    function setDefault()
    {
        $this->addStage("Stammdaten","verfahren_edit");
        $this->addStage("EDV-Unterstützung","verfahren_edit_software");
        $this->addStage("Personenkreise, Datenkategorien","verfahren_edit_betroffene_personen");
        $this->addStage("Weitergabe von Daten","verfahren_edit_empfaenger");
        $this->addStage("TOM","verfaren_edit_technische_massnahmen");
        $this->addStage("Dokumente","verfahren_edit_dokumente");
        $this->addStage("Risikobewertung","verfaren_edit_gefahrenanalyse");
        $this->addStage("Abschluss","verfahren_edit_abschluss");

    }

}