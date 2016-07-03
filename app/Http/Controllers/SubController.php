<?php

namespace App\Http\Controllers;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;
use App\Sub;
use Auth;
use Carbon\Carbon;

class SubController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function store(Request $request) {
        //update database and link logged in user to sub
        $newSub = new Sub;

        //convert handle to id
        define('CONSUMER_KEY', getenv('CONSUMER_KEY'));
        define('CONSUMER_SECRET', getenv('CONSUMER_SECRET'));
        define('OAUTH_CALLBACK', getenv('OAUTH_CALLBACK'));
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, session('access_token'), session('access_token_secret'));
        $content = $connection->get("users/lookup", ["screen_name" => "$request->sub_name"]);

        //check to see if handle exists, and then save, else show error
        if (!array_key_exists('errors', $content)) {
            //check to see if sub already exists
            if (!Auth::user()->subs()->find($content[0]->id) && (Auth::user()->id != $content[0]->id)) {
                    //check to see if sub already in database
                    if (Sub::find($content[0]->id)) {
                        $newSub = Sub::find($content[0]->id);
                    }
                    else {
                        $newSub->id = $content[0]->id;
                        $newSub->name = $content[0]->name;
                        //parse the avatar URL and remove "_normal" to get link to higher res picture
                        $pic = $content[0]->profile_image_url;
                        $pic = str_replace("_normal", "", $pic);
                        $newSub->avatar = $pic;
                    }

                    //update tweets of sub
                    $tweetFeed = $connection->get("statuses/user_timeline", ['user_id' => $content[0]->id, 'exclude_replies' => 0, 'count' => 5]);
                    $newSub->timeline = json_encode($tweetFeed);
                    Auth::user()->last_API_fetch = Carbon::now();

                    //save everything
                    $newSub->save();
                    Auth::user()->subs()->attach($newSub->id);
                    Auth::user()->save();
                    session()->flash('alert-green', $content[0]->name.' has been added!');
            }
            else if (Auth::user()->id == $content[0]->id) {
                session()->flash('alert-red', 'Nice try adding yourself.');
            }
            else {
                session()->flash('alert', $request->sub_name.' is already added.');
            }
        }
        else {
            session()->flash('alert-red', $request->sub_name.' could not be added.');
        }

        return redirect()->route('app.home');
    }

    public function delete($subid) {
        $deleteSub = Sub::find($subid);
        $name = $deleteSub->name;
        $deleteSub->delete();
        return redirect()->route('app.home')->with('alert', $name.' removed.');
    }
}
