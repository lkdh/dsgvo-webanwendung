<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 08.05.2018
 * Time: 12:04
 */

class subpage_admin_benutzerverwaltung  extends subpage{

    function getContent($page)
    {
        $atable = new autotable();
        $atable->add_customrow("email");
        $atable->add_customrow("fachdienst");

        $atable->init("user",array("user_id","username","default_organisation_id","right_can_delete"));
        $atable->set_headername("username","Benutzername");
        $atable->set_headername("right_can_delete","Darf Verarbeitungstätigkeiten löschen?");


        $atable->set_fieldsource("right_can_delete",array(1 => "Ja",0 => "Nein"));

        $atable->edit = true;
        $atable->delete = true;
        $atable->id_row = "user_id";

        $form = new form("add_ad_account");
        $data = array();
        $email = array();

        $res = $page->sql->query("SELECT * FROM ad_personen  ORDER by name ASC");
        while($row = mysqli_fetch_array($res))
        {
            $data[$row["ad_username"]] = $row["name"]."(".$row["ad_username"].")";
            $email[$row["ad_username"]] = $row["email"];
        }
        $form->add_select("AD Benutzerkonto","",$data,"","adkonto",true);


        function set_email($data)
        {
            $ms = new mysql();
            $user = $ms->fetch_array("SELECT * FROM ad_personen WHERE ad_username = '".$data["username"]."'");
            return $user["email"];
        }

        $atable->set_fieldfunction("email","set_email");

        function set_fd($data)
        {
            return substr($data["username"],0,3);
        }
        $atable->set_fieldfunction("fachdienst","set_fd");




        $data = array();
        $res = $page->sql->query("SELECT g.bezeichnung as gruppe_name, o.bezeichnung as orga_name, g.gruppe_id as gruppe_id 
                                  FROM gruppe as g, organisation as o WHERE g.organisation_id = o.organisation_id ORDER by orga_name,gruppe_name ASC");
        while($row = mysqli_fetch_array($res))
        {
            $data[$row["gruppe_id"]] = $row["orga_name"]." - ".$row["gruppe_name"];
        }

        $form->add_select("Organisationseinheit","",$data,"","orga",true);
        $form->setTargetClassFunction("subpage_admin_benutzerverwaltung","add_user");

        return $atable->getContent().      $this->Card($form->getContent(),"AD Benutzerkonto hinzufügen");
    }
    function add_user($data)
    {
        $mc = new mysql();
        $ad_user = $mc->fetch_array("SELECT * FROM ad_personen WHERE ad_username = '".$data["adkonto"]."'");
        $gruppe = $mc->fetch_array("SELECT o.ad_name as adorganame, g.ad_name as adgroupname, g.bezeichnung as gruppe_name, o.bezeichnung as orga_name, g.gruppe_id as gruppe_id 
                                  FROM gruppe as g, organisation as o WHERE g.organisation_id = o.organisation_id AND g.gruppe_id = '".$data["orga"]."'");

        $username = $_SESSION["user"]->ad_username;
        $password = $_SESSION["user"]->ad_password;

        $adServer = "ldap://w12dc1.lkdh.intern";
        $ldap = ldap_connect($adServer);
        $ldaprdn = 'lkdh' . "\\" . $username;
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        $bind = ldap_bind($ldap, $ldaprdn, $password);
        $group_dn = "CN=dsgvo-".$gruppe["adorganame"]."-".$gruppe["adgroupname"].",OU=sync-dsgvo,OU=DH,DC=lkdh,DC=intern";
        $user_dn = $ad_user["dn"];

        if(ldap_mod_add($ldap,$group_dn,array("member" => $user_dn))) {

            if ($ad_user["anrede"] == "Herr")
                $sn = "geehrter";
            else
                $sn = "geehrte";

            $from = "From: Henrik Hansen <henrik.hansen@diepholz.de>";
            $text = "Sehr geehrter " . $ad_user["anrede"] . " " . $ad_user["nachname"] . ",
ab sofort können Sie sich mit Ihrem Benutzernamen  <b>" . $data["adkonto"] . "</b> und Ihrem persönlichen Passwort unter der Adresse <a href='https://datenschutz.diepholz.intern/'>https://datenschutz.diepholz.intern/</a> anmelden.

In der DSVGO-Webanwendung wurde Ihre Benutzerkennung der Organisation <b>" . $gruppe["orga_name"] . "</b> => Organisationseinheit <b>" . $gruppe["gruppe_name"] . "</b> hinzugefügt.

Für Rückfragen stehe ich Ihnen gerne zur Verfügung.
__
Mit freundlichen Grüßen
Henrik Hansen
Team \"Organisation, Prozesse und Sicherheit\"
 
Landkreis Diepholz
Fachdienst 12 eGovernment
Niedersachsenstr. 2
49356 Diepholz
Telefon: 05441-976-1099
Telefax: 05441-976-1782
EMail:  henrik.hansen@diepholz.de
Internet:  http://www.diepholz.de 
";
            $from = "From: Henrik Hansen <henrik.hansen@diepholz.de>";

            $headerFields = array(
                $from,
                "MIME-Version: 1.0",
                "Content-Type: text/html;charset=utf-8"
            );

            mail($ad_user["email"], "[DSGVO-Webanwendung] Sie wurden für die Nutzung freigeschaltet", nl2br($text), implode("\r\n", $headerFields));

            return json_encode(array("status" => 1,"alert" => 1,"title" =>"Benutzer wurde angelegt","message" => "Der Benutzer ". $ad_user["anrede"] . " " . $ad_user["nachname"] ." wurde der AD Gruppe ".$gruppe["gruppe_name"]." hinzugefügt.","type" => "success"));

        }
        else
            return json_encode(array("status" =>1,"alert" => 1,"title" =>"Fehler beim Benutzer in Gruppe verknüpfen","message" => "Fehler beim User in Gruppe packen.<br> User: ".$user_dn."<br>".$group_dn."<br>Fehler: ".ldap_error($ldap),"type" => "danger"));
        }
}