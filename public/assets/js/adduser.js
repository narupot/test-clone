
jQuery(document).ready(function(){

    $("#add_admin_form").bootstrapValidator({
                
        fields: {
            role: {
                ignore: ':disabled, :hidden, :not(:visible)',
                validators: {
                    notEmpty: {
                        message: langMsg.please_select_user_role
                    }
                }
            },
            nick_name: {
                validators: {
                    notEmpty: {
                        message: langMsg.nick_name_is_required
                    }
                },
                required: true,
                minlength: 3
            },        
            first_name: {
                validators: {
                    notEmpty: {
                        message: langMsg.first_name_is_required
                    }
                },
                required: true,
                minlength: 3
            },
            last_name: {
                validators: {
                    notEmpty: {
                        message: langMsg.last_name_is_required
                    }
                },
                required: true,
                minlength: 3
            },
            password: {
                validators: {
                    notEmpty: {
                        message: langMsg.password_is_required
                    },
                    different: {
                        field: 'first_name,last_name',
                        message: langMsg.password_should_not_match_first_or_last_name
                    }
                }
            },
            password_confirm: {
                validators: {
                    notEmpty: {
                        message: langMsg.confirm_password_is_required
                    },
                    identical: {
                        field: 'password',
                        message: langMsg.password_and_confirm_password_should_be_same
                    },
                    different: {
                        field: 'first_name,last_name',
                        message: langMsg.password_should_not_match_first_or_last_name
                    }
                }
            },
            email: {
                validators: {
                    notEmpty: {
                        message: langMsg.email_address_is_required
                    },
                    emailAddress: {
                        message: langMsg.please_enter_valid_email_address
                    }
                }
            }
            // ,
            // contact_no: {
            //     validators: {
            //         notEmpty: {
            //             message: langMsg.contact_number_is_required
            //         },
            //         integer: {
            //             message: langMsg.please_enter_valid_contact_number
            //         }
            //     }
            // },
            // gender: {
            //     validators: {
            //         notEmpty: {
            //             message: langMsg.please_select_gender 
            //         }
            //     }
            // },
            // dob: {
            //     validators: {
            //         notEmpty: {
            //             message: langMsg.please_select_date_of_birth
            //         }
            //     }
            // }
        }
    });

    $("#edit_admin_form").bootstrapValidator({
                
        fields: {
            nick_name: {
                validators: {
                    notEmpty: {
                        message: langMsg.nick_name_is_required
                    }
                },
                required: true,
                minlength: 3
            },        
            first_name: {
                validators: {
                    notEmpty: {
                        message: langMsg.first_name_is_required
                    }
                },
                required: true,
                minlength: 3
            },
            last_name: {
                validators: {
                    notEmpty: {
                        message: langMsg.last_name_is_required
                    }
                },
                required: true,
                minlength: 3
            },
            email: {
                validators: {
                    notEmpty: {
                        message: langMsg.email_address_is_required
                    },
                    emailAddress: {
                        message: langMsg.please_enter_valid_email_address
                    }
                }
            }
            // ,
            // contact_no: {
            //     validators: {
            //         notEmpty: {
            //             message: langMsg.contact_number_is_required
            //         },
            //         integer: {
            //             message: langMsg.please_enter_valid_contact_number
            //         }
            //     }
            // },
            // gender: {
            //     validators: {
            //         notEmpty: {
            //             message: langMsg.please_select_gender 
            //         }
            //     }
            // },
            // dob: {
            //     validators: {
            //         notEmpty: {
            //             message: langMsg.please_select_date_of_birth
            //         }
            //     }
            // }
        }
    }).on('success.form.bv', function(e) {
        e.preventDefault(); // Prevent submit form
        $('#confirm_pwd').modal('show');      
    });

    $("#change_password_form").bootstrapValidator({
        fields: {
            old_password: {
                validators: {
                    notEmpty: {
                        message: langMsg.old_password_is_required
                    }
                }
            },        
            password: {
                validators: {
                    notEmpty: {
                        message: langMsg.password_is_required
                    },
                    different: {
                        field: 'first_name,last_name',
                        message: langMsg.password_should_not_match_first_or_last_name
                    }
                }
            },
            password_confirm: {
                validators: {
                    notEmpty: {
                        message: langMsg.confirm_password_is_required
                    },
                    identical: {
                        field: 'password',
                        message: langMsg.password_and_confirm_password_should_be_same
                    },
                    different: {
                        field: 'first_name,last_name',
                        message: langMsg.password_should_not_match_first_or_last_name
                    }
                }
            }
        }
    }).on('success.form.bv', function(e) {
        
        e.preventDefault(); // Prevent submit form

        $('#loader_span').html('<img src="'+loader_url+'">');
        var data =  new FormData($("#change_password_form")[0]);
        callAjaxUpload(change_password_url, 'post', data, function(result){

            result = JSON.parse(result);
            if(result.status == 'success') {
                $('#content_div').html(result.msg);
            }
            else if(result.status == 'validate_error'){
                $('#loader_span').text('');
                $.each(result.msg, function(key,val){
                    $('#error_'+key).text(val);
                });
            }
        });        
    });

    $("#confirm_password_form").bootstrapValidator({
        fields: {        
            confirm_password: {
                validators: {
                    notEmpty: {
                        message: langMsg.password_is_required
                    }
                }
            }
        }
    }).on('success.form.bv', function(e) {
        
        e.preventDefault(); // Prevent submit form

        $('#confirm_loader_span').html('<img src="'+loader_url+'">');
        var data =  new FormData($("#confirm_password_form")[0]);
        callAjaxUpload(confirm_password_url, 'post', data, function(result){

            result = JSON.parse(result);
            if(result.status == 'success') {
                $('#edit_admin_form').unbind('submit').submit();
            }
            else if(result.status == 'validate_error'){
                $('#confirm_loader_span').text('');
                $.each(result.msg, function(key,val){
                    $('#error_'+key).text(val);
                });
            }
        });        
    });

    jQuery('input[name=product_permission_type]').click(function () {
        if (this.id == "roleproduct-list") {
            jQuery(".filter-table-container").show('fast');
        } else {
            jQuery(".filter-table-container").hide('fast');
        }
    });

    jQuery('input[name=customer_permission_type]').click(function () {
        if (this.id == "customer-list") {
            jQuery(".customer-table-container").show('fast');
        } else {
            jQuery(".customer-table-container").hide('fast');
        }
    });

    jQuery('input[name=order_permission_type]').click(function () {
        if (this.id == "order-list") {
            jQuery(".order-table-container").show('fast');
        } else {
            jQuery(".order-table-container").hide('fast');
        }
    });
}); 

$(".date-select").datepicker({
    format: 'dd-mm-yyyy',
    autoclose: true
}); 

$(function(){   

    var image_upload = $('#image_upload');
    var image_upload_status = $('#image_upload_status');
    var image_display = $('#image_display');

    new AjaxUpload(image_upload, {

        dataType: 'json',
        action: ajax_url,
        name: "uploadfile",
        data: {'upload_path':upload_path, 'width':150, 'height':150, '_token':window.Laravel.csrfToken},

        onSubmit: function(file, ext){
            if (!(ext && /^(jpg|png|jpeg|gif)$/.test(ext))){
                image_upload_status.text("Only jpg|png|jpeg|gif files are allowed");
                return false;
            }
            image_display.html('<img src="'+loader_url+'" width="150" height="150"/>');
        },
        onComplete: function(file, response){
            var json = JSON.parse(response);
            if(json.status == "success"){
                image_display.html('<img src="'+upload_url+json.file_name+'" width="150" height="150" /><input type="hidden" name="image" value="'+json.file_name+'" />');
            } else{
                image_upload_status.text(langMsg.unable_to_uload_file);
            }
        }
    });
}); 
