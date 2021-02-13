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

## モデルの作成

```
docker-compose exec workspace php artisan make:model Article
```
- コマンドが成功すると、laravel/appディレクトリにArticle.phpが作成されます。


Eloquent
- Eloquent、と出てきたらモデルのことだと思ってください。

`Eloquent ORM(Eloquent Object Relational Mapping)`

$this
- $thisは、Articleクラスのインスタンス自身を指しています。

- `$this->メソッド名()`とすることで、インスタンスが持つメソッドが実行され、`$this->プロパティ名`とすることで、インスタンスが持つプロパティを参照します。

リレーション
- 記事と、記事を書いたユーザーは多対1の関係ですが、そのような関係性の場合には、`belongsTo`メソッドを使います。それ以外の関係性の場合は、それぞれ以下のメソッドを使います。
- 1対1の関係は、`hasOne`メソッド
- 1対多の関係は、`hasMany`メソッド
- 多対多の関係は、`belongsToMany`メソッド

型宣言
 >関数のパラメータや戻り値、 クラスのプロパティ (PHP 7.4.0 以降) に対して型を宣言することができます。 これによって、その値が特定の型であることを保証できます。 その型でない場合は、TypeError がスローされます。

外部キー名の省略
- 以下のコードではbelongsToメソッドにuser_idやidといったカラム名が一切渡されていないのに、リレーションが成り立っています。
```
return $this->belongsTo('App\User');
```
- これは、usersテーブルの主キーはid、articlesテーブルの外部キーは関連するテーブル名の単数形_id(つまりuser_id)であるという前提のもと、Laravel側で処理をしているためです。
- 上記のようなネーミングルールになっていない場合は、belongsToメソッドに追加で引数を渡す必要があります

リレーションの使い方の注意点
```
$article->user(); ×
```

```
$article->user; ○
```
- user()がリレーションメソッドであるのに対し、()無しのuserは動的プロパティと呼ばれます。

```
$article->user;         //-- Userモデルのインスタンスが返る
$article->user->name;   //-- Userモデルのインスタンスのnameプロパティの値が返る
$article->user->hoge(); //-- Userモデルのインスタンスのhogeメソッドの戻り値が返る
$article->user();       //-- BelongsToクラスのインスタンスが返る
```

## モデルから記事情報を取得する

コントローラーを編集する
```
$articles = Article::all()->sortByDesc('created_at');
```
- allメソッドは、モデルが持つクラスメソッドです。 [reference](https://readouble.com/laravel/6.x/ja/eloquent.html#retrieving-models)

コレクション

- コレクションはPHPの配列を拡張したもので、Laravelに用意されたクラスです。

- コレクションは配列と同じように扱うことができますが、配列には無い、便利な様々なメソッドを使うことができます。[使えるメソッド一覧](https://readouble.com/laravel/6.x/ja/collections.html)

認証関連のルーティング

- Laravelでは認証関連のルーティングのひな形を用意してくれています。
  
```
Auth::routes(); 
```

>ユーザー認証に関連するメソッドが生成される。railsのresoucesとdeviseを足して2で割った感じ？

トレイト
```
trait RegistersUsers
```

- 上記のようにtraitと宣言されているものがトレイトです。
- トレイトは、そのままではクラスとして使用できず、
  
```
class RegisterController extends Controller
{
    use RegistersUsers;
} 
```

- のように、他のクラスの中でuse トレイト名と記述します。
- 汎用性の高い機能をトレイトとしてまとめておき、他の複数のクラスで共通して使う、といった使い方をします。
>railsのHelperメソッドとかそんなイメージ？

- PHPでは、ひとつのクラスが別のクラスを2つ以上継承することはできません。
- 一方、トレイトはいくつでも同時に使用(use)できます。

三項演算子
- 三項演算子は、式1 ? 式2 : 式3という形式で記述し、`式1がtrueの場合は、式2が値となる``式1がfalseの場合は、式3が値となる`

redirect関数とredirectPathメソッド
- redirect関数は、引数として与えられたURLへクライアントをリダイレクトさせます。
  
method_exists関数
- 第一引数にクラス、第二引数にメソッド名を受け取り、第一引数のクラスに第二引数のメソッドが存在するかどうかをtrueかfalseで返します。

早期return
- returnしているので、if文を抜けた以降にコードがあったとしても、redirectPathメソッドとしての処理はそこで終了します。

```
return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home'
```

>$thisにredirectToがあるか確認して、あれば $his->redirectToを返す

修正して良いコードとそうでないコード

- vendorディレクトリはcomposer installコマンドによってインストールされたPHPの各種ライブラリのコードが配置される箇所ですが、通常はここにあるコードを直接修正することはしません。

バリデーションの確認

```
protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }
```

>Laravelがvalidaterでユーザー認証のバリデーションを用意してくれている

- usersテーブルの他のメールアドレスと被らないこと`unique:users`といった定義も行なっています。[Laravelで使用できるバリデーションのルール](https://readouble.com/laravel/6.x/ja/validation.html#available-validation-rules)

- なお、カラム名がリクエストされたパラメータ名と異なる場合には、以下のようにテーブル名の後にカンマで区切ってカラム名を指定してください。<br>例えば以下は、リクエストされたパラメータ名はnicknameであるけれど、チェック対象のusersテーブルのカラム名がnameである場合の例です。

```
'nickname' => ['unique:users,name']
```

## 登録画面の作成

route関数

- Laravelのroute関数は、与えられた名前付きルートのURLを返します。[名前付きルートのリファレンス ](https://readouble.com/laravel/6.x/ja/routing.html#named-routes)

```
<form method="POST" action="{{ route('名前付きルート') }}">
```

>URLはどちらもregisterだが、送られたリクエストによって画面表示（GET）かユーザー登録（POST）か決まる

@csrf
- csrfは、Cross-Site Request Forgeries(クロスサイト・リクエスト・フォージェリ)というWebアプリケーションの脆弱性の略称で、上記のinputタグはこの脆弱性からWebサービスを守るためのトークン情報です。

#### ※POSTメソッドであるリクエストにこのトークン情報が無いと、Laravelではエラーをレスポンスします。ですので、POST送信を行うBladeには@csrfを含めるようにしてください。
>ハマりそうなポイント

old関数
- old関数は、引数にパラメータ名を渡すと、直前のリクエストのパラメータを返します。

```
<input class="form-control" type="text" id="name" name="name" required value="{{ old('name') }}">
```

- old関数を使うことで、入力した内容が保持された状態でユーザー登録画面が表示されるようになり、ユーザーはエラーになった箇所だけを修正すれば良くなります。

#### ※ただし、passwordとpassword_confirmationはold関数を使ってもnullが返ります。

もしログアウト後のリダイレクト先を変えたい場合は
- vendorディレクトリにあるコードは通常は修正しない
- AuthenticatesUsersトレイトをuseしているLoginControllerにloggedoutメソッドを追加します。
> オーバーライド

@guest, @auth
- @guestから@endguestに囲まれた部分は、ユーザーがまだログインしていない状態の時のみ処理されます。
- 逆に@authから@endauthに囲まれた部分は、ユーザーがログイン済みの状態の時のみ処理されます。
>railsのようにif文で条件分岐しなくてもいい！

#### ※POSTを使う際は、aタグではなく、buttonタグとformタグを使用します。

なお、今回`ログアウトのbuttonタグをformタグの配下`に置かないようにしています。

この理由は、ドロップダウンメニューの`liタグ内にformタグを配置すると`、本教材で使用しているMDBootstrapの仕様でドロップダウンメニューの`レイアウトが崩れてしまう`(横幅が大きくなる)ためです。

そこで、`formタグはliタグの外に配置し`、`formタグのid属性`と、`buttonタグのform属性`それぞれに`"logout-button"`という値を与え、両者を関連付けるようにしています。

$errors変数

- $errors変数は、Illuminate\Support\MessageBagクラスのインスタンスであり、バリデーションエラーメッセージを配列で持っています。
```
@if ($errors->any())
```

- エラーメッセージが1件以上ある場合は、MessageBagクラスのallメソッドで全エラーメッセージの配列を取得し、@foreachで繰り返し表示を行なっています。

```
@foreach($errors->all() as $error)
  <li>{{ $error }}</li>
@endforeach
```

バリデーションエラーメッセージの日本語化
- laravel/resources/langディレクトリにjaディレクトリを作成し、そこにvalidation.phpを作成
- [reference](https://readouble.com/laravel/6.x/ja/validation-php.html)からコピペ

メールアドレスの重複に関するエラーメッセージについて

- 悪意のあるユーザーが様々な他人のメールアドレスを次々と入力し、`どのメールアドレスの持ち主が本Webサービスの利用者であるかを調べることができてしまいます。`
- メールアドレスが登録済みであることがわかるエラーメッセージを表示すると、デメリットもあるということを知識として覚えておいてください。

レコードの削除
- DBを立ち上げてSQLを投げるか、tinkerで対話形式で削除することができる。
>tinkerはrails consoleのイメージ
```

```

[クラスがないって言われた時の処理](https://nextat.co.jp/staff/archives/121)

#### ログイン画面の作成
- ログアウトと同じように`AuthenticateUsersトレイト`の中でloginアクションメソッドが定義されている。

loginアクションメソッド

```
$this->validateLogin($request);
```
- 簡易的なバリデーションをかけている

```
   protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }
```

> ユーザー名とパスワードどちらも必須で文字列で入力するようバリデーション
- Laravelではユーザーに対してログイン時に入力させるID情報とパスワードのうち、ID情報をカスタマイズできるようになっています。
- 標準ではemailですが、email以外にしたければLoginControllerでusernameメソッドを`オーバーライド`すれば良いということです。

hasTooManyLoginAttempts
- ユーザーがログインを試して失敗した回数が定められた上限に達しているかを調べ、上限を越えるとtrueを返します。
- Laravelのデフォルトの設定では、1分間に5回のログイン失敗が上限となります。
- この設定変更方法は同じThrottlesLoginsトレイトを見るとわかります。
- もし、このデフォルト値をカスタマイズしたい場合は、LoginControllerに以下のようにmaxAttemptsプロパティ、decayMinutesプロパティを追加すれば良いことになります。

```
class LoginController extends Controller
{
    // 略
    use AuthenticatesUsers;

    protected $maxAttempts = 5;
    protected $decayMinutes = 1;
    // 略
}
```

キャッシュについて
- ログイン試行回数はLaravelのキャッシュで管理されています。
- キャッシュの保存先の設定はconfigディレクトリのcache.phpにあります。
- 本格的なWebサービスであればキャッシュの保存先はファイルではなく、より高速に読み書きできるメモリとすることが多いです。

remember meトークン
```
<input type="hidden" name="remember" id="remember" value="on">
```

- 上記のinputタグは、次回から自動でログインするという説明がされたチェックボックスに相当するものとなる
- ただし、本教材のWebサービスではtype属性をcheckboxではなくhiddenとすることでユーザーが直接操作できない隠し項目とし、value属性をonとすることで常にチェックが入ったのと同じ状態にしています。
- この結果どうなるかというと、ユーザーがログインした後はログアウト操作を行わない限り、そのブラウザではログイン状態が維持されます。
- 最初のログイン成功後にブラウザにはremember_web_...という名前のCookieが保存され、Laravelではこれがあれば2回目からのログインを不要にしています。

ログイン失敗時のメッセージについて
- もし、メールアドレスとパスワードの出し分けを行っていた場合、適当なメールアドレスや他人のメールアドレスでログインしようとして、パスワードが間違っています、と表示されると、
そのメールアドレスはこのWebサービスにユーザー登録はされており、あとはパスワードさえわかればログインできること
その適当なメールアドレスは誰かの実在するメールアドレスと考えられること
そのメールアドレスの保有者がこのWebサービスを利用していること
などがわかってしまい、悪意のあるユーザーに余計な情報を与えることになります。


- ログイン失敗時のメッセージを詳細に出し分けると、このようなデメリットもあるということを知識として覚えておいてください。

## 記事投稿機能の作成

リソースルートの追加

- よく使われる機能のルーティングをひとまとめにしたメソッドがLaravelでは用意されています。

```
Route::resource('/リソース名', 'コントローラー名'); 
```

```
ex) Route::resource('/articles', 'ArticleController');
```

未ログイン時の考慮

- Laravelには`ミドルウェア`という仕組みがあり、クライアントからのリクエストに対して、リクエストをコントローラーで処理する前あるいは後のタイミングで何らかの処理を行うことができます。

```
Route::resource('/articles', 'ArticleController')->except(['index'])->middleware('auth');
```

- authミドルウェアは、リクエストをコントローラーで処理する前にユーザーがログイン済みであるかどうかをチェックし、ログインしていなければユーザーをログイン画面へリダイレクトします。
- 既にログイン済みであれば、コントローラーでの処理が行われます。
- [ミドルウェア](https://readouble.com/laravel/6.x/ja/middleware.html)
- 各ルーティングでどのようなミドルウェアが使われているかは、`php artisan route:list`で確認することもできます。

未ログイン時のリダイレクト先画面をログイン画面以外にしたい場合
- authミドルウェアは、標準では未ログイン時のリダイレクト先がログイン画面になります。
- これを別の画面に変更したい場合は、app/Http/Middlewareディレクトリの、Authenticateミドルウェアを編集します

フォームリクエストの作成と編集
- フォームリクエストでは、記事投稿画面や記事更新画面から送信された記事タイトルや記事本文のバリデーションなどを行います。

- バリデーションはコントローラーで行わせることもできるのですが、一般にコントローラーにはあまり多くの処理を持たせないようにすることが望ましいとされています。

```
docker-compose exec workspace php artisan make:request ArticleRequest
```
>ArticleRequestが作成される

authorizeメソッド
- フォームリクエストのauthorizeメソッドはデフォルトではfalseを返すようにしていますが、このままだとステータスコード403のHTTPレスポンスがクライアントに返されます。
- そして、ルーティングされているコントローラーのアクションメソッドは処理がされません。
[フォームリクエストの認可](https://readouble.com/laravel/6.x/ja/validation.html#authorizing-form-requests)

rulesメソッド
- rulesメソッドでは、バリデーションのルールを定義します。
- 連想配列形式で、キーにパラメーターを、値にバリデーションルールを指定します。

```
return [
        'title' => 'required|max:50',
        'body' => 'required|max:500',
    ];
```

[使用可能なバリデーションルール](https://readouble.com/laravel/6.x/ja/validation.html#available-validation-rules)

attributesメソッド

- attributesメソッドでは、バリデーションエラーメッセージに表示される項目名をカスタマイズできます。

```
public function attributes()
{
    return [
        'title' => 'タイトル',
        'body' => '本文',
    ];
}
```

- ここでは項目名が記載の通りの日本語で表示されるようにしています。

引数の型宣言
```
public function store(ArticleRequest $request, Article $article)
```

- storeアクションメソッドは、第一引数が$requestとなっています。

- その$requestの手前にはArticleRequestと記述されています。

- これは、引数$requestはArticleRequestクラスのインスタンスである、ということを宣言しています。

- 宣言することでどうなるかというと、もしstoreメソッドの第一引数に、ArticleRequestクラスのインスタンス以外のものが渡されると`TypeErrorという例外が発生して処理は中断します。`
>ある程度のコード量になってくると、引数の型宣言はコードの可読性を高める上で非常に有用です。
[PHPの型の種類](https://www.php.net/manual/ja/language.types.intro.php)
[型宣言](https://www.php.net/manual/ja/functions.arguments.php#functions.arguments.type-declaration)

- Laravelのコントローラーはメソッドの引数で型宣言を行うと、そのクラスのインスタンスが自動で生成されてメソッド内で使えるようになります。
- このようにメソッドの内部で他のクラスのインスタンスを生成するのではなく、外で生成されたクラスのインスタンスをメソッドの引数として受け取る流れをDI(Dependency Injection)と言います。

#### DIを使うことで、あるクラスがあるクラスへ依存している度合い、ここではArticleControllerがArticleクラスへ依存している度合いを下げ、今後の変更がしやすい、テストがしやすい設計となります。

### エラー：新規記事を生成できない。

```
エラーメッセージ
SQLSTATE[42703]: Undefined column: 7 ERROR: column "updated_at" of relation "articles"
does not exist LINE 1: ...sert into "articles" ("title", "body", "user_id", "updated_a... ^ (SQL: insert
 into "articles" ("title", "body", "user_id", "updated_at", "created_at") 
 values (titel, content, 3, 2021-02-13 17:07:37, 2021-02-13 17:07:37) returning "id")
```

`updated_atカラムがないよー`

- 原因：updated_atカラムがなかったことが原因

```
# 日付_create_articles_table.php

    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('id'); これと
            $table->string('title');
            $table->text('body');
            $table->bigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps(); これがなかったからupdated_atが作成されなかった
        });
    }
```

- 解決法：マイグレーションファイルにカラムを追加してDBロールバック⇨再度DBマイグレートを実行

```
docker-compose exec workspace php artisan migrate:rollback
```
```
docker-compose exec workspace php artisan migrate
```

#### 対策：まずエラーメッセージの内容を読み解くこと。（今回の場合はupdated_atカラムがundefindになっているところに着目するべきだった）<br>その上でDBテーブルのエラーだったのでマイグレーションファイルがおかしくないか見にいく。<br>または、マイグレートが正しく実行されているか確認する。

fillableの利用

```
$article->fill($request->all());
```

- リクエストのallメソッドを使うことで、記事投稿画面から送信されたPOSTリクエストのパラメータを以下のように配列で取得できます。
- そして、Articleモデルのfillメソッドにこの配列を渡すと、

```
   protected $fillable = [
        'title',
        'body',
    ];
```

- Artcleモデルのfillableプロパティ内に指定しておいたプロパティ(ここではtitleとbody)のみが、$articleの各プロパティに代入されます。
>記事投稿画面ではタイトルと本文のみを入力してPOST送信できるように作りましたが、クライアント側でツールなどを使ってそれ以外のパラメーターも含んだ不正なリクエストをPOST送信することは可能です。しかし、fillableプロパティを定義したことで、クライアントからのリクエストのパラメーター値をそのまま取り込んで更新しても良いプロパティは、titleとbodyのみと制限されるようになりました。これによって、不正なリクエストによってarticlesテーブルが予期せぬ内容に更新されることを防ぐようになりました。

## 編集、削除昨日

編集

```
public function edit(Article $article) Articleモデルのインスタンスが代入されている
    {
        return view('articles.edit', ['article' => $article]);    
    }
```

- storeアクションメソッドの時と同様、LaravelではArticleモデルのインスタンスのDI(依存性の注入)が行われます。
- DIが行われることで、editアクションメソッド内の$articleにはArticleモデルのインスタンスが代入された状態となっています。
>疎結合にする
- storeアクションメソッドの時と異なり、editアクションメソッドの場合は、$articleには、このeditアクションメソッドが呼び出された時のURIが例えばarticles/3/editであれば、idが3であるArticleモデルのインスタンスが代入されます。

暗黙の結合

- Laravelはタイプヒントされた変数名とルートセグメント名が一致する場合、Laravelはルートかコントローラアクション中にEloquentモデルが定義されていると、自動的に依存解決します。
>railsのように:idと入れなくてもいい

@methodの使用

- LaravelのBladeでPATCHメソッド等を使う場合は、formタグではmethod属性を"POST"のままとしつつ、@methodでPATCHメソッド等を指定するようにします。

```
<form method="POST" action="{{ route('articles.update', ['article' => $article]) }}">
  @method('PATCH')
```

認証済みユーザー情報の取得
- 記事ごとの更新・削除メニューは、その記事を投稿したユーザーにのみ表示する必要があります。

```
@if( Auth::id() === $article->user_id )
// 略
@endif
```
- `Auth::id`でログイン中のユーザーIDが取得できる

[Null合体演算子(??)](https://www.php.net/manual/ja/language.operators.comparison.php#language.operators.comparison.coalesce)

```
{{ $article->title ?? old('title') }} タイトルが存在していたら、表示する
```

- $article->title ?? old('title')となっているコードの??はNull合体演算子と呼ばれるものです。

- null合体演算子は、式1 ?? 式2という形式で記述し、以下の結果となります。

>- 式1がnullでない場合は、式1が結果となる
>- 式1がnullである場合は、式2が結果となる
- form.blade.phpは、記事投稿画面と記事更新画面で共用していますが、記事投稿画面のビューにはコントローラーから変数$articleは渡されていません。
- その為、単純に$article->titleとだけにすると、記事更新画面の表示では問題ないのですが、記事投稿画面を表示しようとするとエラーとなってしまいます。

#### $article->titleとした場合の新規投稿画面のエラー内容

```
Undefined variable: article (View: /var/www/html/resources/views/articles/form.blade.php)
```

```
$article is undefined
```

- $ariticleがundefinedになってる！ないよー

モデルのfillメソッドの戻り値はそのモデル自身
```
$article->user_id = $request->user()->id;
```

ルーティングの確認と編集

[部分的なリソースルート](https://readouble.com/laravel/6.x/ja/controllers.html#restful-partial-resource-routes)

```
Route::resource('/articles', 'ArticleController')->except(['index', 'show'])->middleware('auth'); //-- exceptメソッドの引数を変更
Route::resource('/articles', 'ArticleController')->only(['show']); //-- この行を追加
```

- ここでは、リソースルートにonlyメソッドとexceptメソッドを使い分けて、showアクションメソッドに対してauthミドルウェアを使わないようにしています。