<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 24.05.2018
 * Time: 13:31
 */


class subpage_verfahren_historie  extends subpage
{
    function getContent($page)
    {
        $res = $page->sql->query("SELECT * FROM protokoll WHERE object_typ = 'verfahren' AND object_id = '".$_GET["id"]."' ORDER BY protokoll_id DESC ");
        $tabelle = new table();

        while($row = mysqli_fetch_array($res,MYSQLI_ASSOC))
        {
            $row = array(
            "time" => date("d.m.Y H:i:s",$row["time_created"])." Uhr",
                "Schlüssel" => $row["value_key"],
            "art" => $row["action"],
            "alter Wert" => $row["old_value"],
            "neuer Wert" => $row["new_value"],
                "user_id" => $row["user_id"]);
            $tabelle->addRow($row);
        }
        $verfahren = $page->sql->fetch_array("SELECT * FROM verfahren where verfahren_id = '".$_GET["id"]."'");
        return $this->card($tabelle->getContent(),"Bearbeitungshistorie von \"".$verfahren["bezeichnung"]."\"");
    }
}