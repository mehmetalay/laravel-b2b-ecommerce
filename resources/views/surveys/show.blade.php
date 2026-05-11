@extends('layouts.app')

@section('css')
    <style>
        /* Genel section */
        .survey-section {
            background-color: #f8f9fa;
            padding: 40px 0;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            font-family: 'Inter', sans-serif;
        }

        /* Container başlık ve açıklama */
        .survey-title h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .survey-title p {
            font-size: 1rem;
            color: #4b5563;
            line-height: 1.6;
        }

        /* Soru kutusu */
        .survey-question {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease-in-out;
        }

        .survey-question:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-color: #d1d5db;
        }

        /* Soru label */
        .survey-question label {
            font-weight: 500;
            color: #111827;
            display: block;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        /* Input text */
        .survey-question input[type="text"] {
            width: 100%;
            padding: 10px 12px;
            border-radius: 4px;
            border: 1px solid #d1d5db;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .survey-question input[type="text"]:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59,130,246,0.2);
        }

        /* Radio & Checkbox */
        .survey-question .form-check {
            margin-bottom: 8px;
        }

        .survey-question .form-check-input {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 1px solid #d1d5db;
            transition: all 0.2s;
        }

        .survey-question .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .survey-question .form-check-label {
            margin-left: 8px;
            font-size: 0.95rem;
            color: #374151;
        }

        /* Gönder butonu */
        .survey-submit-btn {
            background-color: #3b82f6;
            color: #ffffff;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            transition: all 0.3s;
        }

        .survey-submit-btn:hover {
            background-color: #2563eb;
            box-shadow: 0 4px 12px rgba(59,130,246,0.25);
            color: #ffffff;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .survey-section {
                padding: 30px 15px;
            }

            .survey-title h2 {
                font-size: 1.5rem;
            }

            .survey-question {
                padding: 15px;
            }
        }
    </style>
@endsection

@section('content')
    <section class="survey-section" x-data='surveyForm(@json($survey))'>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="survey-title">
                        <h2 x-text="survey.title"></h2>
                        <p x-text="survey.description"></p>
                    </div>
                </div>

                <form @submit.prevent="submitSurvey" id="surveyForm">
                    <template x-for="(q, index) in questions" :key="index">
                        <div class="survey-question card mb-4 shadow-sm p-3">
                            <label class="fw-bold fs-5 mb-2" x-text="q.question"></label>

                            <!-- Açıklama (seçim tipi) -->
                            <p class="text-muted small mb-3" x-show="q.type == 'single'">🔘 Lütfen birini seçiniz.</p>
                            <p class="text-muted small mb-3" x-show="q.type == 'multiple'">☑️ Birden fazla seçenek işaretleyebilirsiniz.</p>

                            <!-- Text -->
                            <template x-if="q.type == 'text'">
                                <input type="text" class="form-control" placeholder="Cevabınızı yazın..." x-model="answers[index]">
                            </template>

                            <!-- Single -->
                            <template x-if="q.type == 'single'">
                                <div class="mt-2">
                                    <template x-for="(opt,oIndex) in q.options" :key="oIndex">
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="radio" :name="'q'+index" :value="opt.id" x-model="answers[index]">
                                            <label class="form-check-label" x-text="opt.option_text"></label>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Multiple -->
                            <template x-if="q.type == 'multiple'">
                                <div class="mt-2">
                                    <template x-for="(opt,oIndex) in q.options" :key="oIndex">
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox" :value="opt.id" x-model="answers[index]">
                                            <label class="form-check-label" x-text="opt.option_text"></label>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <p class="text-danger small mt-2" x-show="errors['answers.' + index]" x-text="errors['answers.' + index][0]"></p>
                        </div>
                    </template>

                    <button type="submit" class="btn btn-animation proceed-btn fw-bold">Gönder</button>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.0/dist/cdn.min.js"></script>
    <script>
        function surveyForm(survey) {
            return {
                survey: survey,
                questions: survey.questions ?? [],
                answers: [],
                errors: {},

                init() {
                    this.questions.forEach((q,i) => {
                        this.answers[i] = q.type === 'multiple' ? [] : '';
                    });
                },

                submitSurvey() {
                    this.errors = {};

                    axiosRequest.post(
                        `/surveys/${this.survey.id}/submit`,
                        { answers: this.answers },
                        {
                            onSuccess: (res) => {
                                notify('success', 'Teşekkürler! Ankete katılımınız kaydedildi.');
                                setTimeout(() => window.location.href = '/', 2000);
                            },
                            onValidationError: (errors) => {
                                this.errors = errors;
                                notify('warning', 'Lütfen eksik soruları doldurunuz.');
                            }
                        }
                    );
                }
            }
        }
    </script>
@endsection
