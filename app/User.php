<?php

namespace App;

use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'access_token', 'handle', 'id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';
    protected $primaryKey = 'id';
    public $incrementing = false;

    /**
     * Establish a M-M relationship.
     *
     * @return add relationship
     */

    public function subs() {
        return $this->belongsToMany('App\Sub');
    }

    /**
     * Check to see if user can update; user must wait 1 minute between update
     *
     * @return add relationship
     */

    public function checkUpdate() {
        if (Carbon::now()->diffInMinutes(Carbon::parse(Auth::user()->last_API_fetch)) >= 1)
            return true;
        else
            return false;
    }

    /**
     * Update JSON of latest tweets for the subs
     *
     * @return add relationship
     */

    public function getUpdate() {

    }

    // /**
    //  * Create a new Twitter API connection
    //  *
    //  * @return API connection
    //  */
    //
    // public function makeAPIConnection() {
    //     define('CONSUMER_KEY', getenv('CONSUMER_KEY'));
    //     define('CONSUMER_SECRET', getenv('CONSUMER_SECRET'));
    //     define('OAUTH_CALLBACK', getenv('OAUTH_CALLBACK'));
    //     $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, session('access_token'), session('access_token_secret'));
    //     return $connection
    // }
}
