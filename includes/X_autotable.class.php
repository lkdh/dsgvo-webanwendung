<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 08.05.2018
 * Time: 12:03
 */

class autotable{

    public  $table;
    private  $mc;
    private  $data_res;
    private  $headers;
    private  $gotcontent;
    private  $table_name;
    private  $rows_list;
    private  $header_altname = array();

    public  $edit = false;
    public  $add = false;
    public  $delete = false;
    public  $id_row;

    private  $rowtypes = array();

    private  $functions = array();
    private  $fieldsourcedata = array();

    private  $deletefrage;
    private  $deletefragerowwithname;

    private  $disabled = array();

    private  $customrows = array();

    public  $datatables_enabled = true;

    private  $RowEnableDeleteCheck = false;
    private  $RowEnableEditCheck = false;

    function init($table,$rows,$where = ""){
        $this->table = new table();
        $this->mc = new mysql();
        $this->table->datatables_enabled = $this->datatables_enabled;


        $this->table_name = $table;
        $this->rows_list = $rows;

        $sql = "SELECT ".implode(",",$rows)." FROM ".$table." ".$where;
        $this->data_res = $this->mc->query($sql);

        echo $this->mc->getError();

        if(mysqli_num_rows($this->data_res) > 0)
        {
            $header = $this->mc->fetch_array($sql);
            foreach($header as $key => $value )
            {
                $this->headers[] = $key;
                $this->disabled[$key] = false;
            }
            foreach($this->customrows as $key)
            {
                $this->headers[] = $key;
                $this->disabled[$key] = false;
            }

            $this->gotcontent = true;
        }
    }

    function getContent(){

        if(!isset($this->id_row))
        {
            die(" this->id_row ist nicht gesetzt!");
        }
        else
        {
            $this->disabled[$this->id_row] = true;
        }

        if($this->gotcontent)
        {
            foreach($this->headers as $header )
            {
                if(!isset($this->header_altname[$header]))
                    $this->table->addHeader($header);
                else
                    $this->table->addHeader($this->header_altname[$header]);
            }

            if($this->edit OR $this->delete)
            {
                $this->table->addHeader("Bearbeiten");
            }

            while($row = mysqli_fetch_array($this->data_res,MYSQLI_ASSOC))
            {

                if($this->RowEnableDeleteCheck != false)
                {
                       $fn = $this->RowEnableDeleteCheck;
                       $this->delete = $fn($row);
                }
                if($this->RowEnableEditCheck != false)
                {
                    $fn = $this->RowEnableEditCheck;
                    $this->edit = $fn($row);
                }

                foreach($this->customrows as $key)
                {
                    $row[$key] = "";
                }

                if($this->edit OR $this->delete)
                {
                    $btn = "";

                    $data = array(
                        "data" => $row,
                        "table" => $this->table_name,
                        "rows" => $this->rows_list,
                        "headers" => $this->header_altname,
                        "functions" => $this->functions,
                        "rowtypes" => $this->rowtypes,
                        "id_row" => $this->id_row,
                        "id_value" => $row[$this->id_row],
                        "fieldsourcedata" => $this->fieldsourcedata,
                        "deletetext" => $this->deletefrage,
                        "deletefragerowwithname" => $this->deletefragerowwithname,
                        "disabled" => $this->disabled,
                    );

                    if($this->edit)
                        $btn .= " <a href='#' onclick=\"ajax_modal('autotable','getEditForm','".base64_encode(json_encode($data))."','');\"><i class=\"far fa-edit\"></i></a>";

                    if($this->delete)
                        $btn .= " <a href='#' onclick=\"ajax_modal('autotable','getDeleteForm','".base64_encode(json_encode($data))."','');\"><i class=\"lnk-remove far fa-trash-alt\"></i></a>";

                    $row[] = $btn;
                }



                foreach($row as $key => $value)
                {
                    if(isset($this->functions[$key]))
                    {
                        $fn = $this->functions[$key];
                        $row[$key] = $fn($row);
                    }

                    if(isset($this->fieldsourcedata[$key]))
                    {
                        if(isset($this->fieldsourcedata[$key][$value]))
                            $row[$key] = $this->fieldsourcedata[$key][$value];
                        else
                            $row[$key] = "-";
                    }
                }



                $this->table->addRow($row);

            }

            return $this->table->getContent();
        }
    }


    function set_rowenabledeletecheck($name)
    {
        $this->RowEnableDeleteCheck = $name;
    }
    function set_rowenableeditcheck($name)
    {
        $this->RowEnableEditCheck = $name;
    }

    function add_customrow($name){
        $this->customrows[] = $name;
    }

    function enable_delete($frage,$row_with_name){
        $this->deletefrage = $frage;
        $this->deletefragerowwithname = $row_with_name;
        $this->delete = true;
    }

    function set_disabled($key)
    {
        $this->disabled[$key] = true;
    }


    function set_fieldsource($key,$data)
    {
        $this->fieldsourcedata[$key] = $data;
    }

    function set_rowtype($key,$type)
    {
        $this->rowtypes[$key] = $type;
    }

    function set_fieldfunction($key,$funktionname)
    {
        $this->functions[$key] = $funktionname;
    }

    function set_headername($key,$newname)
    {
        $this->header_altname[$key] = $newname;
    }

    function getDeleteForm($data_raw)
    {
        $data = json_decode(base64_decode($data_raw["data"]));

        $key = $data->deletefragerowwithname;
        $relaceval = "<b>\"".$data->data->$key."\"</b>";


        $btn = "<br><br><a href='#' class='btn btn-danger' onclick=\"ajax_action_class('autotable','ajaxDeleteEntry','location_reload','".$data_raw["data"]."','');\">Ja, Eintrag löschen</a>";

        return json_encode(array("status" => 1, "header" => "Eintrag löschen","content" => sprintf($data->deletetext,$relaceval).$btn));
    }

    function ajaxDeleteEntry($data_raw)
    {
        $data = json_decode(base64_decode($data_raw["data"]));

        $mc = new mysql();
        $mc->query("DELETE FROM ".$data->table." WHERE ".$data->id_row." = '".$data->id_value."'");

        return json_encode(array("status"=> 1,"callback" => "location_reload"));
    }

    function getEditForm($data_raw)
    {
        $data = json_decode(base64_decode($data_raw["data"]));

        #echo "<pre>";
        #print_r($data);

        $form = new form("edit-".$data->table);
        $form->add_hidden("raw_form_data",$data_raw["data"]);
        $form->add_hidden("formname",$form->formname);
        $form->setTargetClassFunction("autotable","saveEditForm");
        foreach($data->rows as $key) {


            if(isset($data->disabled->{$key}))
            {
            if($data->disabled->{$key} ==  true)
                $disabled = false;
            else
                $disabled = true;
            }
            else
                $disabled = true;


            if(isset($data->headers->$key))
                $header = $data->headers->$key;
            else
                $header = $key;

            if(isset($data->fieldsourcedata->$key))
            {
                $form->add_select($header,$data->data->$key,$data->fieldsourcedata->$key,"",$key,$disabled);
            }
            else {
                $form->add_textbox($header, $data->data->$key, "", "", "text", $key, $disabled);
            }
        }

        return json_encode(array("status" => 1, "header" => "title","content" => $form->getContent()));
    }

    function saveEditForm($data_raw){
        foreach($data_raw as $key => $value)
        {
            $p = explode("_",$key);
            if(count($p) > 1) {
                if ($p[1] == "rawformdata") {
                    $confdata = $value;
                }
                if ($p[1] == "formname") {
                    $formname = $value;
                }
            }
        }

        $data = json_decode(base64_decode($confdata));

        $updates = array();
        foreach($data_raw as $key => $value)
        {
            if(!startsWith($key,$formname))
            {
                $updates[$key] = $value;
            }
        }

        $ms = new mysql();

        if($ms->updateRow($data->table,$updates,$data->id_row,$data->id_value))
            return json_encode(array("status"=> 1,"location" => ""));
        else
            return json_encode(array("status"=> 0,"alert" => "1"));

    }



}