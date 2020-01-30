<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 22.11.2018
 * Time: 10:43
 */


class pruefbericht extends pdfinfoblatt {
    public $files = array();
    var $organisation_id;
    var $stand;

    function deckblatt()
    {
        $this->AddPage();
        $this->SetFont('Arial','B',30);

        if(is_admin())
        $this->MultiCell(190,10,$this->utf("Verzeichnis der Verarbeitungstätigkeiten"),0,'C',false);
        else
            $this->MultiCell(190,18,$this->utf("Auszug aus dem\n Verzeichnis der Verarbeitungstätigkeiten"),0,'C',false);

        $this->SetFont('Arial','B',10);


        $mc = new mysql();
        $stand = $mc->fetch_array("SELECT MAX(time_created) as time FROM protokoll");
        $this->stand = $stand["time"];
        $this->Cell(190,10,$this->utf("Stand: ".date("d.m.Y",$this->stand)),0,'',"C");

        $imgpath = get_organisation_logo($this->organisation_id);
      


        if(file_exists($imgpath)) {
            $this->Image($imgpath, 55, 100, 100, 0, 'PNG');
        }
        $this->SetFont('Arial','B',10);
    }

    public function setFiles($file)
    {
        $this->files[] = $file;
    }

    public function concat()
    {
        foreach($this->files AS $file) {
            $pageCount = $this->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $pageId = $this->ImportPage($pageNo);
                $s = $this->getTemplatesize($pageId);
                $this->AddPage($s['orientation'], $s);
                $this->useImportedPage($pageId);
            }
        }
    }

    function InhaltZeile($name,$einrueckung,$seitenzahl,$bold = false)
    {
        $linehighadd = 2;
        $maxlinewidth = 190;
        $offsetline = 1.5;
        $offsetYLine = -1.1;

        $x = $this->getX();
        $y = $this->getY();

        if($bold)
            $this->SetFont('Arial','B',$this->fontsize_text);
        else
            $this->SetFont('Arial','',$this->fontsize_text);


        $this->SetX($einrueckung);
        $textlen = $this->GetStringWidth($this->utf($name));

        $this->MultiCell($maxlinewidth-$einrueckung,$this->lineheight+$linehighadd,$this->utf($name),0,'L',false,"#page-".$seitenzahl);
        $ynew = $this->getY();

        if($textlen < ($maxlinewidth-$einrueckung))
        $this->line($offsetline+$textlen+$einrueckung,$this->getY()+$offsetYLine,$maxlinewidth,$this->getY()+$offsetYLine);

        $this->SetY($y);
        $this->SetX($maxlinewidth);
        $this->Cell(12,$this->lineheight+$linehighadd+1,$seitenzahl,0,'L',false,0,"#page-".$seitenzahl);
        $this->SetX($x);
        $this->ln();
        $this->SetY($ynew);


    }

    function FDSeite($name,$altname,$fd_id,$verfahren)
    {
        $this->AddPage();
        $this->setY(15);
        $this->SetFont($this->font, '', 24);

        if(strlen($altname) > 2)
            $this->Cell(200,8,$this->utf($name.", ".$altname),0,'C',false);
        else
            $this->Cell(200,8,$this->utf($name),0,'C',false);

        $this->ln();
        $this->SetFont($this->font, '', 12);
        $anz = 0;
       foreach($verfahren as $vf)
       {
           if(isset($vf["index"]))
               $anz++;
       }

        if($anz == 1)
            $this->Cell(200, 8, $this->utf( $anz . " Eintrag"), 0, 'C', false);
        else
            $this->Cell(200, 8, $this->utf( $anz . " Einträge"), 0, 'C', false);

        $this->ln();



        $linepos = 0;
        $pages = 1;
        $anz = 0;
        $maxlines = 49;
        foreach($verfahren as $vt)
        {
            if(isset($vt["index"])) {
                if($linepos == $maxlines)
                {
                    $this->AddPage();
                    $pages++;
                    $linepos = 0;
                    $maxlines = 100;
                }
                $textlen = $this->GetStringWidth($this->utf($vt["name"]));


                if($textlen > 177)
                    $linepos = $linepos+2;
                else
                    $linepos++;

                $this->InhaltZeile($vt["name"], 10, $vt["seite"], false);
                $anz++;
            }

        }
    return $pages;
 }
    function Anlagen($name,$altname,$fd_id,$verfahren)
    {


        $this->AddPage();
        $this->setY(15);

        $this->SetFont('Arial','B',30);
        $this->setX(40);
        $this->MultiCell(130,20,$this->utf($altname),0,'C',false);
        $this->SetFont($this->font, '', 16);
        $this->MultiCell(190,8,$this->utf($name),0,'C',false);

        $this->SetFont($this->font, '', 16);
        $this->Cell(190,10,$this->utf("Stand: ".date("d.m.Y",$this->stand)),0,'',"C");

        $this->ln();
        $this->SetFont($this->font, '', 12);
        $anz = 0;
        foreach($verfahren as $vf)
        {
            if($vf["numpages"] > 0)
                $anz++;
        }

        if($anz == 1)
            $this->Cell(200, 8, $this->utf( $anz . " Eintrag"), 0, 'C', false);
        else
            $this->Cell(200, 8, $this->utf( $anz . " Einträge"), 0, 'C', false);

        $this->ln();



        $linepos = 0;
        $pages = 1;
        $anz = 0;
        $maxlines = 36;
        foreach($verfahren as $vt)
        {
            if(isset($vt["fachdienst"]) OR isset($vt["vtaetigkeit"]) OR isset($vt["tom"])) {

                if($linepos == $maxlines)
                {
                    $this->AddPage();
                    $pages++;
                    $linepos = 0;
                    $maxlines = 70;
                }
                $textlen = $this->GetStringWidth($this->utf($vt["name"]));


                if($textlen > 177)
                    $linepos = $linepos+2;
                else
                    $linepos++;

                if(isset($vt["tom"])) {
                    $this->InhaltZeile($vt["name"], 10, $vt["seite"], false);
                }
                else {

                    if (isset($vt["fachdienst"])) {
                        $this->InhaltZeile($vt["name"], 10, $vt["seite"], true);
                    } else {
                        $this->InhaltZeile($vt["name"], 20, $vt["seite"], false);

                    }
                }
                $anz++;
            }

        }
        return $pages;
    }

    var $footerstartfrom = 0;
    function enableFooter()
    {
        $this->inhaltsvz = false;
    }

    var $inhaltsvz = true;
    function Footer()
    {
        if(!$this->inhaltsvz ) {
            $this->SetY(-15);
            $this->SetFont('Arial', '', 8);
            $this->Cell(0, 10, 'Seite ' . ($this->PageNo()-$this->footerstartfrom), 0, 0, 'C');
        }
    }


}