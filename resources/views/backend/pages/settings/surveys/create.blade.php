@extends('backend.layouts.app')

@section('css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.0/dist/cdn.min.js"></script>
@endsection

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => route('admin.surveys.index'), 'label' => 'Anketler'],
            ['url' => route('admin.surveys.create'), 'label' => 'Yeni'],
        ]">
        <li class="nav-item">
            <button type="submit" class="btn btn-success dash-btn mr-2 data-form-button" form="survey-form" data-ajax-submit>
                <i class="las la-save"></i> Kaydet
            </button>
            <a href="{{ route('admin.surveys.index') }}" class="btn btn-info dash-btn">
                <i class="las la-list"></i> Listeye Dön
            </a>
        </li>
    </x-backend.breadcrumb>

    <div x-data="surveyForm()" class="layout-px-spacing">
        <div class="row layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-9 layout-spacing">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Yeni Anket</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <form id="survey-form" data-ajax-form action="{{ route('admin.surveys.store') }}" method="POST">
                            <div class="form-group">
                                <label class="col-form-label">Başlık <span class="text-danger">*</span></label>
                                <input type="text" name="title" x-model="title" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="col-form-label">Açıklama</label>
                                <textarea name="description" x-model="description" class="form-control" rows="6"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label class="col-form-label">Tarih Kullanılsın mı?</label>
                                        <span class="switch">
                                            <label>
                                                <input type="checkbox" name="use_dates" x-model="use_dates">
                                                <span></span>
                                            </label>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6 col-xl-5">
                                    <div class="form-group" x-show="use_dates == 1">
                                        <label class="col-form-label" for="start_date">Anket Tarihi (İlk ve Son)</label>
                                        <div class="input-group">
                                            <input type="date" name="start_at" x-model="start_at" class="form-control">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">/</span>
                                            </div>
                                            <input type="date" name="end_at" x-model="end_at" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Sorular -->
                            <template x-for="(question, qIndex) in questions" :key="qIndex">
                                <div class="border p-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <h5>Soru <span x-text="qIndex+1"></span></h5>
                                        <button type="button" class="btn btn-danger btn-sm" @click="removeQuestion(qIndex)">Sil</button>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-form-label">Soru Metni</label>
                                        <input type="text" class="form-control" :name="`questions[${qIndex}][question]`" x-model="question.question">
                                    </div>

                                    <div class="form-group">
                                        <label class="col-form-label">Tip</label>
                                        <select class="form-control" :name="`questions[${qIndex}][type]`" x-model="question.type">
                                            <option value="single">Tekli Seçim</option>
                                            <option value="multiple">Çoklu Seçim</option>
                                            <option value="text">Metin</option>
                                        </select>
                                    </div>

                                    <template x-if="question.type != 'text'">
                                        <div>
                                            <h6>Seçenekler</h6>
                                            <template x-for="(option, oIndex) in question.options" :key="oIndex">
                                                <div class="d-flex mb-2">
                                                    <input type="text" class="form-control mr-2" :name="`questions[${qIndex}][options][${oIndex}][option_text]`" x-model="option.option_text" placeholder="Seçenek">
                                                    <button type="button" class="btn btn-danger btn-sm" @click="removeOption(qIndex, oIndex)">Sil</button>
                                                </div>
                                            </template>
                                            <button type="button" class="btn btn-secondary btn-sm" @click="addOption(qIndex)">+ Seçenek Ekle</button>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <button type="button" class="btn btn-primary mb-3" @click="addQuestion()">+ Soru Ekle</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-3 layout-spacing">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Durumu</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <div class="form-group row">
                            <div class="col-3">
                                <span class="switch align-items-start">
                                    <label>
                                        <input type="checkbox" name="is_active" x-model="is_active" form="survey-form" checked>
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label" id="status-label-text">Aktif</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function surveyForm() {
            return {
                title: '',
                description: '',
                use_dates: 0,
                start_at: '',
                end_at: '',
                is_active: 1,
                questions: [],

                addQuestion() {
                    this.questions.push({ question: '', type: 'single', options: [] });
                },

                removeQuestion(index) {
                    this.questions.splice(index, 1);
                },

                addOption(qIndex) {
                    this.questions[qIndex].options.push({ option_text: '' });
                },

                removeOption(qIndex, oIndex) {
                    this.questions[qIndex].options.splice(oIndex, 1);
                }
            }
        }
    </script>
@endsection
