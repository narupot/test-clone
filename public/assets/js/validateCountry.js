
$("#addCountryForm").bootstrapValidator({
    fields: {
        country_flag: {
            validators: {
                notEmpty: {
                    message: langMsg.please_select_image
                }
            }
        },        
        'country_name[]': {
            selector: '.country_nm',
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_country_name
                }
            }
        },
        'province_state_header[]': {
            selector: '.ps_header',
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_province_state_header
                }
            }
        }, 
        'city_district_header[]': {
            selector: '.cd_header',
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_city_district_header
                }
            }
        },
        'sub_district_header[]': {
            selector: '.sd_header',
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_sub_district_header
                }
            }
        },                
        country_code: {
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_country_code
                }
            }
        },
        short_code: {
            validators:  {
                notEmpty: {
                    message: langMsg.please_enter_country_short_code
                }
            }
        },
        country_isd: {
            validators:  {
                notEmpty: {
                    message: langMsg.please_select_isd_code
                },
                integer: {
                    message: langMsg.please_enter_valid_isd_code
                } 
            }
        }                
    }
});

$("#editCountryForm").bootstrapValidator({
    fields: {       
        'country_name[]': {
            selector: '.country_nm',
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_country_name
                }
            }
        },
        'province_state_header[]': {
            selector: '.ps_header',
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_province_state_header
                }
            }
        }, 
        'city_district_header[]': {
            selector: '.cd_header',
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_city_district_header
                }
            }
        },
        'sub_district_header[]': {
            selector: '.sd_header',
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_sub_district_header
                }
            }
        },                
        country_code: {
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_country_code
                }
            }
        },
        short_code: {
            validators:  {
                notEmpty: {
                    message: langMsg.please_enter_country_short_code
                }
            }
        },
        country_isd: {
            validators:  {
                notEmpty: {
                    message: langMsg.please_select_isd_code
                },
                integer: {
                    message: langMsg.please_enter_valid_isd_code
                } 
            }
        }               
    }
});


