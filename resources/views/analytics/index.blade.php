@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
<style>
    :root {
        --chart-primary: #f97316;
        --chart-secondary: #0ea5e9;
        --chart-success: #10b981;
        --chart-warning: #f59e0b;
        --chart-danger: #ef4444;
        --chart-purple: #8b5cf6;
        --chart-pink: #ec4899;
    }

    .analytics-header {
        font-family: 'Playfair Display', serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .stat-card {
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, transparent 0%, rgba(249, 115, 22, 0.1) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .stat-card:hover::before {
        opacity: 1;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .chart-container {
        position: relative;
        animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-number {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
    }

    .pulse-dot {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }

    .shimmer {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }

    .gradient-border {
        position: relative;
        background: white;
        padding: 1px;
        border-radius: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f97316 100%);
    }

    .gradient-border-inner {
        background: white;
        border-radius: calc(1rem - 1px);
    }

    /* Custom scrollbar for charts */
    .chart-scroll::-webkit-scrollbar {
        height: 6px;
    }

    .chart-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .chart-scroll::-webkit-scrollbar-thumb {
        background: var(--chart-primary);
        border-radius: 10px;
    }

    .chart-scroll::-webkit-scrollbar-thumb:hover {
        background: #ea580c;
    }
</style>
@endpush

@section('content')
@php
    $user = Auth::user();
@endphp

<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-black analytics-header mb-2">
                    Analytics Dashboard
                </h1>
                <p class="text-gray-600 flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full pulse-dot"></span>
                    Real-time PBMC processing insights
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="refreshData()" 
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                    <i data-feather="refresh-cw" class="w-4 h-4"></i>
                    Refresh
                </button>
                <a href="{{ route('pbmc.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-pbmc text-white rounded-lg hover:bg-orange-700 transition-all">
                    <i data-feather="list" class="w-4 h-4"></i>
                    View Records
                </a>
            </div>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Total Records -->
        <div class="stat-card bg-white rounded-xl border p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-blue-50 rounded-lg">
                    <i data-feather="database" class="w-6 h-6 text-blue-600"></i>
                </div>
                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">ALL TIME</span>
            </div>
            <div class="stat-number text-3xl font-bold text-gray-900 mb-1">
                {{ number_format($stats['total_records']) }}
            </div>
            <p class="text-sm text-gray-600">Total PBMC Records</p>
            <div class="mt-3 flex items-center gap-2 text-xs">
                <span class="text-green-600 font-semibold">↑ {{ $stats['acrn_count'] }}</span>
                <span class="text-gray-500">from ACRN</span>
            </div>
        </div>

        <!-- Average Viability -->
        <div class="stat-card bg-white rounded-xl border p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-green-50 rounded-lg">
                    <i data-feather="activity" class="w-6 h-6 text-green-600"></i>
                </div>
                <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded-full">AVERAGE</span>
            </div>
            <div class="stat-number text-3xl font-bold text-gray-900 mb-1">
                {{ number_format($stats['avg_viability'], 1) }}%
            </div>
            <p class="text-sm text-gray-600">Average Viability</p>
            <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full transition-all duration-1000" 
                     style="width: {{ $stats['avg_viability'] }}%"></div>
            </div>
        </div>

        <!-- Total Cell Count -->
        <div class="stat-card bg-white rounded-xl border p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-purple-50 rounded-lg">
                    <i data-feather="hexagon" class="w-6 h-6 text-purple-600"></i>
                </div>
                <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-2 py-1 rounded-full">TOTAL</span>
            </div>
            <div class="stat-number text-3xl font-bold text-gray-900 mb-1">
                {{ number_format($stats['total_cells'] / 1000000, 1) }}M
            </div>
            <p class="text-sm text-gray-600">Total Cells Processed</p>
            <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                <i data-feather="trending-up" class="w-3 h-3"></i>
                {{ number_format($stats['total_cells']) }} cells
            </div>
        </div>

        <!-- Success Rate -->
        <div class="stat-card bg-white rounded-xl border p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="p-3 bg-orange-50 rounded-lg">
                    <i data-feather="check-circle" class="w-6 h-6 text-orange-600"></i>
                </div>
                <span class="text-xs font-semibold text-orange-600 bg-orange-50 px-2 py-1 rounded-full">≥80%</span>
            </div>
            <div class="stat-number text-3xl font-bold text-gray-900 mb-1">
                {{ number_format($stats['viable_percentage'], 1) }}%
            </div>
            <p class="text-sm text-gray-600">High Viability Rate</p>
            <div class="mt-3 flex items-center gap-2 text-xs">
                <span class="text-orange-600 font-semibold">{{ $stats['viable_count'] }}/{{ $stats['total_records'] }}</span>
                <span class="text-gray-500">samples</span>
            </div>
        </div>

    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <!-- Viability Distribution -->
        <div class="gradient-border">
            <div class="gradient-border-inner bg-white rounded-xl p-6 chart-container">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Viability Distribution</h3>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        High (≥80%)
                        <span class="w-3 h-3 rounded-full bg-orange-500 ml-2"></span>
                        Medium (60-79%)
                        <span class="w-3 h-3 rounded-full bg-red-500 ml-2"></span>
                        Low (<60%)
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="viabilityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Processing Methods -->
        <div class="gradient-border">
            <div class="gradient-border-inner bg-white rounded-xl p-6 chart-container" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Counting Methods</h3>
                    <select id="methodFilter" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-pbmc focus:border-transparent">
                        <option value="all">All Time</option>
                        <option value="month">This Month</option>
                        <option value="week">This Week</option>
                    </select>
                </div>
                <div class="h-64">
                    <canvas id="methodChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- Study Distribution -->
        <div class="gradient-border lg:col-span-2">
            <div class="gradient-border-inner bg-white rounded-xl p-6 chart-container" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Records by Study</h3>
                    <i data-feather="bar-chart-2" class="w-5 h-5 text-gray-400"></i>
                </div>
                <div class="h-80 chart-scroll overflow-x-auto">
                    <canvas id="studyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Performers -->
        <div class="gradient-border">
            <div class="gradient-border-inner bg-white rounded-xl p-6 chart-container" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Top Studies</h3>
                    <i data-feather="award" class="w-5 h-5 text-yellow-500"></i>
                </div>
                <div class="space-y-4">
                    @foreach($stats['top_studies'] as $index => $study)
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm
                                {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : ($index === 1 ? 'bg-gray-100 text-gray-700' : 'bg-orange-100 text-orange-700') }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $study->study_name }}</p>
                                <p class="text-xs text-gray-500">{{ $study->count }} records</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-green-600">{{ number_format($study->avg_viability, 1) }}%</p>
                                <p class="text-xs text-gray-500">avg viability</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    <!-- Timeline Chart -->
    <div class="gradient-border mb-6">
        <div class="gradient-border-inner bg-white rounded-xl p-6 chart-container" style="animation-delay: 0.4s">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Processing Timeline</h3>
                    <p class="text-sm text-gray-500">Monthly PBMC processing volume</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="changeTimelineView('6m')" class="timeline-btn px-3 py-1.5 text-xs font-medium rounded-lg transition-all">6M</button>
                    <button onclick="changeTimelineView('1y')" class="timeline-btn px-3 py-1.5 text-xs font-medium rounded-lg transition-all active">1Y</button>
                    <button onclick="changeTimelineView('all')" class="timeline-btn px-3 py-1.5 text-xs font-medium rounded-lg transition-all">All</button>
                </div>
            </div>
            <div class="h-96">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Additional Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Automated vs Manual -->
        <div class="bg-white rounded-xl border p-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-4">Processing Split</h4>
            <div class="space-y-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-gray-600">Automated</span>
                        <span class="text-xs font-bold text-gray-900">{{ $stats['automated_count'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full transition-all duration-1000" 
                             style="width: {{ ($stats['automated_count'] / max($stats['total_records'], 1)) * 100 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-gray-600">Manual Count</span>
                        <span class="text-xs font-bold text-gray-900">{{ $stats['manual_count'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-500 h-2 rounded-full transition-all duration-1000" 
                             style="width: {{ ($stats['manual_count'] / max($stats['total_records'], 1)) * 100 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl border p-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-4">Recent Activity</h4>
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-xs">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span class="text-gray-600">Last 7 days:</span>
                    <span class="font-bold text-gray-900">{{ $stats['last_7_days'] }} records</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <span class="text-gray-600">Last 30 days:</span>
                    <span class="font-bold text-gray-900">{{ $stats['last_30_days'] }} records</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                    <span class="text-gray-600">This year:</span>
                    <span class="font-bold text-gray-900">{{ $stats['this_year'] }} records</span>
                </div>
            </div>
        </div>

        <!-- Data Quality -->
        <div class="bg-white rounded-xl border p-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-4">Data Quality</h4>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-600">Complete Records</span>
                    <span class="text-xs font-bold text-green-600">{{ number_format(($stats['complete_records'] / max($stats['total_records'], 1)) * 100, 1) }}%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-600">With Comments</span>
                    <span class="text-xs font-bold text-blue-600">{{ number_format(($stats['with_comments'] / max($stats['total_records'], 1)) * 100, 1) }}%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-600">High Quality</span>
                    <span class="text-xs font-bold text-purple-600">{{ number_format(($stats['viable_count'] / max($stats['total_records'], 1)) * 100, 1) }}%</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Chart.js default configuration
    Chart.defaults.font.family = "'JetBrains Mono', monospace";
    Chart.defaults.color = '#6b7280';

    // Viability Distribution Chart
    const viabilityCtx = document.getElementById('viabilityChart').getContext('2d');
    const viabilityChart = new Chart(viabilityCtx, {
        type: 'doughnut',
        data: {
            labels: ['High (≥80%)', 'Medium (60-79%)', 'Low (<60%)'],
            datasets: [{
                data: [
                    {{ $stats['viability_high'] }},
                    {{ $stats['viability_medium'] }},
                    {{ $stats['viability_low'] }}
                ],
                backgroundColor: [
                    '#10b981',
                    '#f97316',
                    '#ef4444'
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 1000
            }
        }
    });

    // Processing Methods Chart
    const methodCtx = document.getElementById('methodChart').getContext('2d');
    const methodChart = new Chart(methodCtx, {
        type: 'bar',
        data: {
            labels: ['Automated', 'Manual Count'],
            datasets: [{
                label: 'Records',
                data: [{{ $stats['automated_count'] }}, {{ $stats['manual_count'] }}],
                backgroundColor: [
                    'rgba(14, 165, 233, 0.8)',
                    'rgba(139, 92, 246, 0.8)'
                ],
                borderColor: [
                    '#0ea5e9',
                    '#8b5cf6'
                ],
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: { precision: 0 }
                },
                x: {
                    grid: { display: false }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });

    // Study Distribution Chart
    const studyCtx = document.getElementById('studyChart').getContext('2d');
    const studyChart = new Chart(studyCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($stats['study_labels']) !!},
            datasets: [{
                label: 'Records',
                data: {!! json_encode($stats['study_counts']) !!},
                backgroundColor: 'rgba(249, 115, 22, 0.8)',
                borderColor: '#f97316',
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: { precision: 0 }
                },
                y: {
                    grid: { display: false }
                }
            },
            animation: {
                duration: 1200,
                easing: 'easeOutQuart'
            }
        }
    });

    // Timeline Chart
    const timelineCtx = document.getElementById('timelineChart').getContext('2d');
    const timelineChart = new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($stats['timeline_labels']) !!},
            datasets: [
                {
                    label: 'Total Records',
                    data: {!! json_encode($stats['timeline_counts']) !!},
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#f97316'
                },
                {
                    label: 'High Viability',
                    data: {!! json_encode($stats['timeline_viable']) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#10b981'
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
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: { precision: 0 }
                },
                x: {
                    grid: { display: false }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });

    // Timeline view buttons
    document.querySelectorAll('.timeline-btn').forEach(btn => {
        btn.classList.add('bg-gray-100', 'text-gray-600');
        if (btn.classList.contains('active')) {
            btn.classList.remove('bg-gray-100', 'text-gray-600');
            btn.classList.add('bg-pbmc', 'text-white');
        }
    });

    function changeTimelineView(period) {
        document.querySelectorAll('.timeline-btn').forEach(btn => {
            btn.classList.remove('bg-pbmc', 'text-white', 'active');
            btn.classList.add('bg-gray-100', 'text-gray-600');
        });
        event.target.classList.remove('bg-gray-100', 'text-gray-600');
        event.target.classList.add('bg-pbmc', 'text-white', 'active');
        
        // Reload data for the selected period
        console.log('Loading timeline data for:', period);
    }

    function refreshData() {
        const icon = event.target.querySelector('i') || event.target.parentElement.querySelector('i');
        icon.style.animation = 'spin 1s linear';
        setTimeout(() => {
            icon.style.animation = '';
            window.location.reload();
        }, 1000);
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</script>
@endpush