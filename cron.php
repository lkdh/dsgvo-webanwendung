<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 02.05.2018
 * Time: 13:17
 */
include("config.php");
$mc = new mysql();

$ds = ldap_connect(AD_HOST) or die("Could not connect to LDAP server.");
ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);

if($ds) {
    $ldapbind = ldap_bind($ds, AD_SYNC_USER, AD_SYNC_PASSWORD) or die ("Error trying to bind: ".ldap_error($ds));

     $filter    = '(sn=*)';
     $pageSize = 999;
     $cookie = '';

     do {
         ldap_control_paged_result($ds, $pageSize, true, $cookie);

         $result  = ldap_search($ds, AD_BASE_DN, $filter);
         $entries = ldap_get_entries($ds, $result);
         foreach ($entries as $e) {

             if( isset($e['mail'][0]) AND isset($e['samaccountname'][0]) AND isset($e['sn'][0]) AND isset($e['givenname'][0]))
			 {
                 $users[] = $e['samaccountname'][0];

			     $username = $e['samaccountname'][0];

			     $email = $e['mail'][0];

                 if(isset( $e["streetaddress"][0]))
                     $strasse = $e["streetaddress"][0];
                 else
                     $strasse = "";

                 if(isset( $e["postalcode"][0]))
                    $plz = $e["postalcode"][0];
                 else
                     $plz = "";

                 if(isset( $e["l"][0]))
                    $ort = $e["l"][0];
                 else
                     $ort = "";

                 if(isset( $e["telephonenumber"][0]))
                 $telefon = $e["telephonenumber"][0];
                    else
                        $telefon = "";

                 if(isset( $e["initials"][0]))
                     $anrede = $e["initials"][0];
                 else
                     $anrede = "";

                 $dn = $e["distinguishedname"][0];

                 if(isset( $e["mail"][0]))
                    $email = $e["mail"][0];
                 else
                     $email = "";

                 if(isset( $e["department"][0]))
                    $fachdienst = $e["department"][0];
                 else
                     $fachdienst  ="";

                 if(isset( $e["postofficebox"][0]) AND isset( $e["physicaldeliveryofficename"][0]))
                     $raum = $e["postofficebox"][0].", ".$e["physicaldeliveryofficename"][0];
                 else
                     $raum = "";

                 $vorname = $e["givenname"][0];
                 $nachname = $e["sn"][0];
                 $name = $nachname.", ".$vorname;

                 $db_user = $mc->fetch_array("SELECT * FROM ad_personen WHERE ad_username = '".$username."'");
                 if(!isset($db_user["person_id"])) {
                     $mc->query("INSERT into ad_personen (ad_username) VALUES ('".$username."')");

                     $db_user = $mc->fetch_array("SELECT * FROM ad_personen WHERE ad_username = '".$username."'");
                 }

                 $updates = array(
                     "name" => $name,
                     "strasse" => $strasse,
                     "plz" => $plz,
                     "ort" => $ort,
                     "telefon" => $telefon,
                     "email" => $email,
                     "anrede" => $anrede,
                     "dn" => $dn,
                     "vorname" => $vorname,
                     "nachname" => $nachname,
                     "fachdienst" => $fachdienst,
                     "raum" => $raum
                 );

                 if(!$mc->updateRow("ad_personen",$updates,"person_id",$db_user["person_id"],array("dn" >= "1")))
                     return "ERROR: ". $mc->getError();
			}
         }
         ldap_control_paged_result_response($ds, $result, $cookie);

     } while($cookie !== null && $cookie != '');

    // Reverselookup
    $res = $mc->query("SELECT * FROM ad_personen");
    while($row = mysqli_fetch_array($res))
    {
        $found = false;
        foreach ($users as $user)
        {
            if($user == $row["ad_username"])
            {
                $found = true;
                break;
            }
        }

        if(!$found)
        {
            echo "User not found! ".$row["ad_username"]."\n";
            $mc->query("DELETE FROM ad_personen WHERE person_id = '".$row["person_id"]."'");
        }
    }
}
else
{
    echo "Failed to connect to AD Server!";
}

ldap_close($ds);


