@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
<style>
    :root {
        --chart-primary: #c2410c;
        --chart-secondary: #0f766e;
        --chart-accent: #1d4ed8;
        --chart-success: #15803d;
        --chart-warning: #d97706;
        --chart-danger: #dc2626;
        --chart-ink: #111827;
        --chart-mist: #f8fafc;
    }

    .analytics-header {
        font-family: 'Playfair Display', serif;
        background: linear-gradient(135deg, #7c2d12 0%, #0f766e 55%, #1d4ed8 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-card {
        position: relative;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(194, 65, 12, 0.14), transparent 48%);
        opacity: 0;
        transition: opacity 0.25s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 22px 40px -18px rgba(15, 23, 42, 0.25);
    }

    .stat-card:hover::before {
        opacity: 1;
    }

    .chart-card {
        background: linear-gradient(180deg, #ffffff 0%, #fffaf5 100%);
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 1rem;
        box-shadow: 0 20px 40px -30px rgba(15, 23, 42, 0.35);
    }

    .chart-container {
        position: relative;
        animation: fadeInUp 0.55s ease-out;
    }

    .stat-number {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
    }

    .pulse-dot {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    .metric-bar {
        background: linear-gradient(90deg, rgba(194, 65, 12, 0.12), rgba(15, 118, 110, 0.12));
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(24px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.45; }
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .delay-1 { animation-delay: 0.08s; }
    .delay-2 { animation-delay: 0.12s; }
    .delay-3 { animation-delay: 0.16s; }
    .delay-4 { animation-delay: 0.20s; }
    .delay-5 { animation-delay: 0.24s; }
    .delay-6 { animation-delay: 0.28s; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-orange-700 font-semibold mb-2">{{ $stats['study_code'] }} dataset</p>
            <h1 class="text-4xl font-black analytics-header mb-2">PBMC Analytics Dashboard</h1>
            <p class="text-gray-600 flex items-center gap-2">
                <span class="w-2 h-2 bg-green-500 rounded-full pulse-dot"></span>
                Imported IAVIC114 processing performance, turnaround, and quality trends
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button"
                    onclick="refreshData(this)"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                <i data-feather="refresh-cw" class="w-4 h-4"></i>
                Refresh
            </button>
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-pbmc text-white rounded-lg hover:bg-orange-700 transition-all">
                <i data-feather="grid" class="w-4 h-4"></i>
                Dashboard
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <div class="stat-card bg-white rounded-xl border p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-orange-50 rounded-lg">
                    <i data-feather="database" class="w-6 h-6 text-orange-700"></i>
                </div>
                <span class="text-xs font-semibold text-orange-700 bg-orange-50 px-2 py-1 rounded-full">REPORTS</span>
            </div>
            <div class="stat-number text-3xl text-gray-900 mb-1">{{ number_format($stats['total_records']) }}</div>
            <p class="text-sm text-gray-600">Imported PBMC records</p>
            <div class="mt-3 text-xs text-gray-500">
                {{ number_format($stats['unique_participants']) }} participants across {{ number_format($stats['unique_visits']) }} visit codes
            </div>
        </div>

        <div class="stat-card bg-white rounded-xl border p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-green-50 rounded-lg">
                    <i data-feather="activity" class="w-6 h-6 text-green-700"></i>
                </div>
                <span class="text-xs font-semibold text-green-700 bg-green-50 px-2 py-1 rounded-full">VIABILITY</span>
            </div>
            <div class="stat-number text-3xl text-gray-900 mb-1">{{ number_format($stats['avg_viability'], 1) }}%</div>
            <p class="text-sm text-gray-600">Average viability percent</p>
            <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-green-500 to-emerald-700 h-2 rounded-full" style="width: {{ min(max($stats['avg_viability'], 0), 100) }}%"></div>
            </div>
        </div>

        <div class="stat-card bg-white rounded-xl border p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-blue-50 rounded-lg">
                    <i data-feather="check-circle" class="w-6 h-6 text-blue-700"></i>
                </div>
                <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-1 rounded-full">PASS</span>
            </div>
            <div class="stat-number text-3xl text-gray-900 mb-1">{{ number_format($stats['pass_rate'], 1) }}%</div>
            <p class="text-sm text-gray-600">Samples marked as pass</p>
            <div class="mt-3 text-xs text-gray-500">{{ $stats['pass_count'] }} of {{ $stats['total_records'] }} reports</div>
        </div>

        <div class="stat-card bg-white rounded-xl border p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-teal-50 rounded-lg">
                    <i data-feather="clock" class="w-6 h-6 text-teal-700"></i>
                </div>
                <span class="text-xs font-semibold text-teal-700 bg-teal-50 px-2 py-1 rounded-full">TURNAROUND</span>
            </div>
            <div class="stat-number text-3xl text-gray-900 mb-1">{{ number_format($stats['avg_blood_draw_to_freezing']) }}</div>
            <p class="text-sm text-gray-600">Avg blood draw to freezing minutes</p>
            <div class="mt-3 text-xs text-gray-500">Processing to freezing avg: {{ number_format($stats['avg_processing_to_freezing']) }} min</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="chart-card p-6 chart-container lg:col-span-1">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Viability Bands</h3>
                    <p class="text-sm text-gray-500">High, medium, and low viability spread</p>
                </div>
                <i data-feather="pie-chart" class="w-5 h-5 text-gray-400"></i>
            </div>
            <div class="h-72">
                <canvas id="viabilityChart"></canvas>
            </div>
        </div>

        <div class="chart-card p-6 chart-container lg:col-span-2" class="delay-1">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Monthly Throughput</h3>
                    <p class="text-sm text-gray-500">Last 12 report months with record count and average viability</p>
                </div>
                <i data-feather="trending-up" class="w-5 h-5 text-gray-400"></i>
            </div>
            <div class="h-72">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="chart-card p-6 chart-container" class="delay-2">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Sample Condition Mix</h3>
                    <p class="text-sm text-gray-500">How imported samples are classified</p>
                </div>
                <i data-feather="shield" class="w-5 h-5 text-gray-400"></i>
            </div>
            <div class="h-80">
                <canvas id="conditionChart"></canvas>
            </div>
        </div>

        <div class="chart-card p-6 chart-container" class="delay-3">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Visit Distribution</h3>
                    <p class="text-sm text-gray-500">Top visit codes represented in imported reports</p>
                </div>
                <i data-feather="bar-chart-2" class="w-5 h-5 text-gray-400"></i>
            </div>
            <div class="h-80">
                <canvas id="visitChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="chart-card p-6 chart-container lg:col-span-1" class="delay-4">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Core KPIs</h3>
                    <p class="text-sm text-gray-500">Operational and data quality checkpoints</p>
                </div>
                <i data-feather="target" class="w-5 h-5 text-gray-400"></i>
            </div>

            <div class="space-y-5">
                <div>
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="text-gray-600">Complete records</span>
                        <span class="font-bold text-gray-900">{{ number_format($stats['complete_records']) }}</span>
                    </div>
                    <div class="metric-bar rounded-full h-2 overflow-hidden">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $stats['total_records'] > 0 ? ($stats['complete_records'] / $stats['total_records']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between text-sm mb-2">
                        <span class="text-gray-600">Reports with comments</span>
                        <span class="font-bold text-gray-900">{{ number_format($stats['with_comments']) }}</span>
                    </div>
                    <div class="metric-bar rounded-full h-2 overflow-hidden">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $stats['total_records'] > 0 ? ($stats['with_comments'] / $stats['total_records']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-1">
                    <div class="rounded-xl bg-orange-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-orange-700 font-semibold">Avg viable cells</p>
                        <p class="stat-number text-2xl text-gray-900 mt-2">{{ number_format($stats['avg_total_viable_cells'], 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">million</p>
                    </div>
                    <div class="rounded-xl bg-teal-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-teal-700 font-semibold">Avg cryovials</p>
                        <p class="stat-number text-2xl text-gray-900 mt-2">{{ number_format($stats['avg_cryovials'], 1) }}</p>
                        <p class="text-xs text-gray-500 mt-1">per report</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-xl border border-gray-200 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Last 30 days</p>
                        <p class="stat-number text-2xl text-gray-900 mt-2">{{ number_format($stats['last_30_days']) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">This year</p>
                        <p class="stat-number text-2xl text-gray-900 mt-2">{{ number_format($stats['this_year']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-card p-6 chart-container lg:col-span-1" class="delay-5">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Operator Snapshot</h3>
                    <p class="text-sm text-gray-500">Top operators by imported report volume</p>
                </div>
                <i data-feather="users" class="w-5 h-5 text-gray-400"></i>
            </div>

            <div class="space-y-4">
                @forelse($stats['operator_performance'] as $operator)
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $operator['operator'] }}</p>
                                <p class="text-xs text-gray-500">{{ $operator['count'] }} reports</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-green-700">{{ number_format($operator['avg_viability'], 1) }}%</p>
                                <p class="text-xs text-gray-500">avg viability</p>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-gray-500">Avg processing to freezing: {{ number_format($operator['avg_processing_minutes']) }} min</div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 p-6 text-sm text-gray-500">
                        No operator initials are available in the imported reports yet.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="chart-card p-6 chart-container lg:col-span-1" class="delay-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Participant Leaders</h3>
                    <p class="text-sm text-gray-500">Participants with the most imported reports</p>
                </div>
                <i data-feather="award" class="w-5 h-5 text-gray-400"></i>
            </div>

            <div class="space-y-4">
                @forelse($stats['participant_leaders'] as $participant)
                    <div class="rounded-xl bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $participant['participant'] }}</p>
                                <p class="text-xs text-gray-500">{{ $participant['count'] }} reports</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-orange-700">{{ number_format($participant['avg_viability'], 1) }}%</p>
                                <p class="text-xs text-gray-500">avg viability</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 p-6 text-sm text-gray-500">
                        Participant identifiers have not been populated yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    Chart.defaults.font.family = "'JetBrains Mono', monospace";
    Chart.defaults.color = '#6b7280';

    const viabilityBands = @json([$stats['viability_high'], $stats['viability_medium'], $stats['viability_low']]);

    const conditionLabels = @json($stats['condition_labels']);
    const conditionCounts = @json($stats['condition_counts']);
    const visitLabels = @json($stats['visit_labels']);
    const visitCounts = @json($stats['visit_counts']);
    const timelineLabels = @json($stats['timeline_labels']);
    const timelineCounts = @json($stats['timeline_counts']);
    const timelineAvgViability = @json($stats['timeline_avg_viability']);

    new Chart(document.getElementById('viabilityChart'), {
        type: 'doughnut',
        data: {
            labels: ['High (>=80%)', 'Medium (60-79%)', 'Low (<60%)'],
            datasets: [{
                data: viabilityBands,
                backgroundColor: ['#15803d', '#d97706', '#dc2626'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 18
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('conditionChart'), {
        type: 'bar',
        data: {
            labels: conditionLabels,
            datasets: [{
                label: 'Reports',
                data: conditionCounts,
                backgroundColor: ['#0f766e', '#c2410c', '#1d4ed8', '#64748b', '#15803d', '#dc2626'],
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: 'rgba(15, 23, 42, 0.06)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    new Chart(document.getElementById('visitChart'), {
        type: 'bar',
        data: {
            labels: visitLabels,
            datasets: [{
                label: 'Reports',
                data: visitCounts,
                backgroundColor: 'rgba(29, 78, 216, 0.82)',
                borderColor: '#1d4ed8',
                borderWidth: 1.5,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: 'rgba(15, 23, 42, 0.06)' }
                },
                y: {
                    grid: { display: false }
                }
            }
        }
    });

    new Chart(document.getElementById('timelineChart'), {
        type: 'bar',
        data: {
            labels: timelineLabels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Reports',
                    data: timelineCounts,
                    backgroundColor: 'rgba(194, 65, 12, 0.78)',
                    borderRadius: 8,
                    yAxisID: 'y'
                },
                {
                    type: 'line',
                    label: 'Avg viability %',
                    data: timelineAvgViability,
                    borderColor: '#0f766e',
                    backgroundColor: 'rgba(15, 118, 110, 0.12)',
                    pointBackgroundColor: '#0f766e',
                    pointRadius: 4,
                    tension: 0.35,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 18
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: 'rgba(15, 23, 42, 0.06)' },
                    title: {
                        display: true,
                        text: 'Reports'
                    }
                },
                y1: {
                    beginAtZero: true,
                    max: 100,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    title: {
                        display: true,
                        text: 'Viability %'
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    function refreshData(button) {
        const icon = button.querySelector('i');
        icon.style.animation = 'spin 1s linear';

        window.setTimeout(() => {
            icon.style.animation = '';
            window.location.reload();
        }, 700);
    }
</script>
@endpush
