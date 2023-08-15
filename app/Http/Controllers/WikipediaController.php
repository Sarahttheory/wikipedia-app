<?php

/** TODO: Важно помнить, что вы должны настроить запросы к Wikipedia API в методе fetchArticleFromWikipedia
 и использовать соответствующий CSS селектор для извлечения содержимого статьи.
 Этот пример предоставляет основную структуру для взаимодействия с моделями,
 базой данных и Wikipedia API с использованием библиотеки Goutte.
 Не забудьте настроить и доработать код согласно вашим конкретным требованиям и условиям.
**/

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Keyword;
use App\Models\ArticleKeyword;
use Goutte\Client;

class WikipediaController extends Controller
{
    public function importForm()
    {
        return view('import');
    }

    public function import(Request $request)
    {
        $keyword = $request->input('keyword');
        $article = $this->fetchArticleFromWikipedia($keyword);

        if ($article) {
            $this->saveArticleToDB($article);
            return redirect()->route('import.form')->with('success', 'Article imported successfully!');
        } else {
            return redirect()->route('import.form')->with('error', 'Article not found.');
        }
    }

    public function searchForm()
    {
        return view('search');
    }

    public function search(Request $request)
    {
        $searchKeyword = $request->input('search_keyword');
        $results = $this->searchArticles($searchKeyword);

        return view('results', ['results' => $results]);
    }

    private function fetchArticleFromWikipedia($keyword)
    {
        $client = new Client();

        // Здесь необходимо заменить URL и CSS селектор на соответствующие для Wikipedia
        $url = "https://en.wikipedia.org/wiki/$keyword";
        $crawler = $client->request('GET', $url);

        $content = $crawler->filter('.mw-parser-output')->text();
        return $content;
    }

    private function saveArticleToDB($articleContent)
    {
        $article = new Article();
        $article->title = 'Article Title'; // Установите нужный заголовок
        $article->content = $articleContent;
        $article->save();

        // Разбор на слова-атомы и сохранение в базе данных
        $atoms = preg_split('/\s+/', $articleContent);
        foreach ($atoms as $atom) {
            $atom = preg_replace('/[^\w]+/', '', $atom); // Удаление знаков препинания
            if (!empty($atom)) {
                $keyword = Keyword::firstOrCreate(['word' => $atom]);

                $articleKeyword = new ArticleKeyword();
                $articleKeyword->article_id = $article->id;
                $articleKeyword->keyword_id = $keyword->id;
                $articleKeyword->count = substr_count($articleContent, $atom);
                $articleKeyword->save();
            }
        }
    }

    private function searchArticles($searchKeyword)
    {
        $keyword = Keyword::where('word', $searchKeyword)->first();
        if (!$keyword) {
            return [];
        }

        $results = ArticleKeyword::where('keyword_id', $keyword->id)
            ->orderBy('count', 'desc')
            ->with('article')
            ->get();

        return $results;
    }
}
