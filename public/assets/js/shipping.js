jQuery(document).ready(function (e) {


    /**************************************/

    if (jQuery('form#shippingProfile').length > 0) {

        jQuery("form#shippingProfile").validate({

            rules: {

                name: {
                    required: true
                },
                comment: {
                    required: true,
                    minlength: 5,
                    maxlength: 500
                }
                /* agree1: "required"*/
            },
            messages: {

                name: {
                    required: "Please enter shipping name."
                },

                comment: {
                    required: "Please enter comment.",
                    minlength: "Your comment must be at least 50 characters long.",
                    maxlength: "Your description must be at max 500 characters long."
                }


                /* agree1: "Please accept our policy."*/
            },
            errorElement: "p",
            errorPlacement: function (error, element) {

                error.addClass("error-msg");
                //element.parents(".col-sm-5").addClass("has-feedback");

                if (element.prop("type") === "checkbox") {
                    error.insertAfter(element.parent("label"));
                } else if (element.is("textarea")) {
                    error.insertAfter(element.next());
                } else {
                    error.insertAfter(element);
                }


            },

        });
    }


    if (jQuery('form#shippingForm').length > 0) {

        jQuery("form#shippingForm").validate({

            rules: {

                country: {
                    required: true
                },
                state: {
                    required: true
                },

                zipFrom: {
                    required: true,
                },
                zipTo: {
                    required: true
                },
                days: {
                    required: true,
                    digits: true
                },
                baseRate: {
                    required: true,
                    number: true
                },
                percentage_per_product: {
                    required: true,
                    number: true
                },
                fixed_rate_per_product: {
                    required: true,
                    number: true
                },
                fixed_rate_per_weight: {
                    required: true,
                    number: true
                }

                /* agree1: "required"*/
            },
            messages: {

                country: {
                    required: "Please select country."
                },

                state: {
                    required: "Please select state."
                },
                zipFrom: {
                    required: "Please enter zip from."

                },
                zipTo: {
                    required: "Please enter zip to."

                },
                days: {
                    required: "Please enter delivery days."

                },
                baseRate: {
                    required: "Please enter base rate."

                },
                percentage_per_product: {
                    required: "Please enter percentage per product ."

                },
                fixed_rate_per_product: {
                    required: "Please enter fixed rate per product."

                },
                fixed_rate_per_weight: {
                    required: "Please enter fixed rate per weight."

                }

                /* agree1: "Please accept our policy."*/
            },
            errorElement: "p",
            errorPlacement: function (error, element) {

                error.addClass("error-msg");
                //element.parents(".col-sm-5").addClass("has-feedback");

                if (element.prop("type") === "checkbox") {
                    error.insertAfter(element.parent("label"));
                } else if (element.is("textarea")) {
                    error.insertAfter(element.next());
                } else {
                    error.insertAfter(element);
                }


            },

        });
    }



})