
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.ico">

    <title>Anmelden | <?php echo $this->pagetitle?></title>

    <!-- Bootstrap core CSS -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="/css/login.css" rel="stylesheet">
</head>

<body class="text-center">
<form class="form-signin" action="" method="post">
    <h1 class="h3 mb-3 font-weight-normal">Datenschutz<br> EU-DSGVO</h1>
    <img class="mb-4" src="/images/logo.png" width="300px">
    <?php if(strlen($this->loginerror) > 0)echo "<p class='loginerror'>".$this->loginerror."</p>";?>
    <label for="benutzername" class="sr-only">Benutzername</label>
    <input type="text" id="benutzername" name="benutzername" class="form-control" placeholder="Benutzername" required autofocus>
    <label for="passwort" class="sr-only">Passwort</label>
    <input type="password" id="passwort" name="passwort"  class="form-control" placeholder="Passwort" required>
    <button class="btn btn-lg btn-primary btn-block" name="trylogin" type="submit">Anmelden</button>
</form>
</body>
</html>
