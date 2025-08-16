@extends('admin.layouts.app')

@section('title', 'Résultats du quiz')
@section('page-title', 'Résultats: ' . $quiz->title)

@php
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['title' => 'Quiz', 'url' => route('admin.quizzes.index')],
        ['title' => $quiz->title, 'url' => route('admin.quizzes.show', $quiz)],
        ['title' => 'Résultats', 'url' => '']
    ];
@endphp

@section('content')
<!-- Statistiques du quiz -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card-1">
            <div class="card-body text-center text-white">
                <h3 class="mb-1">{{ $results->total() }}</h3>
                <small>Total des tentatives</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card-2">
            <div class="card-body text-center text-white">
                <h3 class="mb-1">{{ round($results->avg('score'), 1) }}%</h3>
                <small>Score moyen</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card-3">
            <div class="card-body text-center text-white">
                <h3 class="mb-1">{{ $results->where('score', '>=', 60)->count() }}</h3>
                <small>Réussites (≥60%)</small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card-4">
            <div class="card-body text-center">
                <h3 class="mb-1 text-dark">{{ round(($results->where('score', '>=', 60)->count() / max($results->total(), 1)) * 100, 1) }}%</h3>
                <small class="text-muted">Taux de réussite</small>
            </div>
        </div>
    </div>
</div>

<!-- Informations du quiz -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1">{{ $quiz->title }}</h5>
                        <p class="text-muted mb-0">
                            <span class="badge bg-info me-2">{{ $quiz->subject }}</span>
                            <span class="badge bg-secondary me-2">{{ ucfirst($quiz->difficulty) }}</span>
                            <span class="badge bg-primary">{{ $quiz->questions_count }} questions</span>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('admin.quizzes.show', $quiz) }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-arrow-left me-1"></i>
                            Retour au quiz
                        </a>
                        <button class="btn btn-outline-success" onclick="exportResults()">
                            <i class="fas fa-download me-1"></i>
                            Exporter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Graphique de distribution des scores -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    Distribution des scores
                </h5>
            </div>
            <div class="card-body">
                <canvas id="scoresChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Liste des résultats -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>
            Résultats détaillés ({{ $results->total() }})
        </h5>
        
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" id="filterScore" onchange="filterResults()">
                <option value="">Tous les scores</option>
                <option value="excellent">Excellent (≥90%)</option>
                <option value="bon">Bon (70-89%)</option>
                <option value="passable">Passable (60-69%)</option>
                <option value="echec">Échec (<60%)</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Score</th>
                        <th>Bonnes réponses</th>
                        <th>Temps</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $result)
                        <tr class="result-row" data-score="{{ $result->score }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                        {{ substr($result->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">
                                            <a href="{{ route('admin.users.show', $result->user) }}" class="text-decoration-none">
                                                {{ $result->user->name }}
                                            </a>
                                        </div>
                                        <small class="text-muted">{{ $result->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress me-2" style="width: 60px; height: 8px;">
                                        <div class="progress-bar 
                                            @if($result->score >= 90) bg-success
                                            @elseif($result->score >= 70) bg-info  
                                            @elseif($result->score >= 60) bg-warning
                                            @else bg-danger
                                            @endif" 
                                            style="width: {{ $result->score }}%"></div>
                                    </div>
                                    <span class="fw-bold 
                                        @if($result->score >= 90) text-success
                                        @elseif($result->score >= 70) text-info
                                        @elseif($result->score >= 60) text-warning  
                                        @else text-danger
                                        @endif">
                                        {{ $result->score }}%
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $result->correct_answers }}/{{ $result->total_questions }}
                                </span>
                            </td>
                            <td>
                                @if($result->time_taken)
                                    <span class="text-muted">
                                        {{ gmdate('i:s', $result->time_taken) }}min
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($result->score >= 60)
                                    <span class="status-badge status-active">
                                        <i class="fas fa-check me-1"></i>
                                        Réussi
                                    </span>
                                @else
                                    <span class="status-badge status-inactive">
                                        <i class="fas fa-times me-1"></i>
                                        Échoué
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $result->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $result->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <button class="btn btn-outline-info btn-sm" 
                                        onclick="showResultDetails({{ $result->id }})"
                                        title="Voir détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucun résultat</h5>
                                <p class="text-muted">Ce quiz n'a encore été tenté par aucun utilisateur</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($results->hasPages())
        <div class="card-footer">
            {{ $results->links() }}
        </div>
    @endif
</div>

<!-- Modal pour les détails d'un résultat -->
<div class="modal fade" id="resultDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails du résultat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resultDetailsContent">
                <!-- Contenu chargé via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Graphique de distribution des scores
const ctx = document.getElementById('scoresChart').getContext('2d');

// Préparer les données pour le graphique
const scores = @json($results->pluck('score'));
const scoreRanges = {
    '0-19': 0, '20-39': 0, '40-59': 0, '60-79': 0, '80-100': 0
};

scores.forEach(score => {
    if (score < 20) scoreRanges['0-19']++;
    else if (score < 40) scoreRanges['20-39']++;
    else if (score < 60) scoreRanges['40-59']++;
    else if (score < 80) scoreRanges['60-79']++;
    else scoreRanges['80-100']++;
});

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: Object.keys(scoreRanges).map(range => range + '%'),
        datasets: [{
            label: 'Nombre d\'utilisateurs',
            data: Object.values(scoreRanges),
            backgroundColor: [
                'rgba(220, 53, 69, 0.8)',   // Rouge pour 0-19
                'rgba(253, 126, 20, 0.8)',  // Orange pour 20-39
                'rgba(255, 193, 7, 0.8)',   // Jaune pour 40-59
                'rgba(13, 202, 240, 0.8)',  // Bleu pour 60-79
                'rgba(25, 135, 84, 0.8)'    // Vert pour 80-100
            ],
            borderColor: [
                'rgb(220, 53, 69)',
                'rgb(253, 126, 20)',
                'rgb(255, 193, 7)',
                'rgb(13, 202, 240)',
                'rgb(25, 135, 84)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Filtrer les résultats par score
function filterResults() {
    const filter = document.getElementById('filterScore').value;
    const rows = document.querySelectorAll('.result-row');
    
    rows.forEach(row => {
        const score = parseInt(row.dataset.score);
        let show = true;
        
        switch(filter) {
            case 'excellent':
                show = score >= 90;
                break;
            case 'bon':
                show = score >= 70 && score < 90;
                break;
            case 'passable':
                show = score >= 60 && score < 70;
                break;
            case 'echec':
                show = score < 60;
                break;
            default:
                show = true;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

// Afficher les détails d'un résultat
function showResultDetails(resultId) {
    // Ici vous pouvez faire un appel AJAX pour récupérer les détails
    // Pour l'instant, on affiche juste un placeholder
    document.getElementById('resultDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2">Chargement des détails...</p>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('resultDetailsModal')).show();
    
    // Simulation d'un chargement
    setTimeout(() => {
        document.getElementById('resultDetailsContent').innerHTML = `
            <div class="alert alert-info">
                <h6 class="alert-heading">Fonctionnalité en développement</h6>
                <p class="mb-0">L'affichage détaillé des réponses par question sera bientôt disponible.</p>
            </div>
        `;
    }, 1000);
}

// Exporter les résultats
function exportResults() {
    // Créer un CSV simple
    const rows = [
        ['Utilisateur', 'Email', 'Score', 'Bonnes réponses', 'Total questions', 'Temps (sec)', 'Date']
    ];
    
    document.querySelectorAll('.result-row').forEach(row => {
        if (row.style.display !== 'none') {
            const cells = row.querySelectorAll('td');
            const userName = cells[0].querySelector('.fw-medium a').textContent.trim();
            const userEmail = cells[0].querySelector('.text-muted').textContent.trim();
            const score = cells[1].querySelector('.fw-bold').textContent.replace('%', '');
            const answers = cells[2].querySelector('.badge').textContent;
            const [correct, total] = answers.split('/');
            const timeText = cells[3].textContent.trim();
            const time = timeText === '-' ? '0' : timeText.replace('min', '');
            const date = cells[5].querySelector('div').textContent.trim();
            
            rows.push([userName, userEmail, score, correct, total, time, date]);
        }
    });
    
    const csvContent = rows.map(row => row.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `resultats-quiz-${Date.now()}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@endpush