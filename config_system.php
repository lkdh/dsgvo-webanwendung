<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 21.03.2018
 * Time: 12:17
 */

    define("MYSQL_PASSWORT","Password");
    define("MYSQL_USER","user");
    define("MYSQL_DATENBANK","datenbank");
    define("MYSQL_SERVER","localhost");
    ini_set("display_errors","0");

    define("FTP_HOST","www.diepholz.de");
    define("FTP_USER","user");
    define("FTP_PASS","password");


    define("PDF_FOLDER","/var/www/datenschutz.diepholz.intern/release/");
    define("UPLOAD_FOLDER","/var/www/datenschutz.diepholz.intern/uploads/");

    define("AD_HOST","ldap://dc.lkdh.intern");
    define("AD_BASE_DN","dc=lkdh,dc=intern");
    define("AD_GROUP_PREFIX","dsgvo");

    define("AD_SYNC_USER","benutzer@lkdh");
    define("AD_SYNC_PASSWORD","password");
