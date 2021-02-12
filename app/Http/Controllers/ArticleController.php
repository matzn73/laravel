<?php

namespace App\Http\Controllers;

use App\Article;

use Illuminate\Http\Request;

class ArticleController extends Controller //Controllerを継承している
{
    public function index() //indexメソッドを定義
    {   
        $articles = Article::all()->sortByDesc('created_at');

        return view('articles.index', compact('articles')); //viewを返す
                                        //ここでarticlesを連組配列で定義することでviewファイルで$articlesが使える
    }                           
}
