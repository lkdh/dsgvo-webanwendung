<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 27.03.2018
 * Time: 15:29
 *
 *
 */

class subpage_verfahren_edit extends subpage
{
    function getContent()
    {


        if(!verfahren_right_read())
        {
            return $this->card("Sie besitzen nicht die notwendigen Rechte um diese Verarbeitungstätigkeit lesen zu dürfen.","Zugriff verweigert!");
        }

        $id = (int)$_GET["id"];
        $verfahren = $this->sql->fetch_array("SELECT * FROM verfahren WHERE verfahren_id =".(int)$_GET["id"]);
        $this->title = $verfahren["bezeichnung"];

        $form = new form("verfahren-edit",verfahren_right_write(),true);
        $form->setTargetClassFunction("subpage_verfahren_edit","saveedit");

        $form->add_textbox("Bezeichnung der Verarbeitungstätigkeit",$verfahren["bezeichnung"],"","","text","kurzbeschreibung",verfahren_right_write());

        $form->add_textarea("Zweck der Datenverarbeitung (gem. Artikel 13 DSGVO Abs. 1c oder Artikel 14 Abs. 1c)", $verfahren["beschreibung"],"
        Hinweis: Diese eingegebene Kurzbeschreibung wird dem Bürger im Infoblatt angezeigt<br>
        Beispiel:
       Aufgrund des abgeschlossenen Mietvertrages ist es erforderlich, dass wir Ihre
personenbezogenen Daten erheben und verarbeiten. Deshalb sind Sie verpflichtet, die
personenbezogenen Daten zur Verfügung zu stellen. Sollten Sie die Daten nicht zur
Verfügung stellen, ist es uns nicht möglich, Ihnen Räume zu vermieten.","beschreibung",verfahren_right_write());

        $daarr = array(
            1 => "(a) Eine Einwilligung zur Verarbeitung wurde erteilt",
            2 => "(b) Die Verarbeitung ist zur Erfüllung eines Vertrages oder vorvertraglicher Maßnahmen erforderlich",
            3 => "(c) Die Verarbeitung ist zur Erfüllung einer rechtlichen Verpflichtung erforderlich",
            4 => "(d) Die Verarbeitung ist erforderlich, um lebenswichtige Interessen der betroffenen Person oder einer anderen natürlichen Person zu schützen",
            5 => "(e) Die Verarbeitung ist für die Wahrnehmung einer Aufgabe erforderlich, die im öffentlichen Interesse liegt oder in Ausübung öffentlicher Gewalt erfolgt",
            6 => "(f) Zur Verfolgung von Straftaten oder Ordnungswidrigkeiten, zur Vollstreckung oder zum Vollzug von Strafen oder Maßnahmen im Sinne des § 11 Abs. 1 Nr. 8 des Strafgesetzbuchs (StGB) oder von  Erziehungsmaßregeln oder Zuchtmitteln im Sinne des Jugendgerichtsgesetzes oder zur Vollstreckung von  Bußgeldentscheidungen");


        $form->add_checkboxen("Rechtmäßigkeit der Verarbeitung (gem. Artikel 6 DSGVO Abs. 1 Buchstaben a–f)",$verfahren,$daarr,"","art6",verfahren_right_write());

        $anz = $this->sql->fetch_array("SELECT count(*) as anz FROM datenkategorie as d, verfahren_datenkategorie as vd WHERE vd.verfahren_id = '".$id."' AND vd.datenkategorie_id = d.datenkategorie_id AND d.besondere_kategorie = 1");
        if($anz["anz"] > 0) {
            $daarr = array(
                1 => "Ziff.1 zur Wahrnehmung von Rechten und Pflichten, die aus dem Recht der sozialen Sicherheit und des Sozialschutzes folgen",
                2 => "Ziff.2 zur Wahrnehmung von Rechten und Pflichten der öffentlichen Stellen auf dem Gebiet des Dienst- und Arbeitsrechts",
                3 => "Ziff.3 zum Zweck der Gesundheitsvorsorge oder der Arbeitsmedizin, für die Beurteilung der Arbeitsfähigkeit von beschäftigten Personen, für die medizinische Diagnostik, die Versorgung oder Behandlung im Gesundheits-  oder Sozialbereich oder für die Verwaltung von Systemen und Diensten im Gesundheits- und Sozialbereich oder aufgrund eines Vertrags der betroffenen Person mit einer oder einem Angehörigen eines Gesundheitsberufs, wenn diese Daten von ärztlichem Personal oder durch sonstige Personen, die einer  Geheimhaltungspflicht unterliegen, oder unter der Verantwortung verarbeitet  werden",
                4 => "Ziff.4 aus Gründen des öffentlichen Interesses im Bereich der öffentlichen  Gesundheit und des Infektionsschutzes, wie dem Schutz vor schwerwiegenden grenzüberschreitenden Gesundheitsgefahren oder zur Gewährleistung hoher Qualitäts- und Sicherheitsstandards bei der Gesundheitsversorgung und bei Arzneimitteln und Medizinprodukten; ergänzend zu den in den Absätzen 2 und 3 genannten Maßnahmen sind insbesondere die  berufsrechtlichen und strafrechtlichen Vorgaben zur Wahrung des Berufsgeheimnisses einzuhalten",
                5 => "Ziff.5 zur Abwehr erheblicher Nachteile für das Gemeinwohl oder von Gefahren für die öffentliche Sicherheit und Ordnung",
                6 => "Ziff.6 zur Verfolgung von Straftaten oder Ordnungswidrigkeiten, zur Vollstreckung oder zum Vollzug von Strafen oder Maßnahmen im Sinne des  § 11 Abs. 1 Nr. 8 des Strafgesetzbuchs (StGB) oder von Erziehungsmaßregeln oder Zuchtmitteln im Sinne des Jugendgerichtsgesetzes oder zur Vollstreckung von Bußgeldentscheidungen.");

            $form->add_checkboxen("Rechtsgrundlagen der Verarbeitung (§17 Abs. 1 Niedersächsisches Datenschutzgesetz)", "", $daarr, "Da die Verarbeitungstätigkeit \"besonderer Kategorien personenbezogener Daten\" beinhaltet, muss angegeben werden auf welcher Grundlage diese Verarbeitet werden!", false, verfahren_right_write());
        }

        $daarr =array(0 => "Bitte wählen ...",
                        13 => "Die Datenerhebung erfolgt direkt (Artikel 13 DSGVO) – z.B. durch Antrag des Bürgers oder durch Fragebogen",
                        14 => "Die Datenerhebung erfolgt indirekt (Artikel 14 DSGVO) – d.h. durch eine andere Institution, Einrichtung, z.B. Finanzamt, Krankenkasse, Unternehmen, andere Behörde, etc.",
                      1314 => "Die Datenerhebung erfolgt direkt und indirekt (Artikel 13 & 14)"
    );

        $form->add_select("Wie werden die Personendaten erhoben?",$verfahren["art1314"],$daarr,"Entscheidung ob Artikel 13 oder Artikel 14 DSGVO Anwendung findet","art1314",verfahren_right_write(),"art1314selector");

        $form->add_textbox("Durch welche Institution oder Einrichtung wird die Datenerhebung wahrgenommen? (vgl.  Antwort in Ziffer 4)",$verfahren["art14_unternehmen"],"","","text","art14_unternehmen",verfahren_right_write(),false,true);

        $form->add_textbox("Rechtliche Grundlage",$verfahren["rechtliche_grundlage"],"","Gibt es eine Rechtsgrundlage, die eine Verarbeitung der Daten rechtfertig? Wenn ja, benennen Sie bitte die rechtliche Grundlage.<br>Bei freiwilliger Tätigkeit bitte leer lassen.","text",false,verfahren_right_write());

        $form->add_hidden("id",$_GET["id"]);
        $form->add_textbox("Datum der Einführung",$verfahren["datum_einfuehrung"],"","Seit wann wird die Verarbeitungstätigkeit ausgeübt?","date",false,verfahren_right_write());

        $form->add_AJAXselect("Verantwortliche Person",$verfahren["id_verantwortlich"],"daten","ajax_get_person","verantwortlich",$helptext = "","ajax_add_persondata_inmodal",false,verfahren_right_write());
        $form->add_AJAXselect("Datenschutzbeauftragter",$verfahren["id_adsb"],"daten","ajax_get_person","adsb",$helptext = "","ajax_add_persondata_inmodal",false,verfahren_right_write());


        $gruppe_data = $this->sql->fetch_array("SELECT * FROM gruppe WHERE gruppe_id = '".$verfahren["gruppe_id"]."'");

        $form->add_textbox("Verantwortliche Organisationseinheit",$gruppe_data["bezeichnung"],"","","text","",false);

        $form->add_AJAXselect("Ansprechpartner in der Organisationseinheit bei Rückfragen",$verfahren["id_ansprechpartner"],"daten","ajax_get_person","ansprechpartner",$helptext = "","ajax_add_persondata_inmodal","verfahren-edit_ansprechparterfrdieverarbeitungsttigkeitimfachdienst",verfahren_right_write());

        $form->buttontext = "Speichern";

        $wizzard = new wizzard();
        $wizzard->setHeader($verfahren["bezeichnung"]);

        $wizzard->setDefault();

        $form->disablebtn = true;
        $form->disablebtnurl = "index.php?s=verfahren_edit_software&id=".(int)$_GET["id"];
        return $wizzard->getContent($this->card($form->getContent(),"Stammdaten"),1);
    }

    function saveedit($data)
    {

        if(!isset($data["art6_1"]))
            $data["art6_1"] = 0;
        if(!isset($data["art6_2"]))
            $data["art6_2"] = 0;
        if(!isset($data["art6_3"]))
            $data["art6_3"] = 0;
        if(!isset($data["art6_4"]))
            $data["art6_4"] = 0;
        if(!isset($data["art6_5"]))
            $data["art6_5"] = 0;
        if(!isset($data["art6_6"]))
            $data["art6_6"] = 0;

        $ms = new mysql();
        $updates = array(
            "id_ansprechpartner" => $data["verfahren-edit_ansprechparterfrdieverarbeitungsttigkeitimfachdienst"],
            "rechtliche_grundlage" => $data["verfahren-edit_rechtlichegrundlage"],
            "bezeichnung" => $data["kurzbeschreibung"],
            "beschreibung" => $data["beschreibung"],
            "art6_1" => $data["art6_1"],
            "art6_2" => $data["art6_2"],
            "art6_3" => $data["art6_3"],
            "art6_4" => $data["art6_4"],
            "art6_5" => $data["art6_5"],
            "art6_6" => $data["art6_6"],
            "art1314" => $data["art1314"],
            "art14_unternehmen" => $data["art14_unternehmen"],
            "datum_einfuehrung" => $data["verfahren-edit_datumdereinfhrung"],
            "id_verantwortlich" => $data["verfahren-edit_verantwortlicheperson"],
            "id_adsb" => $data["verfahren-edit_datenschutzbeauftragter"],
        );
        
       $ms->updateRow("verfahren",$updates,"verfahren_id",$data["verfahren-edit_id"]);
           return json_encode(array("status"=> 1,"alert" => "1","location" => "index.php?s=verfahren_edit_software&id=".$data["verfahren-edit_id"]));


    }
}
