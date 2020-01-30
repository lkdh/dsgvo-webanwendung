<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 26.03.2019
 * Time: 16:41
 */


function get_preview_url()
{
    return "<a href=\"https://www.diepholz.de/datenschutz\">https://www.diepholz.de/datenschutz</a>";
}


function upload_verfahren($data){
    GenrateNolisExportConfigFiles();
    GenerateNolisExportTemplateMaster();
    GenerateNolisExportTemplateFileSingle($data["data"]);
    $err = "";
    $mc = new mysql();
    $mc->query("UPDATE verfahren set upload = '".time()."' WHERE verfahren_id = '".$data["data"]."'");

    $verfahren = $mc->fetch_array("SELECT * FROM verfahren where verfahren_id = '".$data["data"]."'");


    $localfile = infoblatt($data["data"],true);
    $upload_files = array(
        'dsgvo'.$data["data"].".pdf" => PDF_FOLDER.$localfile,
        "html".$data["data"].".tpl" => PDF_FOLDER."html".$data["data"].".tpl",
        "dokumente.cfg" => PDF_FOLDER."dokumente.cfg",
        "ordner.cfg" => PDF_FOLDER."ordner.cfg",
        "datenschutz.tpl" => PDF_FOLDER."datenschutz.tpl",
        "verarbeitungstaetigkeiten.cfg" => PDF_FOLDER."verarbeitungstaetigkeiten.cfg"
    );

    $resqq = $mc->query("SELECT * FROM dokumente WHERE typ = 'einversta' AND object_id = '" .$data["data"] . "' AND deleted = 0");
    while ($rowdoc = mysqli_fetch_array($resqq)) {
        $upload_files["dsgvo".$data["data"]."9999999".$rowdoc["dokument_id"].".pdf"] = "../uploads/".$rowdoc["dokument_id"].".".$rowdoc["extension"];
    }

    if($verfahren["art6_1"] == "1")
    {
        $einwilligung =  einwilligung($data["data"],true);
        $upload_files["dsgvo".$verfahren["verfahren_id"]."9999999.pdf"] = PDF_FOLDER.$einwilligung;
    }

    $cont = "";
    if($_SERVER["SERVER_NAME"] != "dev-datenschutz.lkdh.intern")
    {


        $conn_id = ftp_connect(FTP_HOST,"21","10");
        if(!$conn_id)
            $err .= "ftp_connect failed";

        $login_result = ftp_login($conn_id, FTP_USER, FTP_PASS);

        if(!$login_result)
            $err .= "ftp_login failed";


        foreach($upload_files as $key => $value)
        {
            if (!@ftp_put($conn_id, "dsgvo/".$key, $value, FTP_BINARY))
            {
                $err .= "ftp fehler bei upload von ".$value."\n";
            }
        }
        ftp_close($conn_id);
        $cont = "";
        $cont = @file_get_contents("https://www.diepholz.de/seiten/dsgvo_import/import.php?token=b930e0410e2f43d3bd083c12b997b0b2d6f22816");

    }
    if($cont == "Importvorgang abgeschlossen" AND strlen($err) == 0)
    {
        return json_encode(array("status" => "1","alerttype" => "success","header" => "Erfolgreich veröffentlicht!","msg" => "Die Dokumente wurden erfolgreich veröffentlicht!"));
    }
    else
    {
        return json_encode(array("status" => "1","alerttype" => "danger","header" => "Fehler beim veröffentlichen!","msg" => "Der Server hat einen Fehler zurückgeliefert! ".$err));
    }
}

    function GenrateNolisExportConfigFiles(){
        $mc = new mysql();
        $sql = "SELECT g.gruppe_id, g.bezeichnung FROM verfahren as v, gruppe as g, organisation as o 
                WHERE o.organisation_id = '".$_SESSION["user"]->organisation_id."' AND g.ad_name != 'admin' AND v.upload_enabled = 1 AND v.vollstaendig = 1 AND v.gruppe_id = g.gruppe_id AND g.organisation_id = o.organisation_id GROUP BY g.gruppe_id ORDER BY g.bezeichnung";
        $res = $mc->query($sql);
        $ret = "OID|BEZEICHNUNG\n";
        while($row = mysqli_fetch_array($res))
        {
            $ret .= $row["gruppe_id"]."|".$row["bezeichnung"]."\n";
        }
        @file_put_contents(PDF_FOLDER."ordner.cfg",$ret);

        $sql = "SELECT v.verfahren_id, g.gruppe_id, v.bezeichnung FROM verfahren as v, gruppe as g, organisation as o 
                WHERE o.organisation_id = '".$_SESSION["user"]->organisation_id."' AND g.ad_name != 'admin' AND v.upload_enabled = 1 AND v.vollstaendig = 1 AND v.gruppe_id = g.gruppe_id AND g.organisation_id = o.organisation_id ORDER BY v.bezeichnung";
        $ret2 = "VID|ORDNER|BEZEICHNUNG\n";
        $res = $mc->query($sql);
        while($row = mysqli_fetch_array($res))
        {
            $ret2 .= $row["verfahren_id"]."|".$row["gruppe_id"]."|".$row["bezeichnung"]."\n";
        }
        @file_put_contents(PDF_FOLDER."verarbeitungstaetigkeiten.cfg",$ret2);

        $res = $mc->query("SELECT * FROM verfahren WHERE vollstaendig = '1' AND upload_enabled = '1' AND gruppe_id IN (SELECT gruppe_id FROM gruppe WHERE organisation_id = '".$_SESSION["user"]->organisation_id."') order by bezeichnung ASC");
        $ret2 = "DID|OID|VID|BEZEICHNUNG\n";
        while($row = mysqli_fetch_array($res))
        {

            if($row["docs_auto"] == "1") {
                $ret2 .= "dsgvo" . $row["verfahren_id"] . ".pdf|" . $row["gruppe_id"] . "|" . $row["verfahren_id"] . "|Merkblatt zum Datenschutz\n";
            }

            if($row["docs_manual"] == "1") {
                $resqq = $mc->query("SELECT * FROM dokumente WHERE typ = 'einversta' AND object_id = '" . $row["verfahren_id"] . "' AND deleted = 0");
                while ($rowdoc = mysqli_fetch_array($resqq)) {
                    $ret2 .= "dsgvo" . $row["verfahren_id"] . "9999999" . $rowdoc["dokument_id"] . ".pdf|" . $row["gruppe_id"] . "|" . $row["verfahren_id"] . "|" . $rowdoc["name"] . "\n";
                }
            }
            if($row["art6_1"] == "1")
                $ret2 .= "dsgvo".$row["verfahren_id"]."9999999.pdf|".$row["gruppe_id"]."|".$row["verfahren_id"]."|Einverständniserklärung zur Datenverarbeitung\n";



        }
        file_put_contents(PDF_FOLDER."dokumente.cfg",$ret2);
    }

        function GenerateNolisExportTemplateFileSingle($verfahren_id)
        {
            $mc = new mysql();
            $verfahren = $mc->fetch_array("SELECT * FROM verfahren WHERE verfahren_id = '".$verfahren_id."'");

            $ret = "<div id='nolis_content_heading' class='nolis_datenschutz'><h3>".$verfahren["bezeichnung"]."</h3>";
            $ret .= "<div id='nolis_content_site' class='nolis_content_site'>";
            $ret .= "Datenschutzhinweise im Zusammenhang mit der Verarbeitungstätigkeit: <b>".$verfahren["bezeichnung"]."</b>";
            $ret .= "<p>Zweck der Datenverarbeitung:<br>";
            $ret .= $verfahren["beschreibung"]."</p>";

            $ret .= "<p>Datenschutzbeauftragter:<br>";
            $ret .= $verfahren["beschreibung"]."</p>";


            $ret .= "<br><a href='dsgvo".$verfahren["verfahren_id"].".pdf'>Als PDF herunterladen</a><br>(Zuletzt aktualisiert am: ".date("d.m.Y H:i",time())." Uhr)";

            $ret .= "</div>";
            $ret .= "</div>";
            @file_put_contents(PDF_FOLDER."html".$verfahren_id.".tpl",$ret);
        }

function GenerateNolisExportTemplateMaster()
{
    $ret = "<h3 style='color: #0075be; font-size: 130% !important;'>Hinweise zum Datenschutz</h3><p>Der Schutz personenbezogener Daten hat beim Landkreis Diepholz einen hohen Stellenwert. Die Verarbeitung dieser Daten erfolgt im Einklang mit den gesetzlichen Bestimmungen, insbesondere mit den Regelungen der DSGVO (Datenschutzgrundverordnung der Europäischen Union). Nachfolgend informieren wir Sie über die Datenerhebung gem. der Art. 12, 13 und 14 DSGVO zur Verarbeitung Ihrer personenbezogener Daten in den einzelnen Bereichen und über Ihre Rechte.</p>\n";

    $mc = new mysql();
    $sql = "SELECT g.gruppe_id, g.bezeichnung,g.alt_bezeichnung FROM verfahren as v, gruppe as g, organisation as o 
                WHERE o.organisation_id = '".$_SESSION["user"]->organisation_id."' AND g.ad_name != 'admin'  AND  v.upload_enabled = 1 AND v.vollstaendig = 1 AND v.gruppe_id = g.gruppe_id AND g.organisation_id = o.organisation_id GROUP BY g.gruppe_id ORDER BY g.alt_bezeichnung";

    $res = $mc->query($sql);

    $ret .= "<style>
.datenschutzbtndiv{
    margin-top:15px;
    margin-right: 15px;
    float:left;
    line-height:40px;
    text-align:center;
    height:40px; 
    width:321px;
    border:1px solid grey; 
    border-radius: 5px;    
    background-image: url(https://www.diepholz.de/datenschutz/media/btn.png);
    background-repeat: no-repeat; 
    background-position: right center;
}

#datenschutzbtn{
margin-bottom: 30px;
}

.vtaetlist{
margin-bottom:20px;
}

.dsgvoa{
text-decoration: none !important;
 color:#000000 !important;
}

a.dsgvoa:hover{
text-decoration: none !important;
 color:#0075be !important;
}


</style>";
    $ret .="<div id='datenschutzbtn'>";
    while($row = mysqli_fetch_array($res)) {
        $ret .= "<a class='datenschutzbtndiv' href='#".$row["alt_bezeichnung"]."'>".$row["alt_bezeichnung"]."</a>";
    }

    $ret .= "<div style='clear:both;'></div></div>";
    $ret .= "<ul>\n";

    $sql = "SELECT g.gruppe_id, g.bezeichnung,g.alt_bezeichnung FROM verfahren as v, gruppe as g, organisation as o 
                WHERE o.organisation_id = '".$_SESSION["user"]->organisation_id."' AND g.ad_name != 'admin'  AND  v.upload_enabled = 1 AND v.vollstaendig = 1 AND v.gruppe_id = g.gruppe_id AND g.organisation_id = o.organisation_id GROUP BY g.gruppe_id ORDER BY g.bezeichnung";


    $res = $mc->query($sql);
    while($row = mysqli_fetch_array($res))
    {

        if(strlen($row["alt_bezeichnung"]) > 0)
            $name = $row["bezeichnung"].", ".$row["alt_bezeichnung"];
        else
            $name = $row["bezeichnung"];

        $ret .= "<li id='".$row["alt_bezeichnung"]."'><b>".$name."</b></li><ul class='vtaetlist'>";
        $sql2 = "SELECT v.art6_1,v.docs_auto,v.docs_manual,v.art1314,v.verfahren_id, g.gruppe_id, v.bezeichnung FROM verfahren as v, gruppe as g, organisation as o 
                WHERE o.organisation_id = '".$_SESSION["user"]->organisation_id."' AND g.ad_name != 'admin' AND v.upload_enabled = 1 AND v.vollstaendig = 1 AND g.gruppe_id = '".$row["gruppe_id"]."' AND
                 v.gruppe_id = g.gruppe_id AND g.organisation_id = o.organisation_id ORDER BY v.bezeichnung";
        $res2 = $mc->query($sql2);
        while($row2 = mysqli_fetch_array($res2))
        {

            if($row2["docs_auto"] == "1" AND $row2["docs_manual"] == 0 AND $row2["art6_1"] == 0)
            {
                $ret .= "<li><a class='dsgvoa' target='_blank' href='datenschutz/dsgvo".$row2["verfahren_id"].".pdf'>".$row2["bezeichnung"]."</a></li>\n";
            }
            else
            {
                $ret .= "<li>".$row2["bezeichnung"]."</li>\n";
                $ret .= "<ul>";
                if($row2["docs_auto"] == "1")
                    $ret .= "<li><a target='_blank' class='dsgvoa' href='datenschutz/dsgvo".$row2["verfahren_id"].".pdf'>Merkblatt anzeigen</a></li>\n";
                if($row2["art6_1"] == "1")
                    $ret .= "<li><a target='_blank' class='dsgvoa' href='datenschutz/dsgvo".$row2["verfahren_id"]."9999999.pdf'>Einwilligung anzeigen</a></li>\n";

                if($row2["docs_manual"] == "1")
                {
                    $resqq = $mc->query("SELECT * FROM dokumente WHERE typ = 'einversta' AND object_id = '" .$row2["verfahren_id"] . "' AND deleted = 0");
                    while ($rowdoc = mysqli_fetch_array($resqq)) {
                        $ret .= "<li><a class='dsgvoa' target='_blank' href='datenschutz/dsgvo".$row2["verfahren_id"]."9999999".$rowdoc["dokument_id"].".pdf'>".$rowdoc["name"]." anzeigen</a></li>\n";
                    }
                }

                $ret .= "</ul>\n";

            }
        }
        $ret .= "</ul>";
    }
    #  $ret .= "<!--(Zuletzt aktualisiert am: ".date("d.m.Y H:i",time())." Uhr)-->";
    @file_put_contents(PDF_FOLDER."datenschutz.tpl",$ret);
}