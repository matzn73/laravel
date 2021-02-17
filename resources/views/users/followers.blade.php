@extends('app')

@section('title', $user->name . 'のフォロー中')

@section('content')
  @include('nav')
  <div class="container">
    @include('users.user')
    @include('users.tabs', ['hasArticles' => false, 'hasLikes' => false]) {{--両方ともfalseのため記事タブといいねタブは非表示--}}
    @foreach($followers as $person)
      @include('users.person')
    @endforeach
  </div>
@endsection