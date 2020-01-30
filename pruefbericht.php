<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 02.05.2018
 * Time: 13:17
 */
session_start();
include("config.php");
ini_set("display_errors","1");
$mc  = new mysql();

$sql = "SELECT  o.imagepathpng, g.alt_bezeichnung, v.upload_enabled, v.beispiel, v.vollstaendig, o.bezeichnung as organisation_name, v.bezeichnung as verfahren_name, g.bezeichnung as gruppe_name,v.verfahren_id,v.gruppe_id
  FROM verfahren as v,
  gruppe as g,
  organisation as o 
  WHERE o.organisation_id = g.organisation_id AND v.gruppe_id = g.gruppe_id AND o.organisation_id = '".$_SESSION["user"]->organisation_id."' GROUP BY g.bezeichnung";

$pdf_test = new pruefbericht();

$res = $mc->query($sql);
$inhalt = array();

$fd_id = 1;
$image = "";
$verfahren_added = array();

$v_v = array();
$v_r = array();

$fdadded = array();

while($row = mysqli_fetch_array($res))
{
    if(gruppe_right_read($row["gruppe_id"])) {
        $image = $row["imagepathpng"];
        $sql_g = "SELECT  v.art6_1, v.docs_auto, v.docs_manual, v.verfahren_id, v.upload_enabled,v.beispiel,v.vollstaendig,o.bezeichnung as organisation_name, v.bezeichnung as verfahren_name, g.bezeichnung as gruppe_name, g.alt_bezeichnung as gruppe_name_alt, v.verfahren_id,v.gruppe_id FROM verfahren as v,gruppe as g,organisation as o WHERE o.organisation_id = g.organisation_id AND v.gruppe_id = g.gruppe_id AND g.gruppe_id = '" . $row["gruppe_id"] . "' ORDER by organisation_name, verfahren_name ASC ";
        $res1 = $mc->query($sql_g);
        $v_t = array();

        $vtaet_id = 1;
        while ($row_v = mysqli_fetch_array($res1)) {
            $errors = verfahrengetErrors($row_v["verfahren_id"]);
            if ($row_v["vollstaendig"] == 1 AND count($errors) == 0) {
                $verfahren_added[] = $row_v["verfahren_id"];

                if(!isset($fdadded[$row_v["gruppe_name"]]))
                {
                    $fdadded[$row_v["gruppe_name"]] = true;
                    $v_v[] = array("id" => $row_v["verfahren_id"],"numpages" => 0,  "file" => "", "fachdienst" => 1, "name" => $row_v["gruppe_name"], "name_alt" => $row_v["gruppe_name_alt"], "seite" => 0);
                }
                $v_v[] = array("id" => $row_v["verfahren_id"],"numpages" => 0,  "file" => "", "vtaetigkeit" => 1, "name" => $row_v["verfahren_name"], "seite" => 0);

                //infodoc

                gen_infopdf($row_v["verfahren_id"], $fd_id . "." . $vtaet_id);
                $file = PDF_FOLDER . "info_" . $row_v["verfahren_id"] . ".pdf";
                $numpages = $pdf_test->setSourceFile($file);
               $v_t[] = array("id" => $row_v["verfahren_id"], "numpages" => $numpages, "file" => $file, "index" => 1, "name_id" => $fd_id . "." . $vtaet_id, "name" => $row_v["verfahren_name"], "seite" => 0);

                if ($row_v["docs_auto"] == "1") {
                    //Infoblatt
                    gen_infoblatt_cached($row_v["verfahren_id"], true);
                    $file = PDF_FOLDER . $row_v["verfahren_id"] . ".pdf";

                    if (file_exists($file)) {
                        $numpages = $pdf_test->setSourceFile($file);
                         $v_v[] = array("id" => $row_v["verfahren_id"], "numpages" => $numpages, "file" => $file, "infoblatt" => 1, "name" => "Informationsblatt", "seite" => 0);
                    }
                }
                if ($row_v["art6_1"] == "1") {
                    //Einwilligung
                    gen_einwilligung_cached($row_v["verfahren_id"], true);
                    $file = PDF_FOLDER . "E" . $row_v["verfahren_id"] . ".pdf";
                    if (file_exists($file)) {
                        $numpages = $pdf_test->setSourceFile($file);
                        $v_v[] = array("id" => $row_v["verfahren_id"], "numpages" => $numpages, "file" => $file, "einwilligung" => 1, "name" => "Einwilligungserklärung", "seite" => 0);
                    }
                }
                if ($row_v["docs_manual"] == "1") {
                    //Eigene Dokumente
                    $sql = "SELECT * FROM dokumente WHERE object_id = '" . $row_v["verfahren_id"] . "' AND typ = 'einversta' AND deleted = 0";
                    $res_doks = $mc->query($sql);
                    while ($row_dok = mysqli_fetch_array($res_doks)) {
                        try {

                            $file = UPLOAD_FOLDER . $row_dok["dokument_id"] . "." . $row_dok["extension"];
                            if (file_exists($file)) {
                                $numpages = $pdf_test->setSourceFile($file);
                                $v_v[] = array("id" => $row_v["verfahren_id"],"numpages" => $numpages,  "file" => $file, "eigene" => 1, "name" => $row_dok["name"], "seite" => 0);
                            }

                        } catch (Exception $e) {
                            openlog("myScripLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);
                            syslog(LOG_WARNING, "Fehler beim Includen von PDF. Meldung: " . $e->getMessage() . " Dokument ID: " . $row_dok["dokument_id"]);
                        }

                    }
                }

                $vtaet_id++;
            }
        } // END LOOP Verarbeitungstaetigkeit

        if (count($v_t) > 0) {
            $numpages = $pdf_test->FDSeite("","",0,$v_t);
            $inhalt[] = array("name" => $row["gruppe_name"],"numpages" => $numpages, "seite" => 0, "alt_name" => $row["alt_bezeichnung"], "id" => $row["gruppe_id"], "verfahren" => $v_t);
        }
    } // END NO RIGHT CHECK
} // END LOOP Fachdienst

$v_t = array();

if(count($verfahren_added) > 0) {
    $sql = "select * from dokumente as d, massnahmen as m WHERE d.object_id = m.massnahme_id 
            AND 
            d.typ = 'tom'
            AND 
            d.deleted = 0
            AND 
            ( 
                m.massnahme_id IN (SELECT massnahme_id FROM verfahren_massnahmen WHERE verfahren_id IN (" . implode(',', $verfahren_added) . ")) 
              OR 
                m.massnahme_id IN (SELECT massnahme_id FROM software_massnahmen as sm, verfahren_software as vs WHERE vs.verfahren_id IN (" . implode(',', $verfahren_added) . ") AND sm.software_id = vs.software_id)
            )
            GROUP BY d.dokument_id ORDER BY d.name ASC";

    $res = $mc->query($sql);
    echo $mc->getError();
    while ($dokument = mysqli_fetch_array($res)) {
        try {
            $file = UPLOAD_FOLDER . $dokument["dokument_id"] . "." . $dokument["extension"];

            $numpages = $pdf_test->setSourceFile($file);
            $v_r[] = array("id" => $dokument["dokument_id"], "numpages" => $numpages, "file" => $file, "tom" => 1, "name" => $dokument["name"], "seite" => 0, "index" => 1);
        } catch (Exception $e) {
            openlog("myScripLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);
            syslog(LOG_WARNING, "Fehler beim Includen von PDF. Meldung: " . $e->getMessage() . " Dokument ID: " . $row_dok["dokument_id"]);
        }
    }
    $nump = $pdf_test->Anlagen("", "", 0, $v_v);
    $inhalt[] = array("name" => "Unterlagen gem. Artikel 13 und 14 DSGVO", "anlage" => "1", "numpages" => $nump, "seite" => 0, "alt_name" => "Anlage 1 zum Verzeichnis der Verarbeitungstätigkeiten", "id" => $row["gruppe_id"], "verfahren" => $v_v);

    $nump = $pdf_test->Anlagen("", "", 0, $v_r);
    $inhalt[] = array("name" => "Verträge zur Auftragsdatenverarbeitung durch Dritte", "anlage" => "1", "numpages" => 1, "seite" => 0, "alt_name" => "Anlage 2 zum Verzeichnis der Verarbeitungstätigkeiten", "id" => $row["gruppe_id"], "verfahren" => $v_r);

    $seite = 3;
    foreach ($inhalt as $fachdienst_key => $fachdienst) {
        $inhalt[$fachdienst_key]["seite"] = $seite;
        $seite = $seite + $fachdienst["numpages"];
        foreach ($fachdienst["verfahren"] as $verfahren_key => $verfahren) {
            $inhalt[$fachdienst_key]["verfahren"][$verfahren_key]["seite"] = $seite;
            $seite = $seite + $verfahren["numpages"];
        }
    }


    unset($pdf_test);
    $p = 1;
    $pdf = new pruefbericht();
    $pdf->organisation_id = $_SESSION["user"]->organisation_id;
    $pdf->SetAutoPageBreak(false);
    $pdf->fontsize_text = 8;
    $pdf->SetFont('Arial', 'B', $pdf->fontsize_text);


    $pdf->deckblatt();

    $pdf->AddPage();
    $pdf->setY(15);

    $pdf->SetFont($pdf->font, '', 24);

    $pdf->MultiCell(200, $pdf->lineheight + 1, $pdf->utf("Inhaltsverzeichnis"), 0, 'L', false);


    $orga = $mc->fetch_array("SELECT * FROM organisation where organisation_id = '" . $pdf->organisation_id . "'");

    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Cell(180, 5, $pdf->utf($orga["bezeichnung"]), 0, '', "L");
    $pdf->Ln();
    $pdf->Cell(180, 5, $pdf->utf($orga["anschrift"]), 0, '', "L");
    $pdf->Ln();
    $pdf->Cell(180, 5, $pdf->utf($orga["plz"] . " " . $orga["ort"]), 0, '', "L");
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 12);
    $anlagen_rpinted = false;

    foreach ($inhalt as $fd_key => $fachdienst) {

        $anzlines = count($fachdienst["verfahren"]);

        if (!isset($fachdienst["anlage"])) {
            if (strlen($fachdienst["alt_name"]) > 2)
                $pdf->InhaltZeile($fachdienst["name"] . ", " . $fachdienst["alt_name"], 10, $fachdienst["seite"], false);
            else
                $pdf->InhaltZeile($fachdienst["name"], 10, $fachdienst["seite"], false);
        } else {
            if (!$anlagen_rpinted) {
                $anlagen_rpinted = true;
                $pdf->Ln();
                $pdf->Cell(180, 5, $pdf->utf("Dokumente"), 0, '', "L");
                $pdf->Ln();
            }

            $pdf->InhaltZeile($fachdienst["name"], 10, $fachdienst["seite"], false);
        }


    }
    $pdf->enableFooter();

    foreach ($inhalt as $fd_key => $fachdienst) {


        if (!isset($fachdienst["anlage"])) {
            $pdf->FDSeite($fachdienst["name"], $fachdienst["alt_name"], $fachdienst["name_id"], $fachdienst["verfahren"]);

            foreach ($fachdienst["verfahren"] as $key => $seite) {
                $pageCount = $pdf->setSourceFile($seite["file"]);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $pageId = $pdf->ImportPage($pageNo);
                    $s = $pdf->getTemplatesize($pageId);
                    $pdf->AddPage($s['orientation'], $s);
                    $pdf->useImportedPage($pageId);
                }
            }
        } else {
            $pdf->Anlagen($fachdienst["name"], $fachdienst["alt_name"], $fachdienst["name_id"], $fachdienst["verfahren"]);

            foreach ($fachdienst["verfahren"] as $key => $seite) {
                if (strlen($seite["file"]) > 1) {
                    $pageCount = $pdf->setSourceFile($seite["file"]);
                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $pageId = $pdf->ImportPage($pageNo);
                        $s = $pdf->getTemplatesize($pageId);
                        $pdf->AddPage($s['orientation'], $s);
                        $pdf->useImportedPage($pageId);
                    }
                }
            }
        }
    }
    $pdf->Output('I', 'concat.pdf');

}
else
    echo "Keine Verfahren zum Drucken bereit!";
?>