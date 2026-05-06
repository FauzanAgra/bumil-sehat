<?php

namespace App\Http\Controllers\Mother;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $user      = auth()->user();
        $pregnancy = $user->motherProfile?->activePregnancy;
        $week      = $pregnancy?->gestational_week;

        $query = Article::published();

        // Filter berdasarkan minggu kehamilan kalau ada
        if ($week) {
            $query->forWeek($week);
        }

        // Search
        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        $articles = $query->latest('published_at')->paginate(6);

        return view('dashboard.mother.articles', compact('user', 'articles', 'week'));
    }

    public function show(Article $article)
    {
        abort_if($article->status !== 'published', 404);

        $user      = auth()->user();
        $pregnancy = $user->motherProfile?->activePregnancy;
        $week      = $pregnancy?->gestational_week;

        // Artikel terkait — minggu kehamilan sama, bukan artikel ini
        $related = Article::published()
            ->when($week, fn($q) => $q->forWeek($week))
            ->where('id', '!=', $article->id)
            ->take(2)
            ->get();

        return view('dashboard.mother.article-detail', compact(
            'user',
            'article',
            'related'
        ));
    }
}
