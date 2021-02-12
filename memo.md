# tecpit laravel+vueでSNS作ろう講座のメモ

## laradockをインストールする
```
git clone https://github.com/shonansurvivors/laradock-like.git laradock
```
rslt: `Unpacking objects:100% (44/44), done.`

```
cp env-example .env
```
rslt: `env-exampleを生成した`

<br>

## .envファイルの編集
  
```
vi .env
```
rslt: `環境変数をCOMPOSE_PROJECT_NAME=laravel-sns に変更。そのほかはデフォルト`

<br>

## Dockerを使って開発環境を起動する
```
docker-compose up -d workspace php-fpm nginx postgres
```
rslt: `command not found: docker-compose`


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

+ このタイミングでGit Hubにプッシュした

<br>

## コマンドでのコントローラーの新規作成

`laradock`に移動して
```
docker-compose exec workspace php artisan make:controller ArticleController
```
rslt: `コントローラーができる`

+ laravelの元々のコントローラー作成コマンドは`php artisan make:`
+ `docker-compose exec workspace php`で起動中のworkspaceという名前のDockerコンテナ(仮想環境)の中で、続くコマンド(php artisan...)を実行する

+ 型キャスト
  >配列の手前に(object)と記述することで、配列がオブジェクト型に変換されています。

+ viewを返す
  
  ```
  return view('articles.index', ['articles' => $articles]);
  ```
  > railsの場合はデフォルトでコントローラー名/メソッド名のviewファイルを開くが、laravelの場合は`return view`でviewを返さなければいけない。

### viewメソッドの補足
 + withメソッドを使った書き方
  ```
  return view('articles.index')->with(['articles' => $articles]);
  ```
 + compact関数を使う書き方
  ```
  return view('articles.index', compact('articles'));
  ```
  > conpact関数を使うと、連想配列で変数を定義しなくても大丈夫

+ MDBootstrapの使い方はBootstrap4とほぼ同じだが、アイコンが使える。

## @extendsと@section

+ @extends('app')でapp.blade.phpをベースとして使う。

+ @section('title', '記事一覧')は、app.blade.phpの@yield('title')に対応します。

## エラー：Class 'App\Http\Controllers\Controller ' not found

 viewにテンプレートを置いてlocalhostにアクセスしたらこのエラーが出た。
  
  + 記述ミスなし
```
class ArticleController extends Controller //Controllerを継承している
```
原因: `全角スペースを含めていたため、not foundになってしまった。エラーが出た際は必ず調べる`

  >拡張機能のzenkakuが有効になっているので本来なら全角スペースがわかるようになっているのだが、なぜか無効になっていた。再起動することで動くようになった。

対策：`zenkakuを常にオンにする`

ホームディレクトリ からviで以下のコマンドを開く
```
.vscode/extensions/mosapride.zenkaku-◯.◯.◯/extension.js
```

`var enable = false`を`true`に変更
#### これで常にzenkakuが有効になる。

## formatメソッド
>formatメソッドは、Laravelの日付時刻クラスであるCarbonで使えるメソッドです。<br>
引数には、日付時刻表示のフォーマット(形式)を渡します。<br>
'Y/m/d H:i'とすれば、2020/02/01 12:00といった表示になります。<br>
'Y年m月d日 H時i分'とすれば、2020年02月01日 12時00分といった表示になります。

### 全体的な流れ

ルーティング設定⇨ビューの雛形をある程度作っちゃう⇨モデル⇨コントローラー

## データベースの作成
```
docker-compose exec workspace psql -U default -h postgres
```
>psqlは、PostgreSQLをコマンドで操作するためのツールです。

パスワードを`secret`で通過した後に
```
default=# create database larasns;
```
`\q`で抜けられる

## Laravelからデータベースへ接続できるようにする

`laravel`ディレクトリの`.env`の環境変数の値を変更する。
+ 変更後
  ```
  DB_CONNECTION=pgsql
  DB_HOST=postgres
  DB_PORT=5432
  DB_DATABASE=larasns
  DB_USERNAME=default
  DB_PASSWORD=secret
  ```

  ## 記事テーブルの作成
  Laravelを使ってデータベースにテーブルを作成するには、まずマイグレーションファイルを作成する必要があります。

  laradockディレクトリで以下コマンドを実行してください。
  ```
  $ docker-compose exec workspace php artisan make:migration create_articles_table --create=articles
  ```
  >laravelディレクトリにmigrationファイルが生成される

外部キー制約
```
$table->foreign('user_id')->references('id')->on('users');`
```
>上記は、articlesテーブルのuser_idカラムは、usersテーブルのidカラムを参照すること

nullable
>nullableメソッドを使うことで、そのカラムにnullが入ることを許容します。

マイグレーションの実行
```
docker-compose exec workspace php artisan migrate
```

マイグレーションのロールバックについて
>なお、もしマイグレーションファイルの内容を誤ったままマイグレーションを実行してしまった場合は、ロールバックしてください。

>Laravelのマイグレーションでのロールバックは、`php artisan migrate:rollback`というコマンドで行うことができます。
```
$ docker-compose exec workspace php artisan migrate:rollback
```

## 記事モデルの作成
```
docker-compose exec workspace php artisan make:model Article
```
>コマンドが成功すると、laravel/appディレクトリにArticle.phpが作成されます。

