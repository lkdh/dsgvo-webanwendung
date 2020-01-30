<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 26.03.2019
 * Time: 16:33
 */


function has_ftp_upload()
{
    return file_exists("ftpconfig/".$_SESSION["user"]->organisation_adname.".php");
}