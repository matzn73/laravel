<?php

namespace App\Http\Controllers;

use App\Article;

use App\Http\Requests\ArticleRequest;

use Illuminate\Http\Request;

class ArticleController extends Controller //Controllerを継承している
{
    public function index() //indexメソッドを定義
    {   
        $articles = Article::all()->sortByDesc('created_at');

        return view('articles.index', compact('articles')); //viewを返す
                                        //ここでarticlesを連組配列で定義することでviewファイルで$articlesが使える
    }     
    
    public function create()
    {
        return view('articles.create');
    }

    public function store(ArticleRequest $request, Article $article)
    {
        $article->fill($request->all()); 
        $article->user_id = $request->user()->id;
        $article->save();
        return redirect()->route('articles.index');
    }
}
