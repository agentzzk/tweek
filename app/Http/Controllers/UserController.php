<?php

namespace App\Http\Controllers;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Requests;
use App\User;
use App\Sub;
use Auth;
use Illuminate\Http\Request;
use View;

class UserController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function home() {
        $parse['subs'] = Auth::user()->subs;
        return view('home', $parse);
    }

    public function testing() {
        //define the Twitter API parameters, and establish connection
        define('CONSUMER_KEY', getenv('CONSUMER_KEY'));
        define('CONSUMER_SECRET', getenv('CONSUMER_SECRET'));
        define('OAUTH_CALLBACK', getenv('OAUTH_CALLBACK'));
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, session('access_token'), session('access_token_secret'));
        $content = $connection->get("statuses/user_timeline");
        dd($content);
    }
}
