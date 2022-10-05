<?php 
/* This model will assist to track the activity logs of any loggedin users
 * Author: Dinesh Kumar Kovid | Date: 23/01/2017
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Logs extends Model
{
	 protected $table = 'logs';
}