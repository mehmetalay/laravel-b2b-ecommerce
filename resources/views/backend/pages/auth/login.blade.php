@extends('backend.layouts.auth')

@section('content')
    <div class="col-xl-4 col-lg-4 col-md-4">
        <div class="d-flex flex-column justify-content-between h-100 center-area">
            <a>Giriş</a>
            <div>
                <p class="text-dark">Hoşgeldiniz</p>
                <h2 class="text-black">Admin Paneli</h2>
            </div>
            <p class="text-dark d-none d-md-block m-0">
                <a href="#" target="_blank">Developed by Mehmet Alay</a>
            </p>
        </div>
    </div>
    <div class="col-xl-5 col-lg-5 col-md-6">
        <div class="d-flex flex-column justify-content-between h-100 right-area">
            <p></p>
            <form id="login-form">
                <div class="login-three-inputs mt-5">
                    <x-backend.input id="username" type="text" placeholder="Kullanıcı Adı" autofocus />
                    <i class="las la-user-alt"></i>
                </div>
                <div class="login-three-inputs mt-3">
                    <x-backend.input id="password" type="password" placeholder="Şifre" />
                    <i class="las la-lock"></i>
                </div>
                <div class="login-three-inputs check mt-4">
                    <input type="checkbox" class="inp-cbx" id="cbx" name="remember" style="display: none">
                    <label class="cbx" for="cbx">
                        <span>
                            <svg width="12px" height="10px" viewBox="0 0 12 10">
                                <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                            </svg>
                        </span>
                        <span class="font-13 text-muted">Beni hatırla</span>
                    </label>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-4">
                    <button type="submit" class="text-white btn bg-gradient-primary">Giriş Yap <i class="las la-key ml-2"></i></button>
                    <a class="font-13 text-primary" href="/">Şifremi unuttum</a>
                </div>
            </form>
            <div class="login-three-social social-logins mt-4">
                <div class="social-btn bg-gradient-primary">
                    <a href="#" class="fb-btn">
                        <i class="lab la-facebook-f"></i>
                    </a>
                </div>
                <div class="social-btn bg-gradient-primary">
                    <a href="#" class="twitter-btn">
                        <i class="lab la-twitter"></i>
                    </a>
                </div>
                <div class="social-btn bg-gradient-primary">
                    <a href="#" class="google-btn">
                        <i class="lab la-google-plus"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $('#login-form').on('submit', function(e) {
            e.preventDefault();
            var $this = $(this).find('button');
			var text = $($this).html();
			$($this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span> Giriş yapılıyor...');
            $.post('{{ route('admin.login.form') }}', $(this).serialize(), function(data) {
                if (data.success) {
                    window.location.href = data.success
                } else {
                    $($this).html(text).prop('disabled', false);
                    notify((data.error ? 'error' : 'warning'), (data.error ? data.error : data.warning));
                }
            }, 'JSON')
            .fail(function(jqXHR, textStatus, errorThrown) {
                $($this).html(text).prop('disabled', false);
                notify('error', 'İstek sırasında bir hata oluştu. Lütfen site yöneticisiyle iletişime geçin.');
            });
        });
    </script>
@endsection
