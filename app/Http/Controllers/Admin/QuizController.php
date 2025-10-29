<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizResult;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $query = Quiz::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        $quizzes = $query->latest()->paginate(20);
        $subjects = Quiz::distinct()->pluck('subject');
        $difficulties = ['facile', 'moyen', 'difficile'];

        return view('admin.quizzes.index', compact('quizzes', 'subjects', 'difficulties'));
    }

    public function create()
    {
        $subjects = ['dropshipping', 'marketing', 'ecommerce', 'affiliation', 'shopify', 'design', 'finance', 'social_media', 'entrepreneurship', 'advertising'];
        $difficulties = ['facile', 'moyen', 'difficile'];
        
        return view('admin.quizzes.create', compact('subjects', 'difficulties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject' => 'required|string|max:100',
            'difficulty' => 'required|in:facile,moyen,difficile',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.correct_answer' => 'required|integer|min:0',
            'questions.*.explanation' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $quiz = Quiz::create([
            'title' => $request->title,
            'description' => $request->description,
            'subject' => $request->subject,
            'difficulty' => $request->difficulty,
            'questions' => $request->questions,
            'questions_count' => count($request->questions),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Quiz créé avec succès.');
    }

    // MÉTHODE MANQUANTE : show()
    public function show(Quiz $quiz)
    {
        $stats = [
            'total_attempts' => QuizResult::where('quiz_id', $quiz->id)->count(),
            'average_score' => QuizResult::where('quiz_id', $quiz->id)->avg('score') ?? 0,
            'completion_rate' => QuizResult::where('quiz_id', $quiz->id)
                ->where('score', '>=', 60)->count(),
        ];

        return view('admin.quizzes.show', compact('quiz', 'stats'));
    }

    // MÉTHODE MANQUANTE : edit()
    public function edit(Quiz $quiz)
    {
        $subjects = ['dropshipping', 'marketing', 'ecommerce', 'affiliation', 'shopify', 'design', 'finance', 'social_media', 'entrepreneurship', 'advertising'];
        $difficulties = ['facile', 'moyen', 'difficile'];
        
        return view('admin.quizzes.edit', compact('quiz', 'subjects', 'difficulties'));
    }

    // MÉTHODE CORRIGÉE : update()
    public function update(Request $request, Quiz $quiz)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject' => 'required|string|max:100',
            'difficulty' => 'required|in:facile,moyen,difficile',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.correct_answer' => 'required|integer|min:0',
            'questions.*.explanation' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $quiz->update([
            'title' => $request->title,
            'description' => $request->description,
            'subject' => $request->subject,
            'difficulty' => $request->difficulty,
            'questions' => $request->questions,
            'questions_count' => count($request->questions),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Quiz mis à jour avec succès.');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();

        return redirect()->route('admin.quizzes.index')
            ->with('success', 'Quiz supprimé avec succès.');
    }

    public function results(Quiz $quiz)
    {
        $results = QuizResult::where('quiz_id', $quiz->id)
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.quizzes.results', compact('quiz', 'results'));
    }
}