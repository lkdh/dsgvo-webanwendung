<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 21.03.2018
 * Time: 12:17
 */

class page
{
    var $ldap_group_prefix = "softwareverzeichnis";
    var $pagetitle = "Datenschutz Landkreis Diepholz";
    var $loginerror = "";
    var $user;
    var $content;
    var $sql;

    var $header1 = "";

    function setHeader($text)
    {
        $this->header1 = "<h2>".$text."</h2>";
    }

    function page()
    {

        $this->sql = new mysql();

    // LOGOFF Intent
if (isset($_GET["logoff"])) {
session_destroy();
}

// LOGIN Intent
if (isset($_POST["trylogin"])) {
    $this->try_login($_POST["benutzername"], $_POST["passwort"]);
}


// User case
if (isset($_SESSION["user"])) {
    $this->user = $_SESSION["user"];
    // INHALT ANZEIGEN
    $this->show_content();
} else {
    // LOGINSEITE ANZEIGEN
    $this->show_login();
}
}


    function try_login($username, $password)
    {
        $ldap = ldap_connect(AD_HOST);
        $ldaprdn = 'lkdh' . "\\" . $username;
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        $bind = @ldap_bind($ldap, $ldaprdn, $password);

        if ($bind) {
            $filter = "(sAMAccountName=$username)";
            $result = ldap_search($ldap, AD_BASE_DN, $filter);
            ldap_sort($ldap, $result, "sn");
            $info = ldap_get_entries($ldap, $result);

            foreach ($info[0] as $key => $value) {

                if ($key != "usercertificate" AND !is_numeric($key) AND strlen($key) > 1) {
                    if (isset($value["count"])) {
                        if (!empty($key))
                            @$this->user->$key = @array();

                        if ($value["count"] == 1) {
                            $this->user->$key = $value[0];
                        }
                        if ($value["count"] > 1) {
                            $x = 0;
                            while ($x < $value["count"]) {

                                array_push($this->user->$key, $value[$x]);
                                $x++;
                            }
                        }
                    }
                }
            }

            $userdata = $this->sql->fetch_array("SELECT * FROM user WHERE username = '" . $username . "'");
            if (!isset($userdata["user_id"])) {
                $this->sql->query("INSERT into user (username) VALUES ('" . $username . "')");
                $userdata = $this->sql->fetch_array("SELECT user_id FROM user WHERE username = '" . $username . "'");
            }

            $this->user->userid = $userdata["user_id"];

            $groups = array();
            $auth = false;
            $superadmin = false;
            if(isset($this->user->memberof)) {

                if(!is_array($this->user->memberof))
                {
                    $this->user->memberof = array($this->user->memberof);
                }

                foreach ($this->user->memberof as $group) {

                    if(startsWith($group, "CN=".AD_GROUP_PREFIX."-superadmin"))
                    {
                        $superadmin = true;
                    }

                    if (startsWith($group, "CN=".AD_GROUP_PREFIX) and !startsWith($group, "CN=".AD_GROUP_PREFIX."-superadmin")) {
                        $sp = explode(",", $group);
                        $sp1 = explode("-", $sp[0]);
                        $sp2 = explode("=", $sp[0]);

                        $orga = $this->sql->fetch_array("SELECT * FROM organisation WHERE ad_name = '".$sp1[1]."'");
                        if(!isset($orga["organisation_id"]))
                        {
                            $this->sql->query("INSERT into organisation (ad_name,bezeichnung) VALUES ('".$sp1[1]."','".$sp1[1]."')");
                        }

                        $orga = $this->sql->fetch_array("SELECT * FROM organisation WHERE ad_name = '" . $sp1[1] . "'");
                        $gruppe = $this->sql->fetch_array("SELECT g.gruppe_id, g.ad_name as gruppe_ad_name, g.bezeichnung as gruppen_name,o.bezeichnung as orga_name,g.*,o.* 
                                                              FROM gruppe as g, organisation as o WHERE g.ad_name = '" . $sp1[2] . "' AND o.ad_name='".$sp1[1]."' AND g.organisation_id = o.organisation_id");

                        if (!isset($gruppe["gruppe_id"])) {
                            if (isset($orga["organisation_id"])) {
                                $this->sql->query("INSERT into gruppe (organisation_id,bezeichnung, ad_name) VALUES (" . $orga["organisation_id"] . ",'" . $sp1[2] . "','" . $sp1[2] . "')");
                                $gruppe = $this->sql->fetch_array("SELECT g.gruppe_id, g.ad_name as gruppe_ad_name, g.bezeichnung as gruppen_name,o.bezeichnung as orga_name,g.*,o.* 
                                                              FROM gruppe as g, organisation as o WHERE g.ad_name = '" . $sp1[2] . "' AND o.ad_name='".$sp1[1]."' AND g.organisation_id = o.organisation_id");
                                $groups[] = $gruppe;
                                $auth = true;
                            } else {
                                $this->loginerror = "Sie sind einer Gruppe ohne Organisation zugewiesen!";
                            }
                        } else {
                            $groups[] = $gruppe;
                            $auth = true;
                        }
                    }
                }
            }
            else
            {
                $auth = false;
                $this->loginerror = "Sie sind keiner Organisation zugewiesen!";
            }


            if ($auth) {

                foreach($groups as $group)
                {
                    if($group["gruppe_ad_name"] != "admin")
                    {
                        $this->user->gruppe_id = $group["gruppe_id"];
                        break;
                    }
                }
                $this->user->organisation_id = $groups[0]["organisation_id"];

                $this->user->organisation_adname = $groups[0]["ad_name"];
                $this->user->gruppen = $groups;
                $this->user->data = $userdata;
                $this->user->ad_password = $password;
                $this->user->ad_username = $username;
                $this->user->superadmin = $superadmin;
                $_SESSION["user"] = $this->user;
                return true;
            } else {

                $this->loginerror = "Sie wurden noch keiner Organisation zugewiesen!";
                return false;
            }


        } else {
            $this->loginerror = "Fehler! LDAP Server Antwort: ".ldap_error($ldap).")";
            return false;
        }


    }

    function is_super_admin()
    {
       return $this->user->superadmin;
    }


    function is_admin()
    {
        foreach($this->user->gruppen as $gruppe)
        {
           if($gruppe["gruppe_ad_name"] == "admin")
               return true;
        }
        return false;
    }



    function show_content()
    {
        if (isset($_GET["s"])) {
            $cl = "subpage_" . $_GET["s"];
            if (class_exists($cl)) {
                $subpage = new $cl();


                $this->content = $subpage->getContent($this);
                $this->pagetitle = $subpage->title . " | " . $this->pagetitle;

            } else
                echo "page.class " . $cl . " not found!";
        }
            else
            {
                $cl = "subpage_verfahren";
                if (class_exists($cl)) {
                $subpage = new $cl();


                $this->content = $subpage->getContent($this);
                $this->pagetitle = $subpage->title . " | " . $this->pagetitle;

            } else
                echo "page.class " . $cl . " not found!";
        }


        include("body/page.php");
    }



    function show_login()
    {
        include("body/login.php");
    }

    function pr($object)
    {
        echo "<pre>" . print_r($object) . "</pre>";
    }


}