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
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function home()
    {
        $parse['subs'] = Auth::user()->subs;
        $parse['viewStyle'] = Auth::user()->viewStyle;

        // Update all tweets
        if (Auth::user()->checkUpdate()) {
            //define the Twitter API parameters, and establish connection
            define('CONSUMER_KEY', getenv('CONSUMER_KEY'));
            define('CONSUMER_SECRET', getenv('CONSUMER_SECRET'));
            define('OAUTH_CALLBACK', getenv('OAUTH_CALLBACK'));
            $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, session('access_token'), session('access_token_secret'));
            foreach ($parse['subs'] as $sub) {
                $tweetFeed = $connection->get("statuses/user_timeline", ['user_id' => $sub->id, 'exclude_replies' => 0, 'count' => 7]);
                $sub->timeline = json_encode($tweetFeed);
                $sub->save();
            }
            Auth::user()->last_API_fetch = Carbon::now();
            Auth::user()->save();
        }

        //organize array
        if ($parse['viewStyle'] == 'u') {
            $parse['utweets'] = [];
            foreach ($parse['subs'] as $sub) {
                for ($i = 0; $i < sizeOf(json_decode($sub->timeline)); $i++) {
                    array_push($parse['utweets'], json_decode($sub->timeline)[$i]);
                }
            }
            usort($parse['utweets'], function ($item1, $item2) {
                if ($item1->id == $item2->id) {
                    return 0;
                }
                return (strtotime($item1->created_at) < strtotime($item2->created_at)) ? 1 : -1;
            });
        }

        return view('home', $parse);
    }

    public function updateSettings($option)
    {
        if ($option == 'unify') {
            Auth::user()->viewStyle = 'u';
        } elseif ($option == 'split') {
            Auth::user()->viewStyle = 's';
        } else {
            Auth::user()->viewStyle = 's';
        }
        Auth::user()->save();

        return redirect()->route('app.home');
    }
}
