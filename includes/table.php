<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 04.05.2018
 * Time: 08:59
 */

class table{

    var $colums;
    var $rows;
    var $tableid;
    var $datatables_enabled = true;

    var $default_sort_col_id = 0;

    var $styles_cols = array();
    var $styles_rows = array();

    function addHeader($name)
    {
        $this->colums[] = $name;
    }

    function addStyle($col,$style)
    {
        $this->styles_cols[count($this->rows)][$col] = $style;
    }
    function addStyleRow($style)
    {
        $this->styles_rows[count($this->rows)] = $style;
    }

    function addRow($row)
    {
        $this->rows[] = $row;
    }

    function table($tableid = false)
    {
        if(!$tableid)
        {
            $this->tableid = "atable_".rand();
        }
    }

    function getContent()
    {

        if(count($this->colums) > 0) {
            $ret ="<table id='".$this->tableid."' class='table compact table-compact table-sm table-striped table-bordered table-hover'>";

            $ret .= "<thead><tr>";
            foreach ($this->colums as $header) {
                $ret .= "<th>" . $header . "</th>";
            }

        $ret .= "</tr></thead>";


        $ret .= "<tbody>";
        $rownum = 0;
        if(count($this->rows) > 0) {
            foreach ($this->rows as $row) {

                if(isset($this->styles_rows[$rownum]))
                {
                    $style = "style='".$this->styles_rows[$rownum]."'";
                    $ret .= "<tr $style>";
                }
                else
                {
                    $ret .= "<tr>";
                }

                $cellnum = 0;
                foreach ($row as $cell) {
                    if(isset($this->styles_cols[$rownum][$cellnum]))
                    {
                        $style = "style='".$this->styles_cols[$rownum][$cellnum]."'";
                        $ret .= "<td $style>" . $cell . "</td>";
                    }
                    else
                    {
                        $ret .= "<td>" . $cell . "</td>";
                    }
                    $cellnum++;
                }
                $ret .= "</tr>";
                $rownum++;
            }
        }
        $ret .= "</tbody>";

        $ret .="</table>";
        }
        else
            $ret = "Keine Daten gefunden!";
        if($this->datatables_enabled)
        {
            $ret .= "<script>$(document).ready( function () {
                    $('#" . $this->tableid . "').DataTable(
                     {
                         'language': {
                            'url': '/js/german.json'
                        },
                        'stateSave': true,
                        'autoWidth': true,
                        'paging':   false,
                        'order': [[ ".$this->default_sort_col_id.", 'asc' ]],                        
                    } );
                    });
                    </script>";
        }

        return $ret;
    }
}