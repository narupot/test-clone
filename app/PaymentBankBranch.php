<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentBankBranch extends Model
{
    protected $table = 'payment_bank_branch';

    function branchName() {
        
        return $this->hasOne('App\PaymentBankBranchDesc', 'bank_branch_id', 'id')->select('branch_name','lang_id','bank_branch_id')->where('lang_id', session('default_lang'));
    }

}
