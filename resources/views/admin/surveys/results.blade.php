@extends('admin.layouts.app')

@section('css')
    <style>
        .text-answers-container {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 0.5rem;
            background-color: #f9f9f9;
        }

        .text-answers-container::-webkit-scrollbar {
            width: 8px;
        }

        .text-answers-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .text-answers-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .text-answers-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => route('admin.surveys.index'), 'label' => 'Anket'],
            ['url' => route('admin.surveys.index'), 'label' => 'Sonuçlar'],
        ]">
        <li class="nav-item">
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-lg-12">
                <div class="statbox widget box box-shadow">
                    <div class="widget-header">
                        <h4>"{{ $survey->title }}" Sonuçları</h4>
                        <p class="ml-3">Toplam Katılımcı: <span class="font-15 text-danger">{{ $totalParticipants }}</span></p>
                    </div>
                    <div class="widget-content widget-content-area">
                        @foreach($questionsStats as $qIndex => $qStat)
                            <div class="survey-result mb-5">
                                <h6>{{ $qStat['question'] }}</h6>

                                @if($qStat['type'] === 'text')
                                    <div class="text-answers-container mt-2">
                                        <ul class="list-group">
                                            @foreach($qStat['stats']['text_answers'] as $answer)
                                                <li class="list-group-item">{{ $answer }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <div class="row">
                                        <div class="col-md-3">
                                            <canvas id="doughnut-chart-{{ $qIndex }}" style="height: 300px;"></canvas>
                                        </div>
                                    </div>

                                    <script>
                                        document.addEventListener('DOMContentLoaded', function () {
                                            const labels{{ $qIndex }} = @json($qStat['stats']['options']->pluck('option'));
                                            const data{{ $qIndex }} = @json($qStat['stats']['options']->pluck('percent'));
                                            const counts{{ $qIndex }} = @json($qStat['stats']['options']->pluck('count'));

                                            const colors{{ $qIndex }} = [
                                                '#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                                            ];

                                            // Doughnut
                                            new Chart(document.getElementById('doughnut-chart-{{ $qIndex }}'), {
                                                type: 'doughnut',
                                                data: {
                                                    labels: labels{{ $qIndex }},
                                                    datasets: [{
                                                        data: data{{ $qIndex }},
                                                        backgroundColor: colors{{ $qIndex }},
                                                        borderWidth: 1
                                                    }]
                                                },
                                                options: {
                                                    plugins: {
                                                        tooltip: {
                                                            callbacks: {
                                                                label: function(context) {
                                                                    const i = context.dataIndex;
                                                                    return `${labels{{ $qIndex }}[i]}: ${counts{{ $qIndex }}[i]} kişi (%${data{{ $qIndex }}[i]})`;
                                                                }
                                                            }
                                                        },
                                                        legend: { position: 'bottom' }
                                                    }
                                                }
                                            });

                                            // // Pie
                                            // new Chart(document.getElementById('pie-chart-{{ $qIndex }}'), {
                                            //     type: 'pie',
                                            //     data: {
                                            //         labels: labels{{ $qIndex }},
                                            //         datasets: [{
                                            //             data: data{{ $qIndex }},
                                            //             backgroundColor: colors{{ $qIndex }},
                                            //             borderWidth: 1
                                            //         }]
                                            //     },
                                            //     options: {
                                            //         plugins: {
                                            //             tooltip: {
                                            //                 callbacks: {
                                            //                     label: function(context) {
                                            //                         const i = context.dataIndex;
                                            //                         return `${labels{{ $qIndex }}[i]}: ${counts{{ $qIndex }}[i]} kişi (%${data{{ $qIndex }}[i]})`;
                                            //                     }
                                            //                 }
                                            //             },
                                            //             legend: { position: 'bottom' }
                                            //         }
                                            //     }
                                            // });
                                        });
                                    </script>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
@endsection
