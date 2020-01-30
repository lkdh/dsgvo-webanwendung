<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 30.10.2018
 * Time: 09:01
 */
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

require_once('includes/fpdi/autoload.php');


class pdfinfoblatt extends Fpdi
{

    var $font = 'Arial';
    var $fontsize_text = 8;
    var $fontsize_subheaders;
    var $line_subheader;
    var $line_text;
    var $verfahren;
    var $gruppe;
    var $organisation;
    var $blockoffset;
    var $lineheight = 3;
    var $minrestheight = 250;
    var $offsetright = 100;
    var $offsettop = 55;

    var $stand = 0;

    protected $namedDestinations = array();
    protected $n_namedDestinations;

    function SetLink($link, $y = 0, $page = -1)
    {
        if (strpos($link, '#') !== 0) {
            parent::SetLink($link);
        } else {
            // Set destination of internal link
            if ($y == -1)
                $y = $this->y;
            if ($page == -1)
                $page = $this->page;
            $this->namedDestinations[substr($link, 1)] = array($page, $y);
        }
    }

    function _putnamedDestinations()
    {
        $s = array();
        if ($this->DefOrientation == 'P') {
            $hPt = $this->DefPageSize[1] * $this->k;
        } else {
            $hPt = $this->DefPageSize[0] * $this->k;
        }

        foreach ($this->namedDestinations as $name => $namedDestinations) {
            $h = isset($this->PageInfo[$namedDestinations[0]]['size']) ? $this->PageInfo[$namedDestinations[0]]['size'][1] : $hPt;
            $this->_newobj();
            $this->_put(sprintf('[%d 0 R /XYZ 0 %.2F null]', 1 + 2 * $namedDestinations[0], $h - $namedDestinations[1] * $this->k));
            $this->_put('endobj');

            $s[$name] = $this->_textstring($name) . ' ' . $this->n . ' 0 R';
        }
        $this->_newobj();
        $this->n_namedDestinations = $this->n;
        $this->_put('<<');
        ksort($s);
        $this->_put('/Names [' . join(' ', $s) . ']');
        $this->_put('>>');
        $this->_put('endobj');
    }

    function _putresources()
    {
        parent::_putresources();
        if(!empty($this->namedDestinations))
            $this->_putnamedDestinations();
    }

    function _putcatalog()
    {
        parent::_putcatalog();
        if(!empty($this->namedDestinations))
            $this->_put('/Names <</Dests '.$this->n_namedDestinations.' 0 R>>');
    }

    protected function _putpage($n)
    {
        $this->_newobj();
        $this->_put('<</Type /Page');
        $this->_put('/Parent 1 0 R');
        if(isset($this->PageInfo[$n]['size']))
            $this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageInfo[$n]['size'][0],$this->PageInfo[$n]['size'][1]));
        if(isset($this->PageInfo[$n]['rotation']))
            $this->_put('/Rotate '.$this->PageInfo[$n]['rotation']);
        $this->_put('/Resources 2 0 R');
        if(isset($this->PageLinks[$n]))
        {
            // Links
            $annots = '/Annots [';
            foreach($this->PageLinks[$n] as $pl)
            {
                $rect = sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
                $annots .= '<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
                if(is_string($pl[4])) {
                    if (strpos($pl[4], '#') === 0) {
                        $annots .= '/A <</S /GoTo /D ' . $this->_textstring(substr($pl[4], 1)) . '>>>>';
                    } else {
                        $annots .= '/A <</S /URI /URI ' . $this->_textstring($pl[4]) . '>>>>';
                    }
                }
                else
                {
                    $l = $this->links[$pl[4]];
                    if(isset($this->PageInfo[$l[0]]['size']))
                        $h = $this->PageInfo[$l[0]]['size'][1];
                    else
                        $h = ($this->DefOrientation=='P') ? $this->DefPageSize[1]*$this->k : $this->DefPageSize[0]*$this->k;
                    $annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',$this->PageInfo[$l[0]]['n'],$h-$l[1]*$this->k);
                }
            }
            $this->_put($annots.']');
        }
        if($this->WithAlpha)
            $this->_put('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>');
        $this->_put('/Contents '.($this->n+1).' 0 R>>');
        $this->_put('endobj');
        // Page content
        if(!empty($this->AliasNbPages))
            $this->pages[$n] = str_replace($this->AliasNbPages,$this->page,$this->pages[$n]);
        $this->_putstreamobject($this->pages[$n]);
    }

    function add_header($txt,$headert1,$headert2)
    {
        $this->headert1 = $headert1;
        $this->headert2 = $headert2;
        $this->headertext = $txt;
    }

    function Header(){
        $this->SetLink("#page-".$this->PageNo());
        $this->SetFont($this->font,'',10);
        $this->MultiCell(190,4,$this->utf($this->organisation["bezeichnung"]),0,'L',false);
        $this->MultiCell(190,4,$this->utf($this->organisation["anschrift"]),0,'L',false);
        $this->MultiCell(190,4,$this->utf($this->organisation["plz"]." ".$this->organisation["ort"]),0,'L',false);
        $this->setY($this->getY()+11);
    }

    function Footer()
    {

        $this->SetY(-15);
        $this->SetFont($this->font,'',$this->fontsize_text-2);

        if($this->stand != 0) {
            $this->Cell(0, 10, 'Seite '.$this->PageNo(). " von {totalPages} ". 'Stand: ' . date("d.m.Y", $this->stand), 0, 0, 'R');
            $this->SetFont($this->font, '', $this->fontsize_text);
        }
        $this->SetY(-15);
        $this->SetFont($this->font,'',$this->fontsize_text-2);
        $this->Cell(0,10,$this->organisation["pdf_portal"],0,0,'L');
        $this->SetFont($this->font,'',$this->fontsize_text);

    }

    var $angle;

    function Rotate($angle,$x=-1,$y=-1)
    {
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }
    function RotatedText($x,$y,$txt,$angle)
    {
        //Text rotated around its origin
        $this->Rotate($angle,$x,$y);
        $this->Text($x,$y,$txt);
        $this->Rotate(0);
    }


    function utf($intext)
    {
        return utf8_decode(trim($intext));
    }

    var $posrightY = 0;


    function add_block($infotext,$content,$border = true,$fill = true,$fillwidth = false,$right = false)
    {

        $content = trim($content);

        if(strlen($content) != 0) {
            if ($this->posrightY == 0)
                $this->posrightY = $this->offsettop;

            if ($right) {
                $this->setY($this->posrightY);
                $this->setX($this->offsetright);
            }
            if ($fillwidth)
                $fillwidth = 190;
            else
                $fillwidth = 90;

            $this->SetFillColor(200, 200, 200);
            if ($border) {
                $border = "1";
                $this->SetFont($this->font, '', $this->fontsize_text);

            } else {
                $border = "0";
                $this->SetFont($this->font, 'B', $this->fontsize_text);
            }
            $this->MultiCell($fillwidth, $this->lineheight, $this->utf($infotext), $border, 'L', $fill);
            $this->SetFont($this->font, '', $this->fontsize_text);

            if ($right)
                $this->setX($this->offsetright);

            $this->MultiCell($fillwidth, $this->lineheight, $this->utf($content), $border, 'L', false);
            $this->ln();

            if ($right)
                $this->posrightY = $this->getY();
        }
    }

    function add_datablock($infotext,$contentarray,$withoutkeylinebold = true,$keybold = true,$border = true, $fillwidth = false)
    {

        if(count($contentarray) > 0) {

            if ($fillwidth)
                $fillwidth = 190;
            else
                $fillwidth = 90;

            $this->setY($this->getY());
            if ($border)
                $this->SetFont($this->font, '', $this->fontsize_text);
            else
                $this->SetFont($this->font, 'B', $this->fontsize_text);

            $this->SetFillColor(200, 200, 200);
            $this->MultiCell($fillwidth, $this->lineheight, $this->utf($infotext), $border, 'L', $border);

            $anz = count($contentarray);
            $ystart = $this->getY();

            foreach ($contentarray as $key => $value) {

                if (strlen($key) > 2) {
                    if ($keybold) {
                        $this->SetFont($this->font, 'B', $this->fontsize_text);
                        $this->MultiCell(20, $this->lineheight, $this->utf($key), $border, 'L', false);
                        $this->SetFont($this->font, '', $this->fontsize_text);
                        $this->MultiCell($fillwidth - 20, $this->lineheight, $this->utf($value), $border, 'L', false);
                    } else {
                        $this->SetFont($this->font, '', $this->fontsize_text);
                        $this->MultiCell($fillwidth, $this->lineheight, $this->utf($key), $border, 'L', false);
                        $this->SetFont($this->font, '', $this->fontsize_text);
                        $this->MultiCell($fillwidth, $this->lineheight, $this->utf($value), $border, 'L', false);
                    }

                } else {
                    if ($withoutkeylinebold)
                        $this->SetFont($this->font, 'B', $this->fontsize_text);
                    else
                        $this->SetFont($this->font, '', $this->fontsize_text);

                    $this->MultiCell($fillwidth, $this->lineheight, $this->utf($value), false, 'L', false);


                }
            }
            $yend = $this->getY();
            $this->setY($ystart);

            if ($border)
                $this->MultiCell($fillwidth, $yend - $ystart, "", 1, 'L', false);

            $this->setY($yend);
            $this->ln($this->lineheight);
        }
        }

}

function gen_infoblatt_cached($verfahren_id,$writetofile = false)
{
    $mc = new mysql();
    $verfahren = $mc->fetch_array("SELECT * FROM verfahren WHERE verfahren_id = '".$verfahren_id."'");
    $stand  = $mc->fetch_array("SELECT MAX(time_created) as time FROM protokoll WHERE object_id = '".$verfahren_id."'");

    if($verfahren["last_rendering_infoblatt"] == 0)
    {
        $mc->query("update verfahren set last_rendering_infoblatt = '".time()."' WHERE verfahren_id = '".$verfahren_id."'");
        return infoblatt($verfahren_id,$writetofile);
    }

    if($stand["time"] > $verfahren["last_rendering_infoblatt"])
    {
        $mc->query("update verfahren set last_rendering_infoblatt = '".time()."' WHERE verfahren_id = '".$verfahren_id."'");
        return infoblatt($verfahren_id,$writetofile);
    }
    else
    {
        if(!file_exists(PDF_FOLDER.$verfahren_id.".pdf"))
        {
            $mc->query("update verfahren set last_rendering_infoblatt = '".time()."' WHERE verfahren_id = '".$verfahren_id."'");
            return infoblatt($verfahren_id,$writetofile);
        }
        else
            return $verfahren_id.".pdf";
    }
}


function infoblatt($verfahren_id,$writetofile = false)
{
    $pdf = new pdfinfoblatt();
    $mc = new mysql();

    $pdf->SetFont('Arial','B',10);
    $pdf->verfahren = $mc->fetch_array("SELECT * FROM verfahren WHERE verfahren_id = '".$verfahren_id."'");
    $pdf->gruppe = $mc->fetch_array("SELECT * FROM gruppe WHERE gruppe_id = '".$pdf->verfahren["gruppe_id"]."'");
    $pdf->organisation = $mc->fetch_array("SELECT * FROM organisation WHERE organisation_id = '".$pdf->gruppe["organisation_id"]."'");
    $stand  = $mc->fetch_array("SELECT MAX(time_created) as time FROM protokoll WHERE object_id = '".$verfahren_id."'");
    $pdf->stand = $stand["time"];



    $pdf->AddPage();
    $logo = get_organisation_logo($pdf->gruppe["organisation_id"]);
    if(file_exists($logo))
    {
        $pdf->Image($logo,150,9,0,30,'PNG');
    }





    if($pdf->verfahren["art1314"] == "14")
        $pdf->MultiCell(190,$pdf->lineheight+1,$pdf->utf("Informationen zum Datenschutz gem. Art. 14 DSGVO"),0,'L',false);

    if($pdf->verfahren["art1314"] == "13")
        $pdf->MultiCell(190,$pdf->lineheight+1,$pdf->utf("Informationen zum Datenschutz gem. Art. 13 DSGVO"),0,'L',false);

    if($pdf->verfahren["art1314"] == "1314")
        $pdf->MultiCell(190,$pdf->lineheight+1,$pdf->utf("Informationen zum Datenschutz gem. Art. 13 & 14 DSGVO"),0,'L',false);


    $pdf->SetFont('Arial','B',10);
    $pdf->MultiCell(190,$pdf->lineheight+1,$pdf->utf($pdf->verfahren["bezeichnung"]),0,'L',false);

    $pdf->add_block("","Wir möchten Ihnen auf diesem Weg die wesentlichen Informationen zum Datenschutz zu der Verarbeitungstätigkeit ".$pdf->verfahren["bezeichnung"]." mitteilen. Bei Fragen zum Thema Datenschutz bestehen mehrere Kontaktmöglichkeiten.",false,false,true);


    $pdf->add_block("Betroffenenrechte","Sie können über die v.g. Adresse Auskunft über die zu Ihrer Person gespeicherten Daten verlangen. Darüber hinaus können Sie unter bestimmten Voraussetzungen die Berichtigung (Art. 16 DSGVO) oder Löschung (Art. 17,18 und 21 DSGVO) verlangen.",false,false,false,true);

    $pdf->add_block("Recht auf Auskunft","Werden personenbezogene Daten von Ihnen verarbeitet, haben Sie das Recht, Auskunft über die zur Person gespeicherten Daten zu erhalten, z.B. Verarbeitungszwecke, Herkunft der Daten, Empfänger der Daten etc. (Art. 15 DSGVO).",false,false,false,true);
    $pdf->add_block("Recht auf Berichtigung","Sollten unrichtige oder unvollständige personenbezogene Daten verarbeitet werden, steht Ihnen ein Recht auf Berichtigung zu (Art. 16 DSGVO).",false,false,false,true);
    $pdf->add_block("Recht auf Löschung","Liegen die gesetzlichen Voraussetzungen vor, so können Sie die Löschung oder Einschränkung der Verarbeitung verlangen sowie Widerspruch gegen die Verarbeitung einlegen (Art. 17, 18 und 21 DSGVO). Dies gilt insbesondere, wenn diese zu dem Zweck, zu dem sie erhoben wurden nicht mehr benötigt werden.",false,false,false,true);

    $pdf->add_block("Recht auf Datenübertragbarkeit","Wenn Sie in die Datenverarbeitung eingewilligt haben oder ein Vertrag zur Datenverarbeitung besteht und die Datenverarbeitung mithilfe automatisierter Verfahren durchgeführt wird, steht Ihnen gegebenenfalls ein Recht auf Datenübertragbarkeit zu (Art. 20 DSGVO).",false,false,false,true);
    $pdf->add_block("Widerruf der Einwilligung","Wenn Sie in die Verarbeitung durch ".$pdf->organisation["pronomen"]." ".$pdf->organisation["bezeichnung"]." durch eine entsprechende Erklärung eingewilligt haben, können Sie die Einwilligung jederzeit für die Zukunft widerrufen. Die Rechtmäßigkeit der aufgrund der Einwilligung bis zum Widerruf erfolgten Datenverarbeitung wird durch diesen nicht berührt.",false,false,false,true);
    $pdf->add_block("Recht auf Widerspruch","Sie haben grundsätzlich ein allgemeines Widerspruchsrecht gegen eine an sich rechtmäßige Verarbeitung Ihrer personenbezogenen Daten. Sobald Sie Widerspruch eingelegt haben, dürfen wir Ihre Daten nicht mehr verarbeiten, es sei denn, es liegen zwingende schutzwürdige Gründe vor. Ein zwingender Grund kann sich insbesondere aus Gesetzen ergeben, die die Verarbeitung vorsehen oder voraussetzen. Die bis zum Widerspruch verarbeiteten Daten werden hierdurch nicht rechtswidrig. Ihr Widerspruch ist an ".$pdf->organisation["pronomen"]." ".$pdf->organisation["bezeichnung"]." zu richten. (Art. 21 DSGVO)",false,false,false,true);
    $pdf->add_block("Recht auf Beschwerde bei einer Aufsichtsbehörde","Weiterhin steht Ihnen ein Beschwerderecht bei der Landesbeauftragten für den Datenschutz, Prinzenstr. 5, 30159 Hannover zu.",false,false,false,true);

    $pdf->add_block("Sollten Sie von Ihren oben genannten Rechten Gebrauch machen, prüft die öffentliche Stelle, ob die gesetzlichen Voraussetzungen hierfür erfüllt sind.","",false,false,false,true);


    $pdf->setY($pdf->offsettop);

    $ansp = $mc->fetch_array("SELECT * FROM personen WHERE person_id = '".$pdf->verfahren["id_verantwortlich"]."'");
    if(strlen($ansp["ad_username"]) >1)
    {
        $ansp1 = $ansp;
        $ansp = $mc->fetch_array("SELECT * FROM ad_personen WHERE ad_username = '".$ansp1["ad_username"]."'");
        if(strlen($ansp1["name"]) > 0)
        {
            $ansp["name"] =$ansp1["name"];
        }
    }
    $ansp = array(
        "" =>  $ansp["name"],
        " " => $ansp["strasse"].", ".$ansp["plz"]." ".$ansp["ort"].", Telefon: ".$ansp["telefon"],
    );

    $pdf->add_datablock("Verantwortlicher für die Datenverarbeitung",$ansp,false,true,false,false);

    $ansp = $mc->fetch_array("SELECT * FROM personen WHERE person_id = '".$pdf->verfahren["id_adsb"]."'");
    if(strlen($ansp["ad_username"]) >1)
    {
        $ansp1 = $ansp;
        $ansp = $mc->fetch_array("SELECT * FROM ad_personen WHERE ad_username = '".$ansp1["ad_username"]."'");
        if(isset($ansp1["name"]))
        {
            $ansp["name"] =$ansp1["name"];
        }
    }
    $ansp = array(
        "" => $ansp["name"],
        " " => $ansp["strasse"].", ".$ansp["plz"]." ".$ansp["ort"].", Telefon: ".$ansp["telefon"],
    );

    $pdf->add_datablock("Datenschutzbeauftragter",$ansp,false,true,false,false);

    $ansp = array(
        "" => "Die Landesbeauftragte für den Datenschutz",
        " " => "Prinzenstr. 5, 30159 Hannover, Telefon: 0511 120-4500",
    );
    $pdf->add_datablock("Datenschutzaufsichtsbehörde",$ansp,false,true,false,false);


    $pdf->add_block("Zweck der Verarbeitung",$pdf->verfahren["beschreibung"],false,false);

    if(strlen($pdf->verfahren["rechtliche_grundlage"]) > 2)
    $pdf->add_block("Rechtsgrundlage der Verarbeitung",$pdf->verfahren["rechtliche_grundlage"],false,false);


    $res = $mc->query("SELECT * FROM verfahren_weitergabe as vw, weitergabe as w WHERE w.weitergabe_id = vw.weitergabe_id AND vw.verfahren_id = '".$verfahren_id."' AND (w.type = 'extern' OR w.type = 'fachdienst')");
    $data = array();
    while($row = mysqli_fetch_array($res))
    {
        $data[] = $row["name"];
    }
    $pdf->add_block("Wir verarbeiten Ihre Daten unter Einbindung externer Dritter",implode(", ",$data),false,false,false);

    $res = $mc->query("SELECT * FROM verfahren_datenkategorie as vd, datenkategorie as d WHERE d.datenkategorie_id = vd.datenkategorie_id AND vd.verfahren_id = '".$verfahren_id."'");
    $data = array();
    while($row = mysqli_fetch_array($res))
    {
        $data[] = "- ".$row["name"]." (".$row["beschreibung"].")";
    }
    $pdf->add_datablock("Art der erhobenen Daten",$data,false,false,false);

    $res = $mc->query("SELECT * FROM verfahren_datenkategorie as vd, datenkategorie as d WHERE d.datenkategorie_id = vd.datenkategorie_id AND vd.verfahren_id = '".$verfahren_id."'");
    $data = array();

    while($row = mysqli_fetch_array($res))
    {
        if(strlen($row["loeschfristen"]) > 0)
            $data[] = "- ".$row["loeschfristen"]." (".$row["name"].")";
    }
    $pdf->add_datablock("Dauer der Datenspeicherung",$data,false,false,false);

    $res = $mc->query("SELECT * FROM verfahren_weitergabe as vw, weitergabe as w WHERE w.weitergabe_id = vw.weitergabe_id AND vw.verfahren_id = '".$verfahren_id."' AND (w.type = 'drittland')");
    $data = array();
    while($row = mysqli_fetch_array($res))
    {
        $data[] = "- ".$row["name"];
    }
    if(count($row) > 0)
        $pdf->add_datablock("Es ist geplant, Ihre personenbezogenen Daten an folgende/s Drittland/internationale Organisation zu übermitteln",$data,false,false,false);

    if($pdf->verfahren["art1314"] == "14" OR $pdf->verfahren["art1314"] == "1314")
        $pdf->add_block("Ihre Daten haben wir erhoben bei",$pdf->verfahren["art14_unternehmen"],false,false,false,false);

    if($pdf->verfahren["art6_1"] == "1")
    {
        $pdf->add_block("Freiwillige Einwilligung","Sie haben durch eine entsprechende Erklärung eingewilligt, dass die oben genannten Daten durch die Behörde verarbeitet werden dürfen. Sie können die Einwilligung jederzeit für die Zukunft widerrufen. Die Rechtmäßigkeit der aufgrund der Einwilligung bis zum Widerruf erfolgten Datenverarbeitung wird durch diesen nicht berührt. (Beachten Sie hierzu bitte die Kontaktdaten oben)",false,false,false,false);
    }

    if($pdf->verfahren["art6_2"] == "1")
        $art6["Buchstabe b"] = "die Verarbeitung ist zur Erfüllung eines Vertrages oder vorvertraglicher Maßnahmen
erforderlich";

    if($pdf->verfahren["art6_3"] == "1")
        $art6["Buchstabe c"] = "die Verarbeitung ist zur Erfüllung einer rechtlichen Verpflichtung erforderlich";

    if($pdf->verfahren["art6_4"] == "1")
        $art6["Buchstabe d"] = "die Verarbeitung ist erforderlich, um lebenswichtige Interessen der betroffenen Person
oder einer anderen natürlichen Person zu schützen";

    if($pdf->verfahren["art6_5"] == "1")
        $art6["Buchstabe e"] = "die Verarbeitung ist für die Wahrnehmung einer Aufgabe erforderlich , die im
öffentlichen Interesse liegt oder in Ausübung öffentlicher Gewalt erfolgt";

    if($pdf->verfahren["art6_6"] == "1")
        $art6["Buchstabe f"] = "zur Verfolgung von Straftaten oder Ordnungswidrigkeiten, zur Vollstreckung oder zum
Vollzug von Strafen oder Maßnahmen im Sinne des § 11 Abs. 1 Nr. 8 des
Strafgesetzbuchs (StGB) oder von Erziehungsmaßregeln oder Zuchtmitteln im Sinne
des Jugendgerichtsgesetzes oder zur Vollstreckung von Bußgeldentscheidungen.";

    $pdf->anzpages = $pdf->getY();

    $pdf->AliasNbPages('{totalPages}');

    if(!$writetofile)
        $pdf->Output();
    else
    {
        $pdf->Output("F",PDF_FOLDER.$verfahren_id.".pdf");
        return $verfahren_id.".pdf";
    }
}

function merkblatt($verfahren_id,$writetofile = false)
{
    $pdf = new pdfinfoblatt();
    $mc = new mysql();

    $pdf->SetFont('Arial','',12);

    $pdf->verfahren = $mc->fetch_array("SELECT * FROM verfahren WHERE verfahren_id = '".$verfahren_id."'");
    $pdf->gruppe = $mc->fetch_array("SELECT * FROM gruppe WHERE gruppe_id = '".$pdf->verfahren["gruppe_id"]."'");
    $pdf->organisation = $mc->fetch_array("SELECT * FROM organisation WHERE organisation_id = '".$pdf->gruppe["organisation_id"]."'");


    $pdf->anzpages = $pdf->getY();
    $pdf->AliasNbPages('{totalPages}');

    if(!$writetofile)
        $pdf->Output();
    else
    {
        $pdf->Output("F",PDF_FOLDER.$verfahren_id.".pdf");
        return PDF_FOLDER.$verfahren_id.".pdf";
    }


}

function gen_einwilligung_cached($verfahren_id,$writetofile = false)
{
    $mc = new mysql();
    $verfahren = $mc->fetch_array("SELECT * FROM verfahren WHERE verfahren_id = '".$verfahren_id."'");
    $stand  = $mc->fetch_array("SELECT MAX(time_created) as time FROM protokoll WHERE object_id = '".$verfahren_id."'");

    if($verfahren["last_rendering_einwilligung"] == 0)
    {
        $mc->query("update verfahren set last_rendering_einwilligung = '".time()."' WHERE verfahren_id = '".$verfahren_id."'");
        return einwilligung($verfahren_id,$writetofile);
    }

    if($stand["time"] > $verfahren["last_rendering_einwilligung"])
    {
        $mc->query("update verfahren set last_rendering_einwilligung = '".time()."' WHERE verfahren_id = '".$verfahren_id."'");
        return einwilligung($verfahren_id,$writetofile);
    }
    else
    {
        if(!file_exists(PDF_FOLDER."E".$verfahren_id.".pdf"))
        {
            $mc->query("update verfahren set last_rendering_einwilligung = '".time()."' WHERE verfahren_id = '".$verfahren_id."'");
            return einwilligung($verfahren_id,$writetofile);
        }
        else
            return "E".$verfahren_id.".pdf";
    }
}

function einwilligung($verfahren_id,$writetofile = false)
{
    $pdf = new pdfinfoblatt();
    $mc = new mysql();
    $pdf->fontsize_text = 9;
    $pdf->lineheight = 4.5;


    $pdf->SetFont('Arial','',11);

    $pdf->verfahren = $mc->fetch_array("SELECT * FROM verfahren WHERE verfahren_id = '".$verfahren_id."'");
    $pdf->gruppe = $mc->fetch_array("SELECT * FROM gruppe WHERE gruppe_id = '".$pdf->verfahren["gruppe_id"]."'");
    $pdf->organisation = $mc->fetch_array("SELECT * FROM organisation WHERE organisation_id = '".$pdf->gruppe["organisation_id"]."'");

    $stand  = $mc->fetch_array("SELECT MAX(time_created) as time FROM protokoll WHERE object_id = '".$verfahren_id."'");
    $pdf->stand = $stand["time"];

    $pdf->AddPage();
    $pdf->SetFont('Arial','B',12);
    $pdf->MultiCell(190,6,$pdf->utf("Einverständniserklärung zur Verarbeitung personenbezogener Daten"),0,'C',false);
    $pdf->SetFont('Arial','',12);

    $pdf->ln();

    $ansp = $mc->fetch_array("SELECT * FROM personen WHERE person_id = '".$pdf->verfahren["id_verantwortlich"]."'");
    if(strlen($ansp["ad_username"]) >1)
    {
        $ansp1 = $ansp;
        $ansp = $mc->fetch_array("SELECT * FROM ad_personen WHERE ad_username = '".$ansp1["ad_username"]."'");
        if(strlen($ansp1["name"]) > 2)
        {
            $ansp["name"] =$ansp1["name"];
        }
    }
    $ansp = array(
        "" => $ansp["name"],
        " " => $ansp["strasse"]."; ".$ansp["plz"]." ".$ansp["ort"]."; Telefon: ".$ansp["telefon"],
    );
    $pdf->add_datablock("Verantwortliche/r",$ansp,false,false,false,true);

    $ansp = $mc->fetch_array("SELECT * FROM personen WHERE person_id = '".$pdf->organisation["adsb_id"]."'");
    if(strlen($ansp["ad_username"]) > 1)
    {
        $ansp1 = $ansp;
        $ansp = $mc->fetch_array("SELECT * FROM ad_personen WHERE ad_username = '".$ansp1["ad_username"]."'");
        if(isset($ansp1["name"]))
        {
            $ansp["name"] =$ansp1["name"];
        }
    }
    $ansp = array(
        "" => $ansp["name"],
        " " => $ansp["strasse"]."; ".$ansp["plz"]." ".$ansp["ort"]."; Telefon: ".$ansp["telefon"],
    );

    $pdf->add_datablock("Datenschutzbeauftragte/r",$ansp,false,false,false,true);
    $data = array();
    $res = $mc->query("SELECT * FROM verfahren_datenkategorie as vd, datenkategorie as d WHERE d.datenkategorie_id = vd.datenkategorie_id AND vd.verfahren_id = '".$verfahren_id."'");
    while($row = mysqli_fetch_array($res))
    {
        $data[] = $row["name"]." (".$row["beschreibung"].")";
    }
    $pdf->add_block("zu verarbeitende Daten",implode(", ",$data),false,false,true,false);

    $pdf->add_block("Erhebungszweck",$pdf->verfahren["beschreibung"],false,false,true,false);

    $pdf->SetFont($pdf->font, 'B', $pdf->fontsize_text);
    $pdf->write($pdf->lineheight, $pdf->utf("Diese Erklärung ist freiwillig!"));
    $pdf->ln();
    $pdf->ln();
    $pdf->SetFont($pdf->font, '', $pdf->fontsize_text);
    $pdf->write($pdf->lineheight, $pdf->utf("Der/die Unterzeichnende erklärt sich damit einverstanden, dass die oben genannten Daten zu dem oben genannten Zweck verarbeitet (das heißt insbesondere erfasst und gespeichert) werden dürfen."));
    $pdf->ln();
    $pdf->ln();
    $pdf->write($pdf->lineheight, $pdf->utf("Der/die Unterzeichnende ist jederzeit berechtigt, die Einwilligung zu widerrufen. Der Widerruf erfolgt schriftlich an ".$pdf->organisation["pronomen"]." ".$pdf->organisation["bezeichnung"]."; ".$pdf->organisation["anschrift"]."; ".$pdf->organisation["plz"]." ".$pdf->organisation["ort"]." oder per E-Mail an: ".$pdf->organisation["beschwerde_email"]));
    $pdf->ln();
    $pdf->ln();
    $pdf->write($pdf->lineheight, $pdf->utf("Die bis zum Zeitpunkt des Widerrufs erfolgte Verarbeitung der Daten wird durch den Widerruf nicht rechtswidrig. Wenn die Daten gelöscht werden sollen, ist dies ausdrücklich zu verlangen. Dem Verlangen ist zu entsprechen, es sei denn, es gibt eine andere Rechtsgrundlage, ".$pdf->organisation["pronomen"]." ".$pdf->organisation["bezeichnung"]." berechtigt, die Daten zu verarbeiten."));
    $pdf->ln();
    $pdf->ln();
    $pdf->write($pdf->lineheight, $pdf->utf("Gemäß der Artikel 15 - 20 DSGVO können Sie jederzeit gegenüber ".$pdf->organisation["pronomen"]." ".$pdf->organisation["bezeichnung"]." (".$pdf->organisation["beschwerde_email"].") Auskunft, die Berichtigung, Löschung, das Recht auf Einschränkung der Verarbeitung und Sperrung sowie das Recht auf Datenübertragbarkeit einzelner oder aller personenbezogener Daten verlangen. "));
    $pdf->anzpages = $pdf->getY();

    $startY=12;
    $unterschLineh = 270;
    $pdf->SetLineWidth(0.5);
    $pdf->Line($startY, $unterschLineh, $startY+40, $unterschLineh);
    $pdf->SetFont($pdf->font, '', $pdf->fontsize_text);
    $pdf->setY($unterschLineh);
    $pdf->setX($startY-1);
    $pdf->write($pdf->lineheight,"Datum");

    $startY=62;
    $unterschLineh = 270;
    $pdf->SetLineWidth(0.5);
    $pdf->Line($startY, $unterschLineh, $startY+60, $unterschLineh);
    $pdf->SetFont($pdf->font, '', $pdf->fontsize_text);
    $pdf->setY($unterschLineh);
    $pdf->setX($startY-1);
    $pdf->write($pdf->lineheight,"Name in Blockschrift");

    $startY=132;
    $unterschLineh = 270;
    $pdf->SetLineWidth(0.5);
    $pdf->Line($startY, $unterschLineh, $startY+60, $unterschLineh);
    $pdf->SetFont($pdf->font, '', $pdf->fontsize_text);
    $pdf->setY($unterschLineh);
    $pdf->setX($startY-1);
    $pdf->write($pdf->lineheight,"Unterschrift");


    $pdf->AliasNbPages('{totalPages}');

    if(!$writetofile)
        $pdf->Output();
    else
    {
        $pdf->Output("F",PDF_FOLDER."E".$verfahren_id.".pdf");
        return "E".$verfahren_id.".pdf";
    }
}