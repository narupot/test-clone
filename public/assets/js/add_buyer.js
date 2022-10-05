jQuery('input[name="loginuse"]').click(function(e){
    var loginval = jQuery(this).val();
    showloginuse(loginval);
});

if ($('input[name="loginuse"]').length > 0){
    var loginuseval = jQuery('input[name="loginuse"]:checked').val();
    showloginuse(loginuseval);
}

function showloginuse(loginval){
    if(loginval=='email'){
        jQuery('#emaildiv').show();
        jQuery('#ph_numberdiv').hide();
    }else{
        jQuery('#ph_numberdiv').show();
        jQuery('#emaildiv').hide();
    }
}

jQuery('body').on('click','input[name="find_by_use"]',function(e){
    
    var val = jQuery(this).val();
    if(val == 'email'){
        jQuery('#find_by_ph_no').hide();
        jQuery('#find_by_email').show();
    }else{
        jQuery('#find_by_ph_no').show();
        jQuery('#find_by_email').hide();
    }
});


jQuery(document).ready(function(){

    $("#add_buyer_form").bootstrapValidator({
         
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

    $("#change_password_form").bootstrapValidator({
        fields: {       
            password: {
                validators: {
                    notEmpty: {
                        message: langMsg.password_is_required
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
}); 

