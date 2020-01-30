<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 02.05.2018
 * Time: 13:17
 */
class vorgang extends FPDF
{
    var $personengruppen = array();
    var $sql;
    var $data;
    var $anzpages = 3;

    var $fontsize_subheaders = 10;
    var $line_subheader = 5;

    var $fontsize_text = 9;
    var $line_text = 4;

    var $font = "Arial";

    var $blockoffset = 1;


    var $headertext;
    var $id;
    var $person_id;

    function add_header($txt,$person_id)
    {
        $this->person_id = $person_id;
        $this->headertext = $txt;
    }

    function Header(){
        $this->SetFont($this->font,'B',16);
        $this->MultiCell(190,7,$this->utf($this->headertext),1,'C',false);

        $persondata = array();
        $persondata1 = array();


        if($this->person_id != 0) {
            $person = $this->sql->fetch_array("SELECT * FROM personen WHERE person_id = '" . $this->person_id . "'");

            if(strlen($person["ad_username"]) > 0) {

                $altname = $person["name"];

                $person = $this->sql->fetch_array("SELECT * FROM ad_personen WHERE ad_username = '" . $person["ad_username"] . "'");

                if(strlen($altname) > 0)
                    $person["name"] = $altname;
            }

            if(isset($person["name"]))
                $persondata1[] = $person["name"];

            if(isset($person["strasse"]))
                $persondata1[] = $person["strasse"];

            if(isset($person["plz"]) AND isset($person["ort"]))
                $persondata1[] = $person["plz"]." ".$person["ort"];

            if(isset($person["email"]))
                $persondata[] = "E-Mail: ".$person["email"];

            if(isset($person["telefon"]))
                $persondata[] = "Telefon: ".$person["telefon"];
        }
            $this->SetFont($this->font,'',8);
            $x = $this->GetX();
            $y = $this->GetY();
            $this->cell(190,4,$this->utf( "Ansprechpartner: ".implode("; ",$persondata1)),0,'L',false);

            $this->setY($y+4);
            $this->SetX($x);
            $this->cell(190,4,$this->utf( implode("; ",$persondata)),0,'L',false);

            $this->setY($y);
            $this->SetX($x);
            $this->cell(190,8,"",1,'L',false);

            $this->setY($y+8);





#        if($this->headert1 != "0000-00-00")
 #        $this->add_simple_block("Datum der Einführung", date("d.m.Y",strtotime($this->headert1)),120,8);
  #      else
   #         $this->add_simple_block("Datum der Einführung", "unbekannt",120,8);
#
 #       $this->add_simple_block("Letzte Änderung", date("d.m.Y",$this->headert2),70,8);
        $this->setY($this->getY()+5);
    }

     function utf($intext)
    {
        return utf8_decode($intext);
    }

    function key_value_line($key,$value, $border = false,$offsetblock = -1,$blockspacing = 0)
    {
        $oldX = $this->GetX();
        if(strlen($value) > 0) {

            if($offsetblock != -1)
                $this->SetX($offsetblock);

            $y = $this->GetY();
            $this->SetFont($this->font, 'b', $this->fontsize_text);
            $this->Write($this->line_text, $this->utf($key));
            $this->SetFont($this->font, '', $this->fontsize_text);
            $this->Write($this->line_text, $this->utf($value));
            $this->ln();
            $yend = $this->GetY();

            if($border)
            {
                $nheigt = $this->GetY() - $y ;
                $this->setY($y);

                if($offsetblock != -1)
                    $this->SetX($offsetblock);

                $this->Cell(0, $nheigt, '', 1, 1, 'C');
                $this->setY($yend);
            }

            $this->SetY($this->getY()+$blockspacing);

        }
        if($offsetblock != -1)
            $this->SetX($oldX);

    }

    function key_line($style,$value)
    {
        $this->SetFont($this->font,$style,$this->fontsize_subheaders);
        $this->Write($this->line_subheader,$this->utf($value));
        $this->ln();
    }

    function add_Datablock($headertext,$datas = false,$nolisting = false)
    {

        if($this->getY() > 240)
            $this->AddPage();

        if($datas != false) {

            if(strlen($headertext) > 0) {
                $this->SetFont($this->font,'b',$this->fontsize_subheaders);
                $this->key_line('B', $headertext);
            }

            foreach($datas as $key => $value) {
                if($nolisting)
                {
                    $this->key_value_line(  "- ", $value);
                }
                else {
                    if (strlen($key) > 0)
                        $this->key_value_line( $key . ': ', $value);
                    else
                        $this->key_value_line( "", $value);
                }
            }

            $this->setY($this->getY()+$this->blockoffset);

        }
    }

    function add_Datablock_simple($headertext,$datas = false,$border = false,$offsetblock = -1,$blockspacing = 0)
    {
        if($datas != false) {
            $lh = 8;

            $this->key_line('B',  $headertext);

            foreach($datas as $key => $value) {
                $this->key_value_line( '', $value,$border,$offsetblock,$blockspacing);
            }

            $this->setY($this->getY()+$this->blockoffset);
        }
    }

    function add_datenkat_block($row,$verfahren_id)
    {

        if($this->getY() > 240)
            $this->AddPage();


        $mc = new mysql();
        $data = array();

        $offset_left = 15;

        $this->setX($offset_left);


        $startpos = $this->GetY();
        $this->SetFont($this->font, 'b', $this->fontsize_text);
        $this->MultiCell(160, 4, utf8_decode( $row["name"]), 0, 'L', 0);
        $this->SetFont($this->font, '', $this->fontsize_text);

            if(strlen( $row["beschreibung"]) > 2) {
                $this->setX($offset_left);
                $this->MultiCell(160, 4, utf8_decode($row["beschreibung"]), 0, 'L', 0);
            }

            if(strlen( $row["loeschfristen"]) > 2) {
                $this->setX($offset_left);

                $this->key_value_line( 'Löschung: ',$row["loeschfristen"]);
            }

        if($row["besondere_kategorie"] == 1) {
            $this->setX($offset_left);
            $this->key_value_line( '', 'Besondere Kategorie gem. Art. 9 Abs 1 DS-GVO');
        }


            $betr_res = $mc->query("SELECT * FROM verfahren_datenkategorie as vd, datenkategorie_personengruppe as dp, verfahren_personengruppe as vp
            WHERE vd.verfahren_id = ".$verfahren_id." AND vd.datenkategorie_id = ".$row["datenkategorie_id"]." AND vd.vd_id = dp.verfahren_datenkategorie_id AND dp.verfahren_personengruppe_id = vp.vp_id GROUP by vp.personengruppe_id ");
            $personengruppen_ids = array();
            while($personengruppen = mysqli_fetch_array($betr_res,MYSQLI_ASSOC))
            {
                $personengruppen_ids[] = "Ziffer ".$this->personengruppen[$personengruppen["personengruppe_id"]];
            }
            asort($personengruppen_ids);

            $this->setX($offset_left);
            $this->key_value_line('Betroffene Personen: ',implode(", ",$personengruppen_ids));

            $endpos = $this->getY();
            $this->setY($startpos);
            $this->setX($offset_left);
            $this->Cell(0, $endpos - $startpos, '', 1, 1, 'C');

            $this->setY($this->getY()+$this->blockoffset);

    }

    function add_Person($headertext,$person_id)
    {

        if($person_id != 0) {
            $person = $this->sql->fetch_array("SELECT * FROM personen WHERE person_id = '" . $person_id . "'");

            if(strlen($person["ad_username"]) > 2) {

                    $altname = $person["name"];

                $person = $this->sql->fetch_array("SELECT * FROM ad_personen WHERE ad_username = '" . $person["ad_username"] . "'");

                if(strlen($altname) > 0)
                $person["name"] = $altname;
            }


            $this->key_line("b", $headertext);

            $persondata = array();
            if(isset($person["name"]))
                $persondata[] = $person["name"];

            if(isset($person["strasse"]))
                $persondata[] = $person["strasse"];

            if(isset($person["plz"]) AND isset($person["ort"]))
                $persondata[] = $person["plz"]." ".$person["ort"];

            $this->key_value_line('', implode("; ",$persondata));

            $persondata = array();
            if(isset($person["email"]))
                $persondata[] = "E-Mail: ".$person["email"];

            if(isset($person["telefon"]))
                $persondata[] = "Telefon: ".$person["telefon"];

            $this->key_value_line('', implode("; ",$persondata));


                $this->setY($this->getY()+$this->blockoffset);
        }
    }
    function add_simple_block($key, $value,$width,$height)
    {
        $this->SetFont($this->font,'',$this->fontsize_text);
        $this->Cell($width, $height, $this->utf($key.": ".$value), 1, 0);
    }
}

function gen_infopdf($verfahren_id,$id)
{
    $pdf = new vorgang();
    $mc = new mysql();
    $pdf->sql = $mc;
    $pdf->data = $mc->fetch_array("SELECT * FROM verfahren WHERE verfahren_id = '" . $verfahren_id . "'");

    $pdf->SetFont('Arial', '', 14);

    $pdf->add_header($pdf->data["bezeichnung"], $pdf->data["id_ansprechpartner"]);
    $pdf->AddPage();

    $pdf->add_Datablock("Zweck der Verarbeitung (Art. 30 Abs. 1 S. lit b)", array("" => $pdf->data["bezeichnung"]));

    if (strlen($pdf->data["rechtliche_grundlage"]) > 0)
        $pdf->add_Datablock("Rechtliche Grundlage", array("" => $pdf->data["rechtliche_grundlage"]));

    $pdf->add_Person("Angaben zum Verantwortlichen", $pdf->data["id_verantwortlich"]);
    $pdf->add_Person("Angaben zum Vertreter des Verantwortlichen", $pdf->data["id_verantwortlichstellv"]);
    $pdf->add_Person("Angaben zur Person des Datenschutzbeauftragten", $pdf->data["id_adsb"]);


    $gruppe = $mc->fetch_array("SELECT * FROM gruppe WHERE gruppe_id = '" . $pdf->data["gruppe_id"] . "'");


    if(isset( $gruppe["alt_bezeichnung"]))
         $pdf->add_Datablock("Verantwortliche Organisationseinheit (Art. 30 Abs. 1 S. lit 1)",
        array("" => $gruppe["bezeichnung"].", ". $gruppe["alt_bezeichnung"],
        ));
    else
        $pdf->add_Datablock("Verantwortliche Organisationseinheit (Art. 30 Abs. 1 S. lit 1)",
            array("" => $gruppe["bezeichnung"],
            ));

##################################

    $data = array();
    $res = $mc->query("SELECT * FROM verfahren_software as vs, software as s WHERE vs.verfahren_id = " . $verfahren_id . " AND  vs.software_id = s.software_id");
    while ($row = mysqli_fetch_array($res)) {
        $data[] = "- " . $row["name"];
    }
    $pdf->add_Datablock_simple("Eingesetzte EDV-Verfahren", $data);

##################################

    $data = array();
    $res = $mc->query("SELECT * FROM verfahren_personengruppe as vp, personengruppen as p WHERE vp.verfahren_id = " . $verfahren_id . " AND  vp.personengruppe_id = p.personengruppe_id");
    echo $mc->getError();
    $pos = 1;
    while ($row = mysqli_fetch_array($res)) {
        $pdf->personengruppen[$row["personengruppe_id"]] = $pos;

        if (strlen($row["anzahl_personen"]) > 2) {
            if (is_numeric($row["anzahl_personen"])) {
                $personen = " (~ " . $row["anzahl_personen"] . " Personen)";
            } else
                $personen = " (" . $row["anzahl_personen"] . ")";

        } else
            $personen = "";

        $data[] = " Ziffer ". $pos++ . ") " . $row["bezeichnung"] . $personen;
    }

    $pdf->add_Datablock_simple("Kategorien betroffener Personen (Art. 30 Abs. 1 S. 2 lit. c)", $data,true,15,1);

##################################


    $res = $mc->query("SELECT * FROM verfahren_datenkategorie as vd, datenkategorie as d WHERE vd.verfahren_id = " . $verfahren_id . " AND  vd.datenkategorie_id = d.datenkategorie_id");

    if (mysqli_num_rows($res) > 0) {
        if ($pdf->getY() > 240)
            $pdf->AddPage();

        $pdf->SetFont($pdf->font, 'b', $pdf->fontsize_subheaders);
        $pdf->Cell(120, $pdf->line_subheader, "Kategorien personenbezogenen Daten (Art. 30 Abs. 1 S. 2 lit. c)", 0, 1);
    }

    while ($row = mysqli_fetch_array($res)) {
        $pdf->add_datenkat_block($row, $verfahren_id);
    }

##################################

    $res = $mc->query("SELECT * FROM weitergabe as w, verfahren_weitergabe as vw WHERE vw.weitergabe_id = w.weitergabe_id AND vw.verfahren_id = '" . $verfahren_id . "'");
    echo $mc->getError();
    $datas = array();
    while ($row = mysqli_fetch_array($res)) {
        $datas[$row["type"]][] = $row["name"];
    }

    if (isset($datas["fachdienst"]))
        $pdf->add_Datablock("Datenweitergabe innerhalb der Organisation (Art. 30 Abs. 1 S. 2 lit. d)", $datas["fachdienst"], true);

    if (isset($datas["extern"]))
        $pdf->add_Datablock("Datenweitergabe an externe Organisationen (Art. 30 Abs. 1 S. 2 lit. d)", $datas["extern"], true);

    if (isset($datas["drittland"]))
        $pdf->add_Datablock("Datenweitergabe an Organisation in Drittland (Art. 30 Abs. 1 S. 2 lit. d)", $datas["drittland"], true);


    $sql = "select * from massnahmen WHERE  
                (massnahme_id IN (SELECT massnahme_id FROM verfahren_massnahmen WHERE verfahren_id = '" . $verfahren_id . "') 
                OR massnahme_id IN (SELECT massnahme_id FROM software_massnahmen as sm, verfahren_software as vs WHERE vs.verfahren_id = '" . $verfahren_id . "' AND sm.software_id = vs.software_id)) GROUP BY massnahme_id";

    $res = $mc->query($sql);

    $datas = array();
    while ($row = mysqli_fetch_array($res)) {
        $datas[] = $row["name"];
    }

    if (count($datas) > 0)
        $pdf->add_Datablock("Technische und organisatorische Maßnahmen gemäß Art. 32 Abs. 1 DSGVO", $datas, true);


    $pdf->anzpages = $pdf->getY();

    $pdf->AliasNbPages('{totalPages}');

    $pdf->Output("F",PDF_FOLDER . "info_" . $verfahren_id . ".pdf");
}
?>