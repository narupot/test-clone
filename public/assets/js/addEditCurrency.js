
$("#addCurrencyForm").bootstrapValidator({
    fields: {
        currency_name: {
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_currency_name
                }
            }
        },        
        currency_code: {
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_currency_code
                }
            }
        },
        currency_symbol: {
            validators:  {
                notEmpty: {
                    message: langMsg.please_enter_currency_symbol
                }
            }
        },
        currency_image: {
            validators: {
                notEmpty: {
                    message: langMsg.please_select_image
                }
            }
        }
    }
});

$("#editCurrencyForm").bootstrapValidator({
    fields: {
        currency_name: {
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_currency_name
                }
            }
        },        
        currency_code: {
            validators: {
                notEmpty: {
                    message: langMsg.please_enter_currency_code
                }
            }
        },
        currency_symbol: {
            validators:  {
                notEmpty: {
                    message: langMsg.please_enter_currency_symbol
                }
            }
        }
    }
});

