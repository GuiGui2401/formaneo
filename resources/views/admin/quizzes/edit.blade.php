@extends('admin.layouts.app')

@section('title', 'Modifier le quiz')
@section('page-title', 'Modifier: ' . $quiz->title)

@section('content')
<form action="{{ route('admin.quizzes.update', $quiz) }}" method="POST" id="quizForm">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Titre du quiz</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $quiz->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description', $quiz->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Matière</label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                               id="subject" name="subject" value="{{ old('subject', $quiz->subject) }}" required>
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="difficulty" class="form-label">Difficulté</label>
                        <select class="form-select @error('difficulty') is-invalid @enderror" 
                                id="difficulty" name="difficulty" required>
                            <option value="facile" {{ old('difficulty', $quiz->difficulty) === 'facile' ? 'selected' : '' }}>Facile</option>
                            <option value="moyen" {{ old('difficulty', $quiz->difficulty) === 'moyen' ? 'selected' : '' }}>Moyen</option>
                            <option value="difficile" {{ old('difficulty', $quiz->difficulty) === 'difficile' ? 'selected' : '' }}>Difficile</option>
                        </select>
                        @error('difficulty')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                               value="1" {{ old('is_active', $quiz->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Quiz actif</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Questions</h5>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addQuestion()">
                        <i class="fas fa-plus me-1"></i>
                        Ajouter une question
                    </button>
                </div>
                <div class="card-body">
                    <div id="questionsContainer">
                        @php
                            $questions = old('questions', $quiz->questions);
                            if (!is_array($questions)) {
                                $questions = [];
                            }
                        @endphp
                        
                        @foreach($questions as $index => $question)
                            <div class="question-item border rounded mb-3 p-3" data-index="{{ $index }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Question {{ $index + 1 }}</h6>
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeQuestion({{ $index }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Question</label>
                                    <textarea class="form-control" name="questions[{{ $index }}][question]" 
                                              rows="2" required>{{ $question['question'] ?? '' }}</textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Options de réponse</label>
                                    <div class="options-container">
                                        @foreach(['A', 'B', 'C', 'D'] as $optionIndex => $optionLabel)
                                            <div class="input-group mb-2">
                                                <span class="input-group-text">{{ $optionLabel }}</span>
                                                <input type="text" class="form-control" 
                                                       name="questions[{{ $index }}][options][]" 
                                                       value="{{ isset($question['options'][$optionIndex]) ? $question['options'][$optionIndex] : '' }}" 
                                                       placeholder="Option {{ $optionLabel }}" required>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Réponse correcte</label>
                                    <select class="form-select" name="questions[{{ $index }}][correct_answer]" required>
                                        @foreach(['A', 'B', 'C', 'D'] as $optionIndex => $optionLabel)
                                            <option value="{{ $optionIndex }}" 
                                                {{ isset($question['correct_answer']) && $question['correct_answer'] == $optionIndex ? 'selected' : '' }}>
                                                Option {{ $optionLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Explication (optionnel)</label>
                                    <textarea class="form-control" name="questions[{{ $index }}][explanation]" 
                                              rows="2">{{ $question['explanation'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endforeach
                        
                        @if(empty($questions))
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-question-circle fa-3x mb-3"></i>
                                <p>Aucune question dans ce quiz</p>
                                <button type="button" class="btn btn-primary" onclick="addQuestion()">
                                    Ajouter la première question
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.quizzes.show', $quiz) }}" class="btn btn-secondary">Retour</a>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let questionIndex = {{ count($questions) }};

function addQuestion() {
    const container = document.getElementById('questionsContainer');
    
    // Masquer le message "aucune question" s'il existe
    const emptyMessage = container.querySelector('.text-center.py-4');
    if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }
    
    const questionHtml = `
        <div class="question-item border rounded mb-3 p-3" data-index="${questionIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Question ${questionIndex + 1}</h6>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeQuestion(${questionIndex})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Question</label>
                <textarea class="form-control" name="questions[${questionIndex}][question]" rows="2" required></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Options de réponse</label>
                <div class="options-container">
                    ${['A', 'B', 'C', 'D'].map((label, index) => `
                        <div class="input-group mb-2">
                            <span class="input-group-text">${label}</span>
                            <input type="text" class="form-control" name="questions[${questionIndex}][options][]" 
                                   placeholder="Option ${label}" required>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Réponse correcte</label>
                <select class="form-select" name="questions[${questionIndex}][correct_answer]" required>
                    <option value="0">Option A</option>
                    <option value="1">Option B</option>
                    <option value="2">Option C</option>
                    <option value="3">Option D</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Explication (optionnel)</label>
                <textarea class="form-control" name="questions[${questionIndex}][explanation]" rows="2"></textarea>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', questionHtml);
    questionIndex++;
}

function removeQuestion(index) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette question ?')) {
        const questionElement = document.querySelector(`[data-index="${index}"]`);
        if (questionElement) {
            questionElement.remove();
            
            // Vérifier s'il reste des questions
            const remainingQuestions = document.querySelectorAll('.question-item');
            if (remainingQuestions.length === 0) {
                // Afficher le message "aucune question"
                const container = document.getElementById('questionsContainer');
                container.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-question-circle fa-3x mb-3"></i>
                        <p>Aucune question dans ce quiz</p>
                        <button type="button" class="btn btn-primary" onclick="addQuestion()">
                            Ajouter la première question
                        </button>
                    </div>
                `;
            }
        }
    }
}
</script>
@endpush