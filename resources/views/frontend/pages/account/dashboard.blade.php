@extends('frontend.layouts.app')

@section('content')
<section class="user-dashboard-section section-b-space">
    <div class="container-fluid-lg">
        <div class="row">

            <div class="col-12">
                <div class="dashboard-right-sidebar">
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-dashboard" role="tabpanel"
                            aria-labelledby="pills-dashboard-tab">
                            <div class="dashboard-home">
                                <div class="title">
                                    <h2>Hesabım</h2>
                                </div>

                                <div class="dashboard-user-name">
                                    <h6 class="text-content"><b class="text-title">{{ auth('web')->check() ? auth('web')->user()->name : (auth('subdealer')->check() ? auth('subdealer')->user()->name : '') }}</b></h6>
                                </div>

                                <div class="total-box">
                                    <div class="row g-sm-4 g-3">
                                        <div class="col-xxl-2 col-lg-3 col-md-4 col-sm-6">
                                            <div class="totle-contain">
                                                <div class="totle-detail">
                                                    <h5>Cari Bakiyem</h5>
                                                    <h3>{{ number_format($balance, 2) . ' ' . $currency }}</h3>
                                                    <small><a href="/rapor/musteri-ekstresi">Listeye git..</a></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xxl-2 col-lg-3 col-md-4 col-sm-6">
                                            <div class="totle-contain">
                                                <div class="totle-detail">
                                                    <h5>Siparişlerim</h5>
                                                    <h3>{{ number_format($orderTotal, 2) . ' ' . $currency }}</h3>
                                                    <small><a href="/orders">Listeye git..</a></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xxl-2 col-lg-3 col-md-4 col-sm-6">
                                            <div class="totle-contain">
                                                <div class="totle-detail">
                                                    <h5>Ödemelerim</h5>
                                                    <h3>{{ number_format($paymentTotal, 2) . ' TL' }}</h3>
                                                    <small><a href="/payments">Listeye git..</a></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xxl-2 col-lg-3 col-md-4 col-sm-6">
                                            <div class="totle-contain">
                                                <div class="totle-detail">
                                                    <h5>Hesabım</h5>
                                                    <h3>&nbsp;</h3>
                                                    <small><a href="javascript:;" data-bs-toggle="modal" data-bs-target="#editProfile">Düzenle & Şifre Değiştir</a></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xxl-2 col-lg-3 col-md-4 col-sm-6">
                                            <div class="totle-contain">
                                                <div class="totle-detail">
                                                    <h5>Adreslerim</h5>
                                                    <h3>&nbsp;</h3>
                                                    <small><a href="{{ route('addresses.index') }}">Listeye git..</a></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-xxl-2 col-lg-3 col-md-4 col-sm-6">
                                            <div class="totle-contain">
                                                <div class="totle-detail">
                                                    <h5>Ödeme Yap</h5>
                                                    <h3>&nbsp;</h3>
                                                    <small><a href="/payments/page">Ödeme Sayfası</a></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('js')
    <script>
        document.addEventListener('click', function (e) {

            const button = e.target.closest('[data-action="update-password"]');
            if (!button) return;

            setLoading(button, true);

            const passwordInput = document.querySelector('[data-password-input]');
            const passwordError = document.querySelector('[data-password-error]');

            passwordInput.classList.remove('is-invalid');
            passwordError.innerText = '';

            axiosRequest.put('/account/profile', {
                password: passwordInput.value
            }, {
                onSuccess: (response) => {
                    notify('success', response.message || 'Şifre başarıyla güncellendi');
                    passwordInput.value = '';
                    setLoading(button, false);
                },

                onValidationError: (errors) => {
                    if (errors.password) {
                        passwordInput.classList.add('is-invalid');
                        passwordError.innerText = errors.password[0];
                    }
                    setLoading(button, false);
                },

                onError: () => {
                    notify('error', 'Şifre güncellenemedi');
                    setLoading(button, false);
                }
            });
        });
    </script>
@endsection