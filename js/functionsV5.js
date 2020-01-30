
function send_form(str_class,str_function,formname,extradata)
{
    var data = $('#' +formname).serialize();
    $.ajax({
        method: "POST",
        url: "ajax.php",
        data: { str_class: str_class,str_function:str_function,formdata: data, extradata:extradata}
    })
        .done(function( msg ) {

            var obj = jQuery.parseJSON(msg );
            if (obj.status == 1)
            {
                if(obj.location != null)
                {
                   location.href = obj.location;
                }
                if(obj.callback != null)
                {
                    var fnname = obj.callback;
                    eval(fnname)(obj);
                }
                if(obj.alert != null)
                {
                    addAlert(obj.title,obj.message,obj.type);
                }
            }
            if (obj.status == 0) {
                addAlert("AJAX Method Fehler!", obj.msg, 'danger');
            }
            if (obj.status == 2) {
                addAlert(obj.header, obj.msg, 'warning');
            }


        })
        .fail(function( msg,errormsg,errmsgtext ) {
            addAlert("AJAX Fehler!","Fehler in der Funktion ajax_action: " + errmsgtext,'danger');
        });

}

function ajax_action(method,callback)
{
    $.ajax({
        method: "POST",
        url: "ajax.php",
        data: { method: method }
    })
        .done(function( msg ) {

            var obj = jQuery.parseJSON(msg );
            if (obj.status == 1)
            {
                callback(obj)
            }
            else
                addAlert("AJAX Method Fehler!",obj.msg,'danger');


        })
        .fail(function( msg,errormsg,errmsgtext ) {
        addAlert("AJAX Fehler!","Fehler in der Funktion ajax_action: " + errmsgtext,'danger');
        });

}

function ajax_getasync_Content(str_class,str_function,data)
{
    $.ajax({
        method: "POST",
        url: "ajax.php",
        data: { str_class: str_class,str_function:str_function,extradata: data}
    })
        .done(function( msg ) {

         $('#subcontentarea').html(msg);

        })
        .fail(function( msg,errormsg,errmsgtext ) {
            addAlert("AJAX Fehler!","Fehler in der Funktion ajax_action: " + errmsgtext,'danger');
        });

}

function ajax_action_class(str_class,str_function,callback,data,data1)
{
    $.ajax({
        method: "POST",
        url: "ajax.php",
        data: { str_class: str_class,str_function:str_function,data:data,data1:data1}
    })
        .done(function( msg ) {

            var obj = jQuery.parseJSON(msg );
            if (obj.status == 1)
            {
                eval(callback)(obj);
            }
            else
                addAlert("AJAX Method Fehler!",obj.msg,'danger');


        })
        .fail(function( msg,errormsg,errmsgtext ) {
            addAlert("AJAX Fehler!","Fehler in der Funktion ajax_action: " + errmsgtext,'danger');
        });

}

function addAlert(title,message,type) {
    $('#contentarea').prepend(
        '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
        '  <strong>' + title + '</strong><br>' +
        '<button type="button" class="close" data-dismiss="alert">' +
        '&times;</button>' + message + '</div>');
}

function location_index(obj)
{
    location.href = "index.php?show=home";
}
function location_reload(obj)
{
    location.reload();
}


function form_load_select_data(formid,classname,functionname,data,value)
{
    $.ajax({
        method: "POST",
        url: "ajax.php",
        data: { str_class: classname,str_function:functionname,data:data }
    })
        .done(function( msg ) {
            var obj = jQuery.parseJSON(msg );
            if (obj.status == 1)
            {
                $('#' + formid).find('option').remove();
                $.each(obj.data, function (i, item) {
                    var selected = false;
                    if(item.val == value)
                    {
                        selected = true;
                    }

                    $('#' + formid).append($('<option>', {
                        value: item.val,
                        text : item.label,
                        selected: selected
                    }));

                });
            }
            if (obj.status == 0) {
                addAlert("AJAX Method Fehler!", obj.msg, 'danger');
            }
                    })
        .fail(function( msg,errormsg,errmsgtext ) {
            addAlert("AJAX Fehler!","Fehler in der Funktion ajax_action: " + errmsgtext,'danger');
        });

}

function ajax_modal(classname,modalname,data,controlname)
{
    $.ajax({
        method: "POST",
        url: "ajax.php",
        data: { str_class: classname,str_function:modalname,data:data,formcontrolname: controlname }
    })
        .done(function( msg ) {
            var obj = jQuery.parseJSON(msg );
            if (obj.status == 1)
            {
                var header = obj.header;
                var content = obj.content;
                doModal(header,content);
            }
            if (obj.status == 0) {
                addAlert("AJAX Method Fehler!", obj.msg, 'danger');
            }
        })
        .fail(function( msg,errormsg,errmsgtext ) {
            addAlert("AJAX Fehler!","Fehler in der Funktion ajax_action: " + errmsgtext,'danger');
        });
}

function ajax_modal_callback(obj){
    hideModal();
    var fnname = obj.formcontrol;
    eval(fnname)(obj);
}

function doModal(heading, formContent) {
     $('#modal_body').html(formContent);
    $('#modal_title').html(heading);
    $("#modal_page").modal('show');
    //$("#dynamicModal").modal('show');
    //$('#dynamicModal').on('hidden.bs.modal', function (e) {
    //    $(this).remove();
   // });
}

function hideModal()
{
    // Using a very general selector - this is because $('#modalDiv').hide
    // will remove the modal window but not the mask
    $('#modal_page').modal('hide');
}



function upload_file_async(controlname,str_class,str_function)
{
    $.ajax({
        // Your server script to process the upload
        url: 'ajax.php',
        type: 'POST',

        // Form data
        data: new FormData($('form')[0]),

        // Tell jQuery not to process data or worry about content-type
        // You *must* include these options!
        cache: false,
        contentType: false,
        processData: false,

        // Custom XMLHttpRequest
        xhr: function() {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) {
                // For handling the progress of the upload
                myXhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        $('progress').attr({
                            value: e.loaded,
                            max: e.total,
                        });
                    }
                } , false);
            }
            return myXhr;
        }
    });
}

function hide_dom_obj_afterdelete_file(id)
{
    console.log(id.id);
    $("#" +id.id).hide();
    reloadpage();
}


function upload_file(inputid,class_str,function_str,documentid,documenttyp,reload)
{
    var file_data = $('#'+inputid).prop('files')[0];
    var form_data = new FormData();
    form_data.append('file', file_data);
    form_data.append('str_class', class_str);
    form_data.append('str_function', function_str);
    form_data.append('documentid', documentid);
    form_data.append('documenttyp', documenttyp);

    $.ajax({
        url: 'ajax.php', // point to server-side PHP script
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,
        type: 'post',
        success: function(msg){
            var obj = jQuery.parseJSON(msg );
            if (obj.status == 1)
            {
                if(reload == 1)
                {
                    location.reload();
                }
                else {
                    eval(obj.callback)(obj);
                }
            }
            else
                addAlert("AJAX UPLOAD Fehler!",obj.msg,'danger');
        }
    });
}

function art1314selector()
{
    console.log($('#art1314').val());
    if($('#art1314').val() == "14")
        $('#form-gr_art14_unternehmen').show();
    else
    {
        if($('#art1314').val() == "1314")
            $('#form-gr_art14_unternehmen').show();
        else
        {
            $('#form-gr_art14_unternehmen').hide();
        }
    }

}

function releasedtonolis(data)
{
    addAlert(data.header,data.msg,data.alerttype);

    console.log(data);
}

function loadingindicator()
{
    $("#loadingindicator").show();
    $("#contentmatrix").hide();
}

function show_datamatrix(daten)
{
    $("#loadingindicator").hide();
    $("#contentmatrix").show();

    $('#contentmatrix').html(daten.content);
    console.log("show_datamatrix called!");
    console.log(daten);
}