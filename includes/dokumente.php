<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 07.06.2018
 * Time: 11:48
 */


function get_doc_modal_link($dokument_typ,$objectid,$headername = "Headername nicht definiert!",$dokumentname = false)
{
    $mc = new mysql();
    $res = $mc->fetch_array("SELECT count(*) as anz from dokumente WHERE object_id = '".$objectid."' AND typ = '".$dokument_typ."' AND deleted = 0");
    $anz = $res["anz"];

    if ($dokumentname == false)
        $dokumentname = "Dokument";

    if($anz == 0)
        $text = "keine ".$dokumentname." hochgeladen";

    if($anz == 1)
        $text = "ein ".$dokumentname." hochgeladen";

    if($anz > 1)
        $text = $anz." ".$dokumentname." hochgeladen";


    $senddata = base64_encode(json_encode(array("name" => $headername,"objectid" => $objectid,"dokument_typ" => $dokument_typ)));

    return "<script>function show_modal_files_".$objectid.$dokument_typ."(){ajax_modal('dokumente','modal_list_dokumente','".$senddata."');reloadpage();}</script>"
    . "<a href='#' onclick=\"show_modal_files_".$objectid.$dokument_typ."();\"> ".$text."</a>";
}


function get_doc_modal_link_inline($dokument_typ,$objectid,$headername = "Headername nicht definiert!",$dokumentname = false,$disabled = false,$editbtn = false)
{

    $mc = new mysql();
    $res = $mc->query("SELECT * FROM dokumente WHERE object_id = '".$objectid."' AND typ = '".$dokument_typ."' AND deleted = 0");
    $content = "";
    if(mysqli_num_rows($res) > 0)
    {
        while($row = mysqli_fetch_array($res))
        {
            if(!$disabled)
                $removebtn = "<i class='lnk-remove'><a onclick=\"ajax_action_class('dokumente','ajax_remove_dokument','hide_dom_obj_afterdelete_file','".$row["dokument_id"]."')\" class=\"fas fa-times\"></a></i>";
            else
                $removebtn = "";


            if($editbtn)
                $editbtn = "<i class=\"lnk-blue\"><a onclick=\"ajax_modal('dokumente','modal_editfile','rename_document_after_event','".$row["dokument_id"]."')\" class=\"fas fa-edit lnk\"></a></i>  ";
                    else
                $editbtn = "";

            $content .= "<span id='dokument_label_".$row["dokument_id"]."'><a href='dokument.php?id=".$row["dokument_id"]."'><i class=\"far fa-file-pdf\"></i> ".$row["name"]." [".human_filesize($row["size"])."]</a> ".$editbtn.$removebtn."<br></span>";
        }
    }
    else
    {
        $content .= "Bisher keine Dokumente verknüpft!<br>";
    }

    if(!$disabled)
    $content .= "Neue Datei hochladen:<br><input type='file' id='fileupload_".$dokument_typ."'><a href='#' onclick=\"upload_file('fileupload_".$dokument_typ."','dokumente','upload','".$objectid."','".$dokument_typ."',1);\" class='btn btn-primary'>Hochladen</a> ";


    return  $content;

}

class dokumente{

    function modal_editfile($data)
    {
        $mc = new mysql();
        $doc = $mc->fetch_array("SELECT * FROM dokumente WHERE dokument_id = '".$data["formcontrolname"]."'");

        $form = new form("dokument_bearbeiten");
        $form->add_textbox("Bezeichnung",$doc["name"],"","","text","bezechnung",true);
        $form->add_hidden("dokument_id",$data["formcontrolname"]);
        $form->setTargetClassFunction("dokumente","savedocedit");
        return json_encode(array("status" => "1","content" => $form->getContent(),"header"=> "Dokument bearbeiten"));
    }

    function savedocedit($data)
    {
        $mc = new mysql();
        if($mc->query("update dokumente set name = '".$mc->escape($data["bezechnung"])."' WHERE dokument_id = '".$data["dokument_bearbeiten_dokumentid"]."'"))
            return json_encode(array("status" => "1", "msg" => "", "callback" => "ajax_modal_callback", "formcontrol" => "loadmatrix"));

        else
        return json_encode(array("status" => "0", "msg" => "Bitte Name der Datenkategorie angeben ODER Datenkategorie aus Vorlage auswählen"));

}


    function upload($data)
    {
        if(isset($_FILES["file"]["name"])) {
            $p = explode(".", $_FILES["file"]["name"]);
            $extension = $p[count($p) - 1];
            unset($p[count($p) - 1]);
            $name = implode(".", $p);


            $mc = new mysql();
            $mc->query("insert into dokumente (name,filename,extension,object_id,typ,size) VALUES ('" . $name . "','a','" . $extension . "','" . $data["documentid"] . "','" . $data["documenttyp"] . "','" . $_FILES["file"]["size"] . "')");
            move_uploaded_file($_FILES["file"]["tmp_name"], "../uploads/" . $mc->getID() . "." . $extension);
            return json_encode(array("status" => 1, "callback" => "show_modal_files_" . $data["documentid"] . $data["documenttyp"]));
        }
    }

    function ajax_remove_dokument($data)
    {
        $mc = new mysql();
        if($mc->query("update dokumente set deleted = 1 WHERE dokument_id = '".$data["data"]."'"))
        {
            return json_encode(array("status" => 1,"id" => "dokument_label_".$data["data"]));
        }
    }

    function modal_list_dokumente($data)
    {

        $senddata = json_decode(base64_decode($data["data"]));
        $objectid = $senddata->objectid;
        $objecttyp =  $senddata->dokument_typ;

        $mc = new mysql();
        $res = $mc->query("SELECT * FROM dokumente WHERE object_id = '".$objectid."' AND typ = '".$objecttyp."' AND deleted = 0");
        $content = "";
        if(mysqli_num_rows($res) > 0)
        {
            while($row = mysqli_fetch_array($res))
            {
                $removebtn = "<i class='lnk-remove'><a onclick=\"ajax_action_class('dokumente','ajax_remove_dokument','hide_dom_obj_afterdelete_file','".$row["dokument_id"]."')\" class=\"fas fa-times\"></a></i>";
                $content .= "<span id='dokument_label_".$row["dokument_id"]."'><a href='dokument.php?id=".$row["dokument_id"]."'><i class=\"far fa-file-pdf\"></i> ".$row["name"]." [".human_filesize($row["size"])."] ".$removebtn."<br></span>";
            }
        }
        else
        {
            $content .= "Bisher keine Dokumente verknüpft!";
        }

        $content .= "<hr>Neue Datei hochladen:<br><input type='file'  id='fileupload'><a href='#' onclick=\"upload_file('fileupload','dokumente','upload','".$objectid."','".$objecttyp."');\" class='btn btn-success'>Hochladen</a> ";


        return json_encode(array("status" => "1","content" => $content,"header"=> "Verknüpfte Dokumente zu <b>".$senddata->name."</b>"));

    }
}