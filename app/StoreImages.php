<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use Collective\Html\Eloquent\FormAccessible;
use Auth;
use Cache;
use App\Notifications\ResetPassword;
use DB;

class StoreImages extends Authenticatable {
    protected $table = 'store_images';

}
