@extends('app') <!-- app.blade.phpをベースで使用する-->

@section('title', '記事一覧') <!-- app.blade.phpのタイトルに入る内容-->

@section('content') <!-- app.blade.phpのbodyに入っているyeildの中に入れる-->
  @include('nav')
    <div class="container">
      @foreach($articles as $article) <!--eachで回す-->
        @include('articles.card')
      @endforeach {{--eachのend--}}
    </div>
@endsection