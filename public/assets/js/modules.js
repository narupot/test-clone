var requestStatus,
    reqCounter = 5,
    timeoutHandle,
    ajax_res_status = "init";
var nearRange =[80,85,90,95,100];

var computePercentage = function(ps, progressbar){
    if(requestStatus!= "complete"){
      $(progressbar).progressbar({"value": reqCounter});      
    }
     
    if(reqCounter!= 100){      
      timeoutHandle = setTimeout(function(){
           computePercentage(ps, progressbar);
      }, 200);  
    }

    //in case request (restrict not reach to 100)  
    if(ajax_res_status === "init" && ajax_res_status!== "success"){
        if(nearRange.indexOf(reqCounter)==-1) reqCounter++; 
    }else if(ajax_res_status !== "init" && ajax_res_status === "success"){
        reqCounter = nearRange.splice(0,1)[0];       
    }
};


$(function() {
    var token = window.Laravel.csrfToken;
    $(".check-plugin_version").on("click", function() {
        var module_name = $(this).attr('id');
        $.ajax({
            url: check_module_version_url,
            type: "POST",
            data: {
                "module_name": module_name,
                "_token": token
            },
            beforeSend: function(){
                $('.showLoaderTable').removeClass('hide');
            },
            success: function(response) {
                $('.showLoaderTable').addClass('hide');
                if (response == 'not_be_installed') {
                    var message = " <p style='color:#bc4343; padding-top:20px;'>You are trying to install less or same version of this module. You could only install heigher version. </p>";
                    $("#dialog-confirm").html(message);
                    // Define the Dialog and its properties.
                    $("#dialog-confirm").dialog({
                        resizable: false,
                        closeIcon: true,
                        theme: 'modern',
                        closeOnEscape: false,
                        open: function(event, ui) {
                            $(".ui-dialog-titlebar-close").hide();
                        },
                        draggable: false,
                        modal: true,
                        title: "Module Version Update Alert",
                        height: 350,
                        width: 500,

                        buttons: {
                            "OK": function() {
                                $(this).dialog('close');
                                callback(false,module_name,token);

                            }
                        }
                        
                    });

                } else {

                    $("#dialog-confirm").html(response);
                    // Define the Dialog and its properties.
                    $("#dialog-confirm").dialog({
                        resizable: false,
                        closeIcon: true,
                        theme: 'modern',
                        closeOnEscape: false,
                        open: function(event, ui) {
                            $(".ui-dialog-titlebar-close").hide();
                        },
                        draggable: false,
                        modal: true,
                        title: "Module Version Update Alert",
                        height: 350,
                        width: 500,

                        buttons: {
                            "Yes": function() {
                                $(this).dialog('close');
                                callback(true, module_name, token);
                            },
                            "No": function() {
                                $(this).dialog('close');
                                callback(false, module_name, token);
                            }
                        }
                        
                    });

                }
            }

        });
    });

    function callback(status, module_name, token) {
        
        if (status) {
            $.ajax({
                url: url_v_install,
                type: "POST",
                data: {
                    "module_name": module_name,
                    "_token": token
                },
                success: function(response) {
                    
                        var response_content = "<center>"+response.message+"</center>";
                        
                        $("#dialog-confirm").html(response_content);
                        $("#dialog-confirm").dialog({
                            resizable: false,
                            closeIcon: true,
                            theme: 'modern',
                            closeOnEscape: false,
                            open: function(event, ui) {
                                $(".ui-dialog-titlebar-close").hide();
                            },
                            draggable: false,
                            modal: true,
                            title: "Module Version Update Status",
                            height: 150,
                            width: 500,

                            buttons: {
                                "OK": function() {
                                    $(this).dialog('close');

                                }
                            }, 
                            close: function () {
                                location.reload(true);

                            }
                            
                        });

                    
                }

            });

        } else {

            $.ajax({
                url: url_v_uninstall,
                type: "POST",
                data: {
                    "module_name": module_name,
                    "_token": token
                },
                success: function(response) {
                    if (response != '') {
                        //alert(response);
                        var response_content = "<center>Plugin Version has been upgraded successfylly.</center>";
                        

                        $("#dialog-confirm").html(response_content);
                        $("#dialog-confirm").dialog({
                            resizable: false,
                            closeIcon: true,
                            theme: 'modern',
                            closeOnEscape: false,
                            open: function(event, ui) {
                                $(".ui-dialog-titlebar-close").hide();
                            },
                            draggable: false,
                            modal: true,
                            title: "Module Version Update Status",
                            height: 350,
                            width: 500,

                            buttons: {
                                "OK": function() {
                                    $(this).dialog('close');

                                }
                            }, 
                            close: function () {
                                location.reload(true);

                            }
                            
                        });
                    }
                }

            });
        }
    }

    

    // Progress bar part
    var actionType;
    var moduleName, progressTimer, progressbar_unins = $("#progressbar_unins"), progressbar_ins = $("#progressbar_ins"),
        progressLabel_unins= $(".progress-label-unins"),
        progressLabel_ins = $(".progress-label-ins"),
        dialogButtons = [{
            text: "Cancel Instalation",
            click: closeDownload
        }],
        dialog_ins = $("#dialog_ins").dialog({
            autoOpen: false,
            closeOnEscape: false,
            resizable: false,
            modal:true,
            // buttons: dialogButtons,
            open: function() {
                actionType = "install";
                progressTimer = setTimeout(progress(actionType), 2000);
                requestStatus = undefined;
                reqCounter = 5;
            },
            beforeClose: function() {
                installButton.button("option", {
                    disabled: false,
                    label: "Start Installation"
                });
            }
        }),

        dialog_unins = $("#dialog_unins").dialog({
            autoOpen: false,
            closeOnEscape: false,
            resizable: false,
            modal:true,
            // buttons: dialogButtons,
            open: function() {
                actionType = "uninstall";
                progressTimer = setTimeout(progress(actionType), 2000);
                requestStatus = undefined;
                reqCounter = 5;
            },
            beforeClose: function() {
                uninstallButton.button("option", {
                    disabled: false,
                    label: "Start Uninstallation"
                });
            }
        }),

        installButton = $(".installButton").button().on("click", function() {
            
            $(this).button("option", {
                disabled: true,
                label: "Installing..."
            });
            moduleName = $(this).attr('id');
            dialog_ins.dialog("open");
        });

        uninstallButton = $(".uninstallButton").button().on("click", function() {
           
            $(this).button("option", {
                disabled: true,
                label: "Uninstalling..."
            });
            moduleName = $(this).attr('id');
            dialog_unins.dialog("open");
        });

    progressbar_unins.progressbar({
        value: true,
        change: function() {
            progressLabel_unins.text("Progress: " + reqCounter + "%")
            progressbar_unins.progressbar({"value": reqCounter});           
        },
        complete: function() {
            progressLabel_unins.text("Uninstallation Completed!");
            dialog_unins.dialog("option", "buttons", [{
                text: "Close",
                click: closeDownload,
            }]);
            $(".ui-dialog button").last().focus();
            requestStatus = "complete";
            reqCounter = 100;
            window.clearTimeout(timeoutHandle);
        }
    });

    progressbar_ins.progressbar({
        value: true,
        change: function() {
            progressLabel_ins.text("Progress: " + reqCounter + "%")
            progressbar_ins.progressbar({"value": reqCounter});           
        },
        complete: function() {
            
            progressLabel_ins.text("Installation Completed!");
            dialog_ins.dialog("option", "buttons", [{
                text: "Close",
                click: closeDownload,
            }]);
            $(".ui-dialog button").last().focus();
            requestStatus = "complete";
            reqCounter = 100;
            window.clearTimeout(timeoutHandle);
        }
    });

    function progress(type) {
        requestStatus= undefined;

        if(typeof type!= "undefined" && type ==='install'){
            var requestUrl = install_url,
                progressLabel = progressLabel_ins,
                progressbar = progressbar_ins,
                dialog = dialog_ins,
                text = "Installation";
        }else if(typeof type!="undefined" && type === "uninstall"){
            var requestUrl = uninstall_url,
                progressLabel = progressLabel_unins,
                progressbar = progressbar_unins,
                dialog = dialog_unins,
                text = "Uninstallation";
        }
        
        $.ajax({
            url: requestUrl,
            type: "POST",
            data: {
                "module_slug_name": moduleName,
                "_token": token
            },
            beforeSend: function(xhr) {
              nearRange = [80,85,90,95,100];              
              computePercentage(progressLabel, progressbar);             
            },            
            success: function(response) {
                if (response.status == 'success') {
                    ajax_res_status = "success";  

                } else {
                    progressLabel.text(text+" failed and progress rollbacked.").css({'color':'#FF545A'});
                    dialog.dialog("option", "buttons", [{
                        text: "Close",
                        click: closeDownload,
                    }]);
                    $(".ui-dialog button").last().focus();
                    requestStatus = "failed";
                    reqCounter = false;
                    window.clearTimeout(timeoutHandle);
                      
                }
            },
            error: function(){
                progressLabel.text(text+" intrupped and progress rollbacked.").css({'color':'#FF545A'});
                dialog.dialog("option", "buttons", [{
                    text: "Close",
                    click: closeDownload,
                }]);
                $(".ui-dialog button").last().focus();
                requestStatus = "failed";
                reqCounter = false;
                window.clearTimeout(timeoutHandle);
                }
        });
    };

    function closeDownload(type) {
        if(typeof actionType!= "undefined" && actionType ==='install'){  
            actionType = "";          
            var progressLabel = progressLabel_ins,
                progressbar = progressbar_ins,
                dialog = dialog_ins,
                Button = installButton;
        }else if(typeof actionType!= "undefined" && actionType === "uninstall"){
            actionType = "";
            var progressLabel = progressLabel_unins,
                progressbar = progressbar_unins,
                dialog = dialog_unins,
                Button = uninstallButton;
        }

        clearTimeout(progressTimer);
        dialog.dialog("close");           
        progressbar.progressbar("value", false);
        progressLabel
            .text("Starting Installation...");
        Button.focus();
        location.reload(true);

    }
});