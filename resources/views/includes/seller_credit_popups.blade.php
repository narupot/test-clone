<!--  Login register popup -->
<div id="editNickName" class="modal fade" role="dialog" >
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="mb-0">@lang('shop.update_dignated_name')</h1>                    
                <span class="close fas fa-times" data-dismiss="modal"></span>
            </div>
            <div class="modal-body">
                <form name="updateNickName" id="updateNickName" action="{{action('Seller\CreditController@editBuyerNickName')}}" method="POST">
                     {{ csrf_field() }}
                    <div class="reg-customer-form">
                        <div class="form-group">
                            <label>@lang('shop.designated_name')</label>
                            <input type="hidden" name="id" value="" id="hidden_id">
                            <input type="text" name="user_designated_name">
                        </div>
                        <div class="form-group">
                            <button class="btn update_nickname" type="button">@lang('common.btn_update')</button>
                            <!-- <input type="submit" class="btn" value="Give Credit"> -->
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="giveCredit" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header border-bottom">
            <h1 class="mb-0">@lang('shop.give_credit')</h1>                    
            <span class="close fas fa-times" data-dismiss="modal"></span>
        </div>
        <div class="modal-body">        
            <form name="giveCredits" id="giveCredits" action="{{action('Seller\CreditController@giveCredit')}}" method="POST">
                {{ csrf_field() }}
                <input type="hidden" name="id" value="" id="id">
                <input type="hidden" name="customer_name" id="cust_name" value="">
                <input type="hidden" name="customer_email" id="cust_email" value="">
                <div class="reg-customer-form">
                    <div class="form-group">
                        <div class="user-block">
                            <div class="user-img">
                                <a href="#">
                                    <img src="images/login/user.jpg" width="50" alt="" id="cust_image">
                                </a>
                            </div>
                            <div class="user-body">
                                <div class="customer-name" id="customer-name"></div>                          
                            </div>
                        </div>
                    </div>                  

                    <div class="form-group">
                        <label>@lang('shop.payment_method')</label>

                        <select name="payment_period" id="payment_period">
                        </select>
                    </div>
                    <div class="form-group">
                        <label>@lang('shop.credit_limit')</label>
                        <input type="number" name="credited_amount" id="credited_amount">
                    </div>
                    <div class="form-group">
                        <button class="btn" type="button" id="give_credit">@lang('shop.give_credit')</button>
                        <!-- <input type="submit" class="btn" value="Give Credit"> -->
                    </div>
                </div>
            </form>       
        </div>
        </div>
    </div>
</div>