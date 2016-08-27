<!DOCTYPE html>
<html>
<head>
    <title>Tweek</title>

    {{-- Mobile settings --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    {{-- Styles --}}
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,500" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css">

    {{-- Emoji support by Twitter --}}
    <script src="//twemoji.maxcdn.com/twemoji.min.js"></script>
    <script>
        window.onload = function () {
            // Set the size of the rendered Emojis: 16x16, 36x36, or 72x72
            twemoji.size = '72x72';
            // Parse the document body and insert <img> tags in place of Unicode Emojis
            twemoji.parse(document.body);
        }
    </script>
</head>
<body>
<nav>
    <ul>
        <p>Signed In as: {{'@'.Auth::user()->handle }}</p>
        <li><a href="">Settings</a></li>
        <li><a href="logout">Logout</a></li>
    </ul>
</nav>

<h1>tweek - a Custom Tweet feed</h1>

@if (session('alert'))
    <div class="flash">
        <p>{{ session('alert') }}</p>
    </div>
@endif
@if (session('alert-green'))
    <div class="flash green">
        <p>{{ session('alert-green') }}</p>
    </div>
@endif
@if (session('alert-red'))
    <div class="flash red">
        <p>{{ session('alert-red') }}</p>
    </div>
@endif

<div class="search">
    <h2>Start a list by searching for users:</h2>
    {!! Form::open(array('route' => 'add.sub')) !!}
    {{ csrf_field() }}
    {!! '@'.Form::text('sub_name') !!}
    {!! Form::button('Add', ['type' => 'submit']) !!}
    {!! Form::close() !!}
</div>

<div class="options">
    <ul>
        @if ($viewStyle == "s")
            <li><a class="buttonLink" href="{{ route('update.settings', 'unify') }}">Unify</a></li>
            <li><strong>Split</strong></li>
        @elseif ($viewStyle == "u")
            <li><strong>Unify</strong></li>
            <li><a class="buttonLink" href="{{ route('update.settings', 'split') }}">Split</a></li>
        @endif
    </ul>
</div>
<div class="tweets">
    @if ($viewStyle == "s")
        @foreach ($subs as $sub)
            <div class='tweetContainerS'>
                <div class="info">
                    <a class="buttonLink negate" href="{{ route('delete.sub', $sub) }}">Remove</a>
                    <h2>{{ $sub->name }}</h2>
                    <img src="{{ $sub->avatar }}">
                    <p class="tiny">Last
                        updated: {{ Carbon\Carbon::parse(Auth::user()->last_API_fetch)->toDayDateTimeString() }}</p>
                </div>
                <div class="feed">
                    @for ($i = 0; $i < sizeOf(json_decode($sub->timeline)); $i++)
                        <a class="tweet" target="_blank"
                           href="http://twitter.com/{{ Auth::user()->handle }}/status/{{ json_decode($sub->timeline)[$i]->id }}">
                            <div class="tweet">
                                <p class="text">{{ json_decode($sub->timeline)[$i]->text }}</p>
                                <p class="tiny">{{ date('H:i, M d', strtotime(json_decode($sub->timeline)[$i]->created_at)) }}</p>
                            </div>
                        </a>
                    @endfor
                </div>
            </div>
        @endforeach
    @else
        <div class='tweetContainerU'>
            @foreach ($utweets as $tweet)
                <div class="tweet">
                    <div class="imageDiv">
                        <img src="{{ App\Sub::find($tweet->user->id)->avatar }}">
                    </div>
                    <div class="contentDiv">
                        <a class="tweet" target="_blank" href="http://twitter.com/{{ Auth::user()->handle }}/status/{{ $tweet->id }}">
                            <div class="tweet">
                                <p class="text">{{ $tweet->text }}</p>
                                <p class="tiny">{{ date('g:i A, M d', strtotime($tweet->created_at)) }}</p>
                            </div>
                        </a>
                    </div>
                    <div class="clear"></div>
                </div>
            @endforeach
        </div>
    @endif
</div>
</body>
</html>
