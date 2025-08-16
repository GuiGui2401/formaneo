@extends('admin.layouts.app')

@section('title', 'Quiz: ' . $quiz->title)
@section('page-title', $quiz->title)

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body">
                <h4>{{ $quiz->title }}</h4>
                <p class="text-muted">{{ $quiz->description }}</p>
                
                <div class="d-flex gap-2 mb-3">
                    <span class="badge bg-info">{{ $quiz->subject }}</span>
                    <span class="badge 
                        @if($quiz->difficulty === 'facile') bg-success
                        @elseif($quiz->difficulty === 'moyen') bg-warning
                        @else bg-danger
                        @endif">
                        {{ ucfirst($quiz->difficulty) }}
                    </span>
                    <span class="status-badge {{ $quiz->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $quiz->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn btn-primary btn-sm flex-fill">
                        <i class="fas fa-edit me-1"></i>
                        Modifier
                    </a>
                    <a href="{{ route('admin.quizzes.results', $quiz) }}" class="btn btn-outline-info btn-sm flex-fill">
                        <i class="fas fa-chart-bar me-1"></i>
                        Résultats
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Statistiques -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Statistiques</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <h4 class="text-primary">{{ $quiz->questions_count }}</h4>
                            <small class="text-muted">Questions</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-info">{{ $stats['total_attempts'] }}</h4>
                        <small class="text-muted">Tentatives</small>
                    </div>
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-success">{{ round($stats['average_score'], 1) }}%</h4>
                            <small class="text-muted">Score moyen</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning">{{ $stats['completion_rate'] }}</h4>
                        <small class="text-muted">Réussites</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Questions -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    Questions ({{ is_array($quiz->questions) ? count($quiz->questions) : 0 }})
                </h5>
            </div>
            <div class="card-body">
                @if(is_array($quiz->questions) && count($quiz->questions) > 0)
                    @foreach($quiz->questions as $index => $question)
                        <div class="question-item border rounded mb-3 p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0">Question {{ $index + 1 }}</h6>
                                <span class="badge bg-secondary">
                                    {{ isset($question['correct_answer']) ? $question['correct_answer'] + 1 : 'N/A' }}
                                </span>
                            </div>
                            
                            <p class="fw-medium">{{ $question['question'] ?? 'Question non définie' }}</p>
                            
                            @if(isset($question['options']) && is_array($question['options']))
                                <div class="row">
                                    @foreach($question['options'] as $optionIndex => $option)
                                        <div class="col-md-6 mb-2">
                                            <div class="p-2 rounded {{ isset($question['correct_answer']) && $optionIndex === $question['correct_answer'] ? 'bg-success text-white' : 'bg-light' }}">
                                                <strong>{{ chr(65 + $optionIndex) }}.</strong> {{ $option }}
                                                @if(isset($question['correct_answer']) && $optionIndex === $question['correct_answer'])
                                                    <i class="fas fa-check ms-2"></i>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            @if(isset($question['explanation']) && $question['explanation'])
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <strong>Explication:</strong> {{ $question['explanation'] }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune question</h5>
                        <p class="text-muted mb-4">Ce quiz ne contient aucune question</p>
                        <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn btn-primary">
                            Ajouter des questions
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
