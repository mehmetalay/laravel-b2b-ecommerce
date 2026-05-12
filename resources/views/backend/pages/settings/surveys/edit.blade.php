@extends('backend.layouts.app')

@section('css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.0/dist/cdn.min.js"></script>
@endsection

@section('content')
    <x-backend.breadcrumb :items="[
            ['url' => route('admin.surveys.index'), 'label' => 'Anketler'],
            ['url' => route('admin.surveys.edit', $survey->id), 'label' => 'Düzenle'],
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

    <div x-data='surveyForm(@json($surveyJson))' class="layout-px-spacing">
        <div class="row layout-top-spacing switch-outer-container">
            <div class="col-12 col-xl-9 layout-spacing">
                <div class="statbox widget box box-shadow mb-4">
                    <div class="widget-header">
                        <h4>Anket Düzenle</h4>
                    </div>
                    <div class="widget-content widget-content-area">
                        <form id="survey-form" data-ajax-form action="{{ route('admin.surveys.update', $survey->id) }}" method="POST">
                            @method('PUT')
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
                                        <label class="col-form-label">Anket Tarihi (İlk ve Son)</label>
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
                            <!-- Bilgi -->
                            @if ($survey->answers()->distinct('dealer_id')->count())
                                <p class="text-danger"><strong>Dikkat:</strong> Bu anket için katılımcılar mevcut. Soruları düzenleyemezsiniz. Soruları düzenlerseniz, katılımcıların cevapları etkilenebilir.</p>
                            @else
                                <p class="text-success">Bu anket için henüz katılımcı yok. Soruları düzenleyebilirsiniz.</p>
                            @endif

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
                                        <input type="text" class="form-control"
                                               :name="`questions[${qIndex}][question]`"
                                               x-model="question.question">
                                    </div>

                                    <div class="form-group">
                                        <label class="col-form-label">Tip</label>
                                        <select class="form-control"
                                                :name="`questions[${qIndex}][type]`"
                                                x-model="question.type">
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
                                                    <input type="text" class="form-control mr-2"
                                                           :name="`questions[${qIndex}][options][${oIndex}][option_text]`"
                                                           x-model="option.option_text" placeholder="Seçenek">
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
                                        <input type="checkbox" name="is_active" x-model="is_active" form="survey-form">
                                        <span></span>
                                    </label>
                                </span>
                            </div>
                            <label class="col-9 col-form-label">Aktif</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function surveyForm(survey = null) {
            return {
                title: survey?.title ?? '',
                description: survey?.description ?? '',
                use_dates: survey?.use_dates ?? 0,
                start_at: survey?.start_at ?? '',
                end_at: survey?.end_at ?? '',
                is_active: survey?.is_active ?? 1,
                questions: survey?.questions.map(q => ({
                    question: q.question,
                    type: q.type,
                    sort_order: q.sort_order,
                    options: q.options.map(o => ({ option_text: o.option_text, sort_order: o.sort_order }))
                })) ?? [],

                addQuestion() {
                    this.questions.push({ question: '', type: 'single', options: [], sort_order: this.questions.length + 1 });
                },

                removeQuestion(index) {
                    this.questions.splice(index, 1);
                    this.questions.forEach((q, i) => q.sort_order = i + 1);
                },

                addOption(qIndex) {
                    const options = this.questions[qIndex].options;
                    options.push({ option_text: '', sort_order: options.length + 1 });
                },

                removeOption(qIndex, oIndex) {
                    const options = this.questions[qIndex].options;
                    options.splice(oIndex, 1);
                    options.forEach((o, i) => o.sort_order = i + 1);
                }
            }
        }
    </script>
@endsection
