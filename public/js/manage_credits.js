$(document).ready(function () {
        $(document).on("show.bs.modal",'#editNickName', function(e){
            $("#hidden_id").val($(e.relatedTarget).data('id'));
        });

         $(document).on("show.bs.modal",'#giveCredit,#edit_credit', function(e){
            $("#payment_period").html($(e.relatedTarget).data('select_options'));
            $("#customer-name").html($(e.relatedTarget).data('customer_name'));
            $("#cust_name").val($(e.relatedTarget).data('customer_name'));
            $("#cust_email").val($(e.relatedTarget).data('customer_email'));
            $("#id").val($(e.relatedTarget).data('id'));
            $("#credited_amount").val($(e.relatedTarget).data('credited_amount'));
            $("#cust_image").attr('src',$(e.relatedTarget).data('image'));
        });

        $(document).on("click",".update_nickname",function(){
            var formAction = $("#updateNickName").attr('action');
            var formMethod = $("#updateNickName").attr('method');
            var form_data = new FormData($("#updateNickName")[0]);
            
            callAjaxFormRequest(formAction,formMethod,form_data,function(result){
                if(result.status=='fail'){
                    showSweetAlertError(result.message);

                }else if(result.status=='success'){
                    swal({
                        type: resp.status, 
                        title: text_success, 
                        text: result.message,
                        confirmButtonText : text_ok_btn,
                    }).then(function(){
                        location.reload();
                    });       
                }
            });
        });

        $(document).on("click","#give_credit, #edit_credit",function(){
            var formAction = $("#giveCredits").attr('action');
            var formMethod = $("#giveCredits").attr('method');
            var form_data = new FormData($("#giveCredits")[0]);

            var payment_period = $("#payment_period").val();
            var credited_amount = $("#credited_amount").val();
            var cust_name = $("#cust_name").val();
            var cust_email = $("#cust_email").val();
            var cust_image = $("#cust_image").attr('src');
            //var html_cont = '<div class="text-left"><div class="form-group">Now you give credit for</div><div class="form-group"><div class="user-block"><div class="user-img"><a href="#"><img src="'+cust_image+'" width="50" alt=""></a></div><div class="user-body"><div class="customer-name">'+cust_email+'</div><div class="customer-name">'+cust_name+'</div></div></div></div><div class="row"><label class="col-sm-3 form-group">Amount</label><div class="col form-group">'+credited_amount+' Bhat</div></div><div class="row"><label class="col-sm-5">Time to pay credit</label><div class="col">'+payment_period+' Days</div></div></div>';
            
            callAjaxFormRequest(formAction,formMethod,form_data,function(result){
                if(result.status=='error'){
                    showSweetAlertError(result.message);

                }else if(result.status=='success'){
                    swal({
                        type: result.status, 
                        title: text_success, 
                        text: result.message,
                        confirmButtonText : text_ok_btn,
                    }).then(function(){
                        location.reload();
                    });       
                }
            });
            // swal({
            //     html:html_cont,
            //     showCancelButton: true,
            //     confirmButtonColor: '#d33',
            //     cancelButtonColor: '#D9D9D9',
            //     confirmButtonText: 'Submit!'
            //     }).then((result) => {
            //         if(result) {
            //             callAjaxFormRequest(formAction,formMethod,form_data,function(result){
            //                 if(result.status=='error'){
            //                     showSweetAlertError(result.message);

            //                 }else if(result.status=='success'){
            //                     swal(result.status, result.message, "success").then(function(){
            //                         location.reload();
            //                     });       
            //                 }
            //             });
            //         }
            //     });
        });

        $(document).on('click','.reject_credit', function(){
            var id = $(this).attr('id');
            var data = {'id':id,'action':'reject_credit'};
            swal({
                title: are_you_sure,
                type:'warning',
                text:text_reject_message,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: txt_no,
                confirmButtonText: text_yes_reject_it
                }).then((result) => {
                    if(result) {
                        callAjaxRequest(handle_credit_ajax_request_url,'POST',data,function(result){
                            if(result.status=='error'){
                                showSweetAlertError(result.message);

                            }else if(result.status=='success'){
                                swal({
                                    type: result.status, 
                                    title: text_success, 
                                    text: result.message,
                                    confirmButtonText : text_ok_btn,
                                }).then(function(){
                                    location.reload();
                                });       
                            }
                        });
                    }
                });
        });

        $(document).on('click','.remove_credit', function(){
            var id = $(this).attr('id');
            var data = {'id':id,'action':'remove_credit'};
            //console.log(user_id);
            callAjaxRequest(check_credit_remove_url,'POST',data,function(result){
                if(result.status=='remove'){
                    swal({
                        title:result.title,
                        type:'warning',
                        text:result.message,
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        cancelButtonText: txt_no,
                        confirmButtonText: text_yes_remove_it
                        }).then((result) => {
                            if(result) {
                                callAjaxRequest(handle_credit_ajax_request_url,'POST',data,function(response){
                                    if(response.status=='error'){
                                        showSweetAlertError(response.message);
                                    }else if(response.status=='success'){
                                        swal({
                                            type: response.status, 
                                            title: text_success, 
                                            text: response.message,
                                            confirmButtonText : text_ok_btn,
                                        }).then(function(){
                                            location.reload();
                                        });     
                                    }
                                });
                            }
                        });       
                }else if(result.status=='overdue'){
                    showSweetAlertError(result.message);
                }else{
                    showSweetAlertError(result.message);
                }
            });
        });

        $(document).on("change",'.credit_paid', function(){
            var order_id = ($(this).attr('data-order_id')!==undefined)?$(this).attr('data-order_id'):'';
            var data = {"user_id":user_id,"order_id":order_id};
            swal({
                title: are_you_sure,
                type:'warning',
                text: text_confirm_paid_message,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: txt_no,
                confirmButtonText: text_yes_paid_it
                }).then((result) => {
                    if(result) {
                        callAjaxRequest(credit_paid_url,'POST',data,function(result){
                            if(result.status=='error'){
                                swal({
                                    type: result.status, 
                                    title: text_error, 
                                    text: result.message,
                                    confirmButtonText : text_ok_btn,
                                }).then(function(){
                                    location.reload();
                                });
                            }else if(result.status=='success'){
                                swal({
                                    type: result.status, 
                                    title: text_success, 
                                    text: result.message,
                                    confirmButtonText : text_ok_btn,
                                }).then(function(){
                                    location.reload();
                                });      
                            }
                        });
                    }
                });
        });
    });