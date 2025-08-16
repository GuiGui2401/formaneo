@extends('admin.layouts.app')

@section('title', 'Gestion des quiz')
@section('page-title', 'Quiz')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="subject">
                    <option value="">Toutes matières</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject }}" {{ request('subject') === $subject ? 'selected' : '' }}>{{ $subject }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="difficulty">
                    <option value="">Toutes difficultés</option>
                    @foreach($difficulties as $difficulty)
                        <option value="{{ $difficulty }}" {{ request('difficulty') === $difficulty ? 'selected' : '' }}>{{ ucfirst($difficulty) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    <a href="{{ route('admin.quizzes.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.quizzes.create') }}" class="btn btn-success w-100">Nouveau Quiz</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Liste des quiz ({{ $quizzes->total() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Quiz</th>
                        <th>Matière</th>
                        <th>Difficulté</th>
                        <th>Questions</th>
                        <th>Tentatives</th>
                        <th>Statut</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quizzes as $quiz)
                        <tr>
                            <td>
                                <div>
                                    <div class="fw-bold">{{ $quiz->title }}</div>
                                    @if($quiz->description)
                                        <small class="text-muted">{{ Str::limit($quiz->description, 60) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td><span class="badge bg-info">{{ $quiz->subject }}</span></td>
                            <td>
                                <span class="badge 
                                    @if($quiz->difficulty === 'facile') bg-success
                                    @elseif($quiz->difficulty === 'moyen') bg-warning
                                    @else bg-danger
                                    @endif">
                                    {{ ucfirst($quiz->difficulty) }}
                                </span>
                            </td>
                            <td><span class="badge bg-secondary">{{ $quiz->questions_count }}</span></td>
                            <td>{{ \App\Models\QuizResult::where('quiz_id', $quiz->id)->count() }}</td>
                            <td>
                                <span class="status-badge {{ $quiz->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $quiz->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.quizzes.show', $quiz) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteQuiz({{ $quiz->id }}, '{{ $quiz->title }}')" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                                <p>Aucun quiz trouvé</p>
                                <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">Créer le premier quiz</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($quizzes->hasPages())
        <div class="card-footer">{{ $quizzes->withQueryString()->links() }}</div>
    @endif
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le quiz <strong id="quizName"></strong> ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteQuiz(quizId, quizTitle) {
    document.getElementById('quizName').textContent = quizTitle;
    document.getElementById('deleteForm').action = `/admin/quizzes/${quizId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush