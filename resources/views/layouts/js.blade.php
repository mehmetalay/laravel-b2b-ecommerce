<script src="/assets/js/jquery-3.6.0.min.js"></script>
<script src="/assets/js/jquery-ui.min.js"></script>
<script src="/assets/js/bootstrap/bootstrap.bundle.min.js"></script>
<script src="/assets/js/bootstrap/bootstrap-notify.min.js"></script>
<script src="/assets/js/bootstrap/popper.min.js"></script>
<script src="/assets/js/feather/feather.min.js"></script>
<script src="/assets/js/feather/feather-icon.js"></script>
<script src="/assets/js/lazysizes.min.js"></script>
<script src="/assets/js/slick/slick.js"></script>
<script src="/assets/js/slick/slick-animation.min.js"></script>
<script src="{{ versioned_asset('assets/js/slick/custom_slick.js') }}"></script>
{{-- <script src="/assets/js/auto-height.js"></script> --}}
<script src="{{ versioned_asset('assets/js/quantity.js') }}"></script>
{{-- <script src="/assets/js/wow.min.js"></script>
<script src="/assets/js/custom-wow.js"></script> --}}
<script src="/assets/js/script.js"></script>
<script src="/assets/js/sweetalert2.js"></script>
<script src="/assets/js/lazy-load.js"></script>
<script src="/assets/js/select2.min.js"></script>
<script src="{{ versioned_asset('assets/js/asey/inputmask.min.js') }}"></script>
<script src="{{ versioned_asset('assets/js/asey/form-helper.js') }}"></script>
<script src="{{ versioned_asset('assets/js/app.js') }}"></script>
<x-asey.js />
<script type="module">
    import { Fancybox } from "/assets/js/fancybox.esm.js";
    Fancybox.bind('[data-fancybox="gallery"]', {
        Images: {
            Panzoom: {
                maxScale: 3,
            },
        },
    });
</script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const pushFlashNotify = (type, message) => {
        if (!message) {
            return;
        }

        notify(type, message);
    };

    pushFlashNotify('error', @json(session('error')));
    pushFlashNotify('success', @json(session('success')));
    pushFlashNotify('warning', @json(session('warning')));

    const swalSuccessMessage = @json(session('swal-success'));
    if (swalSuccessMessage) {
        Swal.fire({
            title: '{{ trans('translations.swal_js.basarili') }}',
            text: swalSuccessMessage,
            icon: 'success',
            showConfirmButton: false,
            timer: 3000,
        });
    }

    const orderSuccessHtml = @json(session()->get('order-success'));
    if (orderSuccessHtml) {
        Swal.fire({
            title: '{{ trans('translations.swal_js.basarili') }}',
            html: orderSuccessHtml,
            icon: 'success',
            showConfirmButton: false,
            showConfirmButton: true,
            confirmButtonText: '{{ trans('translations.swal_js.tamam') }}'
        });
    }
</script>

@if (Route::is('payments.page') || Route::is('payments.payment-link'))
    <script src="/assets/js/imask.min.js"></script>
    <script>
        window.onload = function() {
            new IMask(document.getElementById('credit_card_number'), {
                mask: '0000 0000 0000 0000'
            });
            new IMask(document.getElementById('credit_card_exp_date'), {
                mask: 'MM{/}YY',
                groups: {
                    YY: new IMask.MaskedPattern.Group.Range([24, 99]),
                    MM: new IMask.MaskedPattern.Group.Range([1, 12]),
                }
            });
            new IMask(document.getElementById('cvc'), {
                mask: '0000',
            });
        }

        function paymentPostMessage(data) {
            setTimeout(function() {
                paymentModalName.modal('hide');
                paymentModalBody.html('');

                if (data.type == 'success') {
                    Swal.fire({
                        title: '{{ trans('translations.payment.page.islem_basarili') }}',
                        text: data.message,
                        icon: data.type,
                        timer: 5000,
                        timerProgressBar: true,
                        willClose: () => {
                            window.location.href = data.url;
                        }
                    });
                } else {
                    Swal.fire({
                        title: '{{ trans('translations.payment.page.islem_basarisiz') }}',
                        text: data.message,
                        icon: data.type,
                        confirmButtonText: '{{ trans('translations.payment.page.tamam') }}'
                    });
                }
            }, 5000);
        }

        $(window).on('beforeunload', function() {
            if (paymentModalName.hasClass('show')) {
                return '{{ trans('translations.payment.page.odeme_tamamlanamadi_bu_sayfadan_ayrilmak_istediginize_emin_misiniz') }}';
            }
        });
    </script>
@endif

@if (Route::is('index') && auth('web')->check() && auth('web')->user()->role === 'dealer')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const survey = @json($survey);
            const hasParticipated = @json($hasParticipated);

            if (!survey || hasParticipated) return;

            const closedUntilKey = 'survey_closed_until_' + survey.id + '_' + "{{ auth('web')->user()->id }}";
            const closedUntil = localStorage.getItem(closedUntilKey);
            if (closedUntil && new Date(closedUntil) > new Date()) return;

            const surveyModalEl = document.getElementById('surveyModal');
            if (!surveyModalEl) return;

            const surveyModal = new bootstrap.Modal(surveyModalEl);

            surveyModal.show();

            const closeBtn = document.getElementById('closeSurveyBtn');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    surveyModal.hide();
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    localStorage.setItem(closedUntilKey, tomorrow.toISOString());
                });
            }

            const goBtn = document.getElementById('goSurveyBtn');
            if (goBtn) {
                goBtn.setAttribute('href', '/surveys/' + survey.id);
            }
        });
    </script>
@endif
@yield('js')
{{-- @stack('scripts') --}}
