<?php
/**
 * Created by PhpStorm.
 * User: d12hanse
 * Date: 04.05.2018
 * Time: 10:15
 */

class uploader
{

    function getContent($class,$method,$customdata = "")
    {
        $ret = "<div id=\"drop-area\">
                    <input type=\"file\" title=\"Click to add Files\">
                </div>
                <script>
                $(\"#drop-area\").dmUploader({
                url: 'ajax.php',

                extraData: {
                    \"str_class\": '".$class."',
                    \"str_function\": '".$method."',
                    \"data\": '".$customdata."',
                },

                onInit: function(){
                    console.log('Callback: Plugin initialized');
                },
  
                onUploadSuccess: function(id, data){
                    var obj = jQuery.parseJSON(data );
                    if (obj.status == 1)
                    {
                        var header = obj.header;
                        var content = obj.content;
                        doModal(header,content);
                    }
                    if (obj.status == 0) {
                        addAlert(\"AJAX Method Fehler!\", obj.msg, 'danger');
                    }
                },
                onUploadError: function( msg,errormsg,errmsgtext ) {
                    addAlert(\"AJAX Fehler!\",\"Fehler in der Funktion ajax_action: \" + errmsgtext,'danger');
                }
                });</script>
                
";

        return $ret;
    }
}