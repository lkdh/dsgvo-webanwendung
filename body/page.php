
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title><?php echo $this->pagetitle?></title>

    <!-- Bootstrap core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/open-iconic-bootstrap.css" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css" integrity="sha384-+d0P83n9kaQMCwj8F4RJB66tzIwOKmrdb46+porD/OvrJ+37WqIM7UoBtwHO6Nlg" crossorigin="anonymous">

    <link href="/css/smart_wizard.css" rel="stylesheet" type="text/css" />
    <link href="/css/smart_wizard_theme_arrows.css" rel="stylesheet" type="text/css" />

    <link href="/css/jquery.dm-uploader.min.css" rel="stylesheet" type="text/css" />
    <link href="/css/jquery.dataTables.min.css" rel="stylesheet">


    <!-- Custom styles for this template -->
    <link href="/css/page.css" rel="stylesheet">

    <script src="/js/popper.min.js"></script>
    <script src="/js/functionsV5.js"></script>

    <script src="/js/jquery-3.3.1.min.js"></script>
    <script src="/js/jquery.serialzize.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/jquery.dm-uploader.min.js"></script>
    <script src="/js/jquery.dataTables.min.js"></script>


</head>

<body>

<div class="modal" id='modal_page' tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title"></h5>
                <a class="close" data-dismiss="modal">×</a>
            </div>
            <div class="modal-body" id="modal_body">
            </div>
            <div class="modal-footer">
                <span class="btn btn-danger" data-dismiss="modal">Abbrechen</span>
            </div>
        </div>
    </div>
</div>


<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="index.php">Datenschutz</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse navbar-expand-sm" id="navbarsExampleDefault">

        <ul class="navbar-nav mr-auto">

            <li class="nav-item">
                <a class="nav-link" href="?s=verfahren">Meine Verarbeitungstätigkeiten</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="?s=beispiele">Beispiele</a>
            </li>
            <li class="nav-item">
                <a target="_blank" class="nav-link" href="pruefbericht.php">DSB-Paket</a>
            </li>


            <?php

            if($this->is_admin())
            {

                $menus = array(
                        "Technische / Organisatorische Maßnahmen" => array("url" => "admin_tom", "superadmin" => false) ,
                        "EDV-Anwendungen" => array("url" => "admin_edv_anwendungen", "superadmin" => true) ,
                        "Datenkategorien" => array("url" => "admin_datenkategorien", "superadmin" => true) ,
                        "Personen" => array("url" => "admin_personen", "superadmin" => false) ,
                        "Organisation" => array("url" => "admin_organisation", "superadmin" => false) ,
                        "Benutzer" => array("url" => "admin_benutzerverwaltung", "superadmin" => true) ,
                        "Gruppen" => array("url" => "admin_gruppen", "superadmin" => false),
                );

               echo "<li class=\"nav-item dropdown\">
                    <a class=\"nav-link dropdown-toggle\" href=\"#\" id=\"dropdown01\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">Administration</a>
                    <div class=\"dropdown-menu\" aria-labelledby=\"dropdown01\">";

               foreach($menus as $key => $value)
               {

                   if($value["superadmin"] == true)
                   {
                       if($this->is_super_admin())
                       {
                           echo "<a class=\"dropdown-item\" href=\"?s=".$value["url"]."\">".$key."</a>";
                       }
                   }
                   else
                   echo "<a class=\"dropdown-item\" href=\"?s=".$value["url"]."\">".$key."</a>";
               }
               echo "</div>
          </li>
";
            }

            ?>
        </ul>
        <span class="navbar-nav nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="dropdown02" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $this->user->sn.", ".$this->user->givenname?></a>
            <div class="dropdown-menu" aria-labelledby="dropdown02">
                <a class="dropdown-item" href="?s=konto">Mein Konto</a>
                <a class="dropdown-item" onclick="ajax_action('ajax_logoff',location_index)" href="#">Abmelden</a>
            </div>
        </span>
    </div>
</nav>

<main role="main" id="contentarea" class="container-fluid">

    <?php
    echo $this->header1;

    if($_SERVER["SERVER_NAME"] == "dev-datenschutz.lkdh.intern")
    {
        echo alert("Entwicklungsumgebung!!","Derzeit wird die Datenbank: ".MYSQL_DATENBANK." verwendet!","danger");

    }

    echo $this->content;
    ?>

</main><!-- /.container -->

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script>
$(function () {

$('[data-toggle="tooltip"]').tooltip();
})

</script>
</body>
</html>
