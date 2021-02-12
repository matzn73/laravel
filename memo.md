# tecpit laravel+vueでSNS作ろう講座のメモ

## laradockをインストールする
```
git clone https://github.com/shonansurvivors/laradock-like.git laradock
```
`result: Unpacking objects:100% (44/44), done.`

```
cp env-example .env
```
`env-exampleを生成した`

<br>

## .envファイルの編集
  
```
vi .env
```
`環境変数をCOMPOSE_PROJECT_NAME=laravel-sns　に変更。そのほかはデフォルト`

<br>

## Dockerを使って開発環境を起動する
```
docker-compose up -d workspace php-fpm nginx postgres
```
`result: command not found: docker-compose`


#### docker desctop for mac をインストールしたらコマンドが実行できた。

>Dockerを使用するためには、面倒臭いコマンドをたくさん打ち込まなきゃいけないのかと思ったが、一旦これをインストールしちゃえば使えるらしい？とりあえずやっていきながら、どんな挙動をするのかを探っていこう


  以下のコマンドでコンテナを停止できる
```
docker-compose stop
```

<br>

## Laravelのインストール

```
docker-compose exec workspace composer create-project --prefer-dist laravel/laravel . "6.8.*"
```
>docker composeから始まるコマンドを実行する時は、必ず「laradockディレクトリ」で実行する。



+ laravelディレクトリが生成され、ディレクトリがたくさんできる

+ localhostにアクセスすると、ウェルカムページが表示される

+ タイムゾーンを``'Asia/Tokyo'``に変更する。

<br>

## サイト設計
1. usersテーブル
   + usersテーブルは最初から用意されている。

   + passwordにnull許容を付けて、パスワードが空の状態を認めています。
    >これは、Googleアカウントを使ったユーザー登録の場合、本教材のWebサービスではパスワードを設定不要にしているためです。

2. articlesテーブル
   + user_idに紐づいている

3. likesテーブル
     + usersテーブルとarticlesテーブルを紐付ける中間テーブルとなります。
     + user_id,article_idを持つ

4. tagsテーブル
    + タグ名を管理
    + 同じタグが重複することの無いよう、nameにはユニーク制約

5. article_tagテーブル
    + 「どの記事に」「何のタグが」付いているかを管理
    + article,tagの中間テーブル。すなわちarticle_id,tags_idを持つ

6. followsテーブル
   + usersテーブルとusersテーブルを紐付ける中間テーブル
   + usersテーブルを参照した、forower_id,forowee_idを持つ。

>ここにきてDBの構造がなんとなく理解できてきたかも。

>やっぱりどのフレームワークでもこの辺は同じなんだな。

## ルーティングの定義
`Route::リクエスト名('url','コントローラー名@アクション名')`

```
ex) Route::get('/','ArticleController@index')
```

```
rubyだと) get '/' => 'ArticleController#index'
```