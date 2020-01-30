<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 23.03.2018
 * Time: 09:51
 */

class mysql{
    var $con;
    var $debug;
    var $connected = false;
    function mysql()
    {
        if(!$this->con = @mysqli_connect(MYSQL_SERVER,MYSQL_USER,MYSQL_PASSWORT,MYSQL_DATENBANK))
            $this->connected = false;
        else
            $this->connected = true;

    }
    function query($sql)
    {
        if($this->debug)
            echo $sql;

        return mysqli_query($this->con,$sql);
    }

    function fetch_array($sql)
    {
        if(! $res = $this->query($sql))
        {
            echo $this->getError();
        }
        else
        return mysqli_fetch_array($res,MYSQLI_ASSOC);
    }
    function getError()
    {
        return mysqli_error($this->con);
    }
    function getID(){
        return mysqli_insert_id($this->con);
    }

    function escape($str)
    {
        return mysqli_real_escape_string($this->con,$str);
    }

    //("software_massnahmen","software_id",$dbdata["software_id"],"massnahmen_id",$tom_ids);
    function updateRelation($table,$base_id_name,$base_id_value,$relation_id_name,$relation_ids)
    {
        $mc = new mysql();
        // fehlenden hinzufügen
        $res = $mc->query("SELECT * FROM ".$table." WHERE ".$base_id_name." = ".$base_id_value);
        while($row = mysqli_fetch_array($res))
        {
            $found = false;
            foreach($relation_ids as $rid)
            {
                if($rid == $row[$relation_id_name])
                {
                    $found = true;
                }
            }
            if(!$found)
            {
                $mc->query(" DELETE FROM ".$table." WHERE ".$base_id_name." = ".$base_id_value." AND ".$relation_id_name." = ".$row[$relation_id_name]);
                echo $mc->getError();
            }
        }

        foreach($relation_ids as $rid)
        {
            $found = false;
            $res = $mc->query("SELECT * FROM ".$table." WHERE ".$base_id_name." = ".$base_id_value);
            while($row = mysqli_fetch_array($res)) {

                if ($rid == $row[$relation_id_name]) {
                    $found = true;
                }
            }
               if(!$found)
               {
                   $mc->query("INSERT into ".$table." (".$base_id_name.",".$relation_id_name.") VALUES ('".$base_id_value."','".$rid."')");
                   echo $mc->getError();
               }
        }
    }

    function updateRow($table,$data,$whereRow,$whereValue,$ignorekeys = array())
    {
        $sql = "SELECT * FROM ".$table." WHERE ".$whereRow." = '".$whereValue."'";
        $olddata = $this->fetch_array($sql);
        $changed = false;
        foreach($data as $key => $value)
        {
            $value = stripslashes($value);

            if(!isset($olddata[$key]))
            {
                echo "Der Wert für ".$key." ist nicht im mysql schema vorhanden!";
            }
            if($olddata[$key] != $value)
            {
                $changed = true;
                add_protokoll("edit", $whereValue, $table,$olddata[$key],$value,$key);
            }
        }
        if($changed) {
            $updatedata = array();
            foreach ($data as $key => $value) {
                $updatedata[] = $key . " = " . "'" . $value . "'";
            }

            $sql = "UPDATE " . $table . " SET " . implode(",", $updatedata) . " WHERE " . $whereRow . " = '" . $whereValue . "'";
            return $this->query($sql);
        }
        else
            return -1;
    }

}