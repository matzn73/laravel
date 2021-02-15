<?php

namespace App\Http\Controllers;

use App\Article;
use App\Tag;
use App\Http\Requests\ArticleRequest;
use Illuminate\Http\Request;

class ArticleController extends Controller //Controllerを継承している
{
    public function __construct()
    {
        $this->authorizeResource(Article::class, 'article');
    }

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
        $request->tags->each(function ($tagName) use ($article) {
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $article->tags()->attach($tag);
        });
        return redirect()->route('articles.index');
    }

    public function edit(Article $article)
    {
        $tagNames = $article->tags->map(function ($tag) {
            return ['text' => $tag->name]; //textというキーがついている必要がある
        });
        return view('articles.edit', [
            'article' => $article,
            'tagNames' => $tagNames,
        ]);
    }

    public function update(ArticleRequest $request, Article $article)
    {
        $article->fill($request->all())->save();
        $article->tags()->detach(); //全削除した上で
        $request->tags->each(function ($tagName) use ($article) { //eachで回して再度タグをテーブルに登録する
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $article->tags()->attach($tag);
        });
        return redirect()->route('articles.index');
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return redirect()->route('articles.index');
    }

    public function show(Article $article)
    {
        return view('articles.show', ['article' => $article]);
    }
}
