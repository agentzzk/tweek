<?php

namespace App\Http\Controllers\Auth;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\User;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Socialite;
use Validator;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    public function userLogin() {
        //define the Twitter API parameters
        define('CONSUMER_KEY', getenv('CONSUMER_KEY'));
        define('CONSUMER_SECRET', getenv('CONSUMER_SECRET'));
        define('OAUTH_CALLBACK', getenv('OAUTH_CALLBACK'));

        //setup a connection with TwitterOAuth
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
        //create a request_token request with TwitterOAuth, and define the callback URL - POST oauth/request_token
        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => OAUTH_CALLBACK));
        //request_token is only valid for a couple seconds, so save the important stuff
        session()->put('oauth_token', $request_token['oauth_token']);
        session()->put('oauth_token_secret', $request_token['oauth_token_secret']);
        session()->save();

        //once the request token for oauth is saved, make a URL and ask user to login to the app session
        $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
        return redirect($url);
    }

    public function redirectAfterLogin(Request $request) {
        //define the Twitter API parameters
        define('CONSUMER_KEY', getenv('CONSUMER_KEY'));
        define('CONSUMER_SECRET', getenv('CONSUMER_SECRET'));
        define('OAUTH_CALLBACK', getenv('OAUTH_CALLBACK'));

        //retrieve the oauth verifier from URL that Twitter sends
        session()->put('oauth_verifier', $request->only('oauth_verifier')['oauth_verifier']);
        session()->save();

        //setup a connection with TwitterOAuth
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, session('oauth_token'), session('oauth_token_secret'));
        //exchange the request token for a user access token, and pass in the verifier that Twitter wants - POST oauth/access_token
        $access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => session('oauth_verifier')]);
        //save the access token
        session()->put('access_token', $access_token['oauth_token']);
        session()->put('access_token_secret', $access_token['oauth_token_secret']);
        session()->put('id', $access_token['user_id']);
        session()->put('handle', $access_token['screen_name']);

        //dd(session()->all());

        return redirect()->route('auth');
    }

    /**
     * Obtain the user information from Twitter.
     *
     * @return Response
     */
    public function authUser()
    {
        $authUser = $this->findOrCreateUser();
        Auth::login($authUser, true);
        return redirect()->route('app.home');
    }

    /**
     * Return user if exists; create and return if doesn't
     *
     * @param $twitterUser
     * @return User
     */
    private function findOrCreateUser()
    {
        $authUser = User::where(['id' => session('id')])->first();

        if ($authUser){
            return $authUser;
        }

        return User::create(['id' => session('id'),
                            'handle' => session('handle'),
                            'access_token' => session('access_token')]);
    }
}
