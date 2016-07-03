<?php

namespace App\Http\Controllers;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Requests;
use App\Sub;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use View;

class UserController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function home() {
        $parse['subs'] = Auth::user()->subs;

        // Update all tweets
        if (Auth::user()->checkUpdate()) {
            //define the Twitter API parameters, and establish connection
            define('CONSUMER_KEY', getenv('CONSUMER_KEY'));
            define('CONSUMER_SECRET', getenv('CONSUMER_SECRET'));
            define('OAUTH_CALLBACK', getenv('OAUTH_CALLBACK'));
            $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, session('access_token'), session('access_token_secret'));
            foreach ($parse['subs'] as $sub) {
                $tweetFeed = $connection->get("statuses/user_timeline", ['user_id' => $sub->id, 'exclude_replies' => 0, 'count' => 5]);
                $sub->timeline = json_encode($tweetFeed);
                $sub->save();
            }
            Auth::user()->last_API_fetch = Carbon::now();
            Auth::user()->save();
        }

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
