<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 23.03.2018
 * Time: 12:16
 */

class subpage_verfahren_add extends subpage
{
    function getContent($page)
    {
        $this->title = "Neue Verarbeitungstätigkeit anlegen";
        $page->setHeader($this->title);

        $form = new form("verfahren-add");

        $form->add_textbox("Zweck der Verarbeitung","","","Je Verarbeitung muss vorher der Zweck festgelegt werden.<br>
        Der Zweck muss eindeutig und so aussagekräftig sein, dass die Aufsichtsbehörde die Angemessenheit der getroffenen Schutzmaßnahmen und die Zulässigkeit der Verarbeitung vorläufig einschätzen kann.
        <br><br><b>Beispiele:</b><br>
- Personalaktenführung/Stammdaten<br>
- Lohn-, Gehalts- und Bezügeabrechnung<br>
- Arbeitszeiterfassung<br>
- Urlaubsdatei<br>
- Nutzungsprotokollierungen IT/Internet/E-Mail<br>
- Bewerbungsverfahren<br>
- Telefondatenerfassung<br>
- Firmenparkplatzverwaltung<br>
- Videoüberwachung an Arbeitsplätzen, in Schulen etc.<br>
- Schülerverwaltung, Unterrichtsplanung, Zeugniserstellung<br>
- Beschaffung/Einkauf sowie Finanzbuchhaltung<br>
- Antragsbearbeitung (Bauanträge, Wohngeldanträge etc.)<br>
- Rats- und Bürgerinformationssysteme<br>
- Meldewesen (Melderegister)<br>
- Fahrerlaubnisregister und Fahrzeugregister<br>
- Wahlen (Wählerverzeichnis)<br>
- amtsärztliche Untersuchungen<br>
- Schwangeren- und Mütterberatung<br>
- Erfassung und Überwachung der nichtakademischen Heilberufe<br>
","text",false,true);



            $group = array();
            foreach($_SESSION["user"]->gruppen as $grup)
            {
                if($grup["gruppe_ad_name"] != "admin") {
                    $group[$grup["gruppe_id"]] = $grup["gruppen_name"];
                }
            }

        if(count($group) > 1) {

               $form->add_select("Verantwortliche Fachabteilung", "", $group, "Für welche Organisation soll die neue Verfahrensbeschreibung angelegt werden?",false,true);
        }
        else
            $form->add_hidden("Gruppe",$_SESSION["user"]->gruppen[0]["gruppe_ad_name"]);


        $form->setTargetClassFunction("subpage_verfahren_add","ajax_add_verfaren");
        $form->buttontext = "Speichern und weiter";

        $form->elements[] = "<a class='btn btn-primary btn-sm' href='#' onclick=\"send_form('".$form->targetclass."','".$form->targetfunction."','form_".$form->formname."','backtoindex')\">Speichern</a> ";

        return $this->card($form->getContent(),"");
    }

    function ajax_add_verfaren($data)
    {
        if(strlen($data["verfahren-add_zweckderverarbeitung"]) == 0)
        {
            return json_encode(array("status" => "2", "header" => "Bitte alle Felder ausfüllen!","msg" => "Bitte eine Bezeichnung angeben!"));
        }

        if(isset($data["verfahren-add_verantwortlichefachabteilung"])) {
            $datagruppe = $this->sql->fetch_array("SELECT * FROM gruppe WHERE gruppe_id = '" . $data["verfahren-add_verantwortlichefachabteilung"] . "'");
            $gruppe_id = $datagruppe["gruppe_id"];
            $orga_id =  $datagruppe["organisation_id"];
        }
        else
            {
                $gruppe_id = $_SESSION["user"]->gruppe_id ;
                $orga_id =  $_SESSION["user"]->organisation_id;
            }

        $dataorga = $this->sql->fetch_array("SELECT * FROM organisation WHERE organisation_id = '".$orga_id."'");

        $sql = "insert into verfahren (bezeichnung,gruppe_id,id_adsb,id_verantwortlich,datum_einfuehrung) VALUES ('".$data["verfahren-add_zweckderverarbeitung"]."','".$gruppe_id."','".$dataorga["adsb_id"]."','".$dataorga["default_verantwortliche_person"]."','0000-00-00')";
        if($this->sql->query($sql))
        {
            add_protokoll("add",$this->sql->getID(),"verfahren");
            add_protokoll("edit",$this->sql->getID(),"verfahren","",$data["verfahren-add_zweckderverarbeitung"],"bezeichnung");
            add_protokoll("edit",$this->sql->getID(),"verfahren","",$gruppe_id,"gruppe_id");
            if(isset($data["extradata"]))
                return json_encode(array("status" => "1", "location" => "?s=verfahren"));
            else
                return json_encode(array("status" => "1", "location" => "?s=verfahren_edit&id=".$this->sql->getID()));

        }
        else
        {
            return json_encode(array("status" => "0", "msg" => $this->sql->getError()));
        }
    }
}