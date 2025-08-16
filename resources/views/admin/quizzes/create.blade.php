@extends('admin.layouts.app')

@section('title', 'Créer un quiz')
@section('page-title', 'Nouveau Quiz')

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Quiz', 'url' => route('admin.quizzes.index')],
        ['title' => 'Nouveau Quiz', 'url' => '']
    ];
@endphp

@section('content')
<form action="{{ route('admin.quizzes.store') }}" method="POST" id="quizForm">
    @csrf
    
    <div class="row">
        <!-- Informations générales -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations générales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre du quiz <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title') }}" 
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Matière <span class="text-danger">*</span></label>
                        <select class="form-select @error('subject') is-invalid @enderror" 
                                id="subject" 
                                name="subject" 
                                required>
                            <option value="">Choisir une matière</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject }}" {{ old('subject') === $subject ? 'selected' : '' }}>
                                    {{ $subject }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="difficulty" class="form-label">Difficulté <span class="text-danger">*</span></label>
                        <select class="form-select @error('difficulty') is-invalid @enderror" 
                                id="difficulty" 
                                name="difficulty" 
                                required>
                            <option value="">Choisir la difficulté</option>
                            @foreach($difficulties as $difficulty)
                                <option value="{{ $difficulty }}" {{ old('difficulty') === $difficulty ? 'selected' : '' }}>
                                    {{ ucfirst($difficulty) }}
                                </option>
                            @endforeach
                        </select>
                        @error('difficulty')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Quiz actif
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Questions -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Questions (<span id="questionCount">0</span>)
                    </h5>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addQuestion()">
                        <i class="fas fa-plus me-1"></i>
                        Ajouter une question
                    </button>
                </div>
                <div class="card-body">
                    <div id="questionsContainer">
                        <!-- Les questions seront ajoutées ici dynamiquement -->
                    </div>
                    
                    <div id="noQuestions" class="text-center py-4 text-muted">
                        <i class="fas fa-question-circle fa-3x mb-3"></i>
                        <p>Aucune question ajoutée</p>
                        <button type="button" class="btn btn-primary" onclick="addQuestion()">
                            Ajouter la première question
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.quizzes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Retour
                </a>
                
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i>
                    Créer le quiz
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let questionIndex = 0;

function addQuestion() {
    const container = document.getElementById('questionsContainer');
    const noQuestions = document.getElementById('noQuestions');
    
    const questionHtml = `
        <div class="question-item border rounded mb-3 p-3" data-index="${questionIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Question ${questionIndex + 1}</h6>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeQuestion(${questionIndex})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Question <span class="text-danger">*</span></label>
                <textarea class="form-control" 
                          name="questions[${questionIndex}][question]" 
                          rows="2" 
                          required 
                          placeholder="Saisissez votre question..."></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Options de réponse <span class="text-danger">*</span></label>
                <div class="options-container">
                    ${generateOptions(questionIndex)}
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Réponse correcte <span class="text-danger">*</span></label>
                <select class="form-select" name="questions[${questionIndex}][correct_answer]" required>
                    <option value="">Choisir la bonne réponse</option>
                    <option value="0">Option A</option>
                    <option value="1">Option B</option>
                    <option value="2">Option C</option>
                    <option value="3">Option D</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Explication (optionnel)</label>
                <textarea class="form-control" 
                          name="questions[${questionIndex}][explanation]" 
                          rows="2" 
                          placeholder="Expliquez pourquoi cette réponse est correcte..."></textarea>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', questionHtml);
    noQuestions.style.display = 'none';
    questionIndex++;
    updateQuestionCount();
}

function generateOptions(questionIndex) {
    const options = ['A', 'B', 'C', 'D'];
    return options.map((option, index) => `
        <div class="input-group mb-2">
            <span class="input-group-text">${option}</span>
            <input type="text" 
                   class="form-control" 
                   name="questions[${questionIndex}][options][]" 
                   placeholder="Option ${option}" 
                   required>
        </div>
    `).join('');
}

function removeQuestion(index) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette question ?')) {
        const questionItem = document.querySelector(`[data-index="${index}"]`);
        questionItem.remove();
        updateQuestionCount();
        
        if (document.querySelectorAll('.question-item').length === 0) {
            document.getElementById('noQuestions').style.display = 'block';
        }
    }
}

function updateQuestionCount() {
    const count = document.querySelectorAll('.question-item').length;
    document.getElementById('questionCount').textContent = count;
}

// Validation du formulaire
document.getElementById('quizForm').addEventListener('submit', function(e) {
    const questions = document.querySelectorAll('.question-item');
    
    if (questions.length === 0) {
        e.preventDefault();
        alert('Veuillez ajouter au moins une question.');
        return false;
    }
    
    // Vérifier que chaque question a toutes ses options remplies
    let hasError = false;
    questions.forEach(function(question) {
        const options = question.querySelectorAll('input[name*="[options]"]');
        options.forEach(function(option) {
            if (!option.value.trim()) {
                hasError = true;
                option.classList.add('is-invalid');
            } else {
                option.classList.remove('is-invalid');
            }
        });
    });
    
    if (hasError) {
        e.preventDefault();
        alert('Veuillez remplir toutes les options de réponse.');
        return false;
    }
});

// Ajouter une question par défaut
document.addEventListener('DOMContentLoaded', function() {
    addQuestion();
});
</script>

<style>
.question-item {
    background-color: #f8f9fa;
}

.question-item:hover {
    background-color: #e9ecef;
}

.options-container .input-group-text {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}
</style>
@endpush