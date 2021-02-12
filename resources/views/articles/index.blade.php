@extends('app') <!-- app.blade.phpをベースで使用する-->

@section('title', '記事一覧') <!-- app.blade.phpのタイトルに入る内容-->

@section('content') <!-- app.blade.phpのbodyに入っているyeildの中に入れる-->
  @include('nav')
    <div class="container">
      @foreach($articles as $article) <!--eachで回す-->
        <div class="card mt-3">
          <div class="card-body d-flex flex-row">
            <i class="fas fa-user-circle fa-3x mr-1"></i>
            <div>
              <div class="font-weight-bold">
                {{ $article->user->name }} <!--記事を投稿したユーザー名-->
              </div> 
              <div class="font-weight-lighter">
                {{ $article->created_at->format('Y/m/d H:i') }} <!--投稿日時-->
              </div>
            </div>
          </div>
          <div class="card-body pt-0 pb-2">
            <h3 class="h4 card-title">
              {{ $article->title }} {{--タイトル--}}
            </h3>
            <div class="card-text">
              {!! nl2br(e( $article->body )) !!} {{--内容--}}
            </div>
          </div>
        </div>
      @endforeach {{--eachのend--}}
    </div>
@endsection