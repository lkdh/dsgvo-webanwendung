<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 22.03.2018
 * Time: 13:47
 */

function ajax_logoff()
{
    session_destroy();
    echo json_encode(array("status" => 1));
}