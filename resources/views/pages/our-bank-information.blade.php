@extends('layouts.app')

@section('content')
    <section class="breadscrumb-section pt-0">
        <div class="container-fluid-lg">
            <div class="row">
                <div class="col-12">
                    <div class="breadscrumb-contain">
                        <h2>{{ trans('translations.menu.banka_bilgilerimiz') }}</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="/">
                                        <i class="fa-solid fa-house"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ trans('translations.menu.banka_bilgilerimiz') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="fresh-vegetable-section section-lg-space">
        <div class="container-fluid-lg">
            <div class="row gx-xl-5 gy-xl-0 g-3">
                <div class="col-12">
                    <div class="fresh-contain">
                        <div>
                            @if (app()->getLocale() == 'tr')
                                <div class="review-title mb-4">
                                    <h3><strong>ÜNVANIMIZ:</strong> ÖZDOĞAN HIRDAVAT SAN.VE TİC. LTD. ŞTİ.</h3>
                                </div>
                                <div class="table-responsive">
                                    <table class="table order-tab-table">
                                        <thead>
                                            <tr>
                                                <th>Banka</th>
                                                <th>Şube Bilgileri</th>
                                                <th>TL Hesap No</th>
                                                <th>IBAN No</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><img src="{{ image_url('halkbank.svg', 'bank_logo') }}" alt="Halkbank" width="200"></td>
                                                <td>PERPA-862</td>
                                                <td>10260455</td>
                                                <td>TR15 0001 2009 8620 0010 2604 55</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('ziraatbankasi.svg', 'bank_logo') }}" alt="Ziraat Bankası" width="200"></td>
                                                <td>PERPA 1969</td>
                                                <td>63349976-5001</td>
                                                <td>TR78 0001 0019 6963 3499 7650 01</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('vakifbank.svg', 'bank_logo') }}" alt="VakıfBank" width="200"></td>
                                                <td>PERPA 323</td>
                                                <td>158007304528020</td>
                                                <td>TR26 0001 5001 5800 7304 5280 20</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('ziraatkatilim.svg', 'bank_logo') }}" alt="Ziraat Katılım" width="200"></td>
                                                <td>ÇAĞLAYAN 195</td>
                                                <td>1422199-3</td>
                                                <td>TR70 0020 9000 0142 2199 0000 03</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('teb.svg', 'bank_logo') }}" alt="TEB" width="150"></td>
                                                <td>PERPA 127</td>
                                                <td>34571602</td>
                                                <td>TR75 0003 2000 0000 0034 5716 02</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('yapikredi.svg', 'bank_logo') }}" alt="Yapı Kredi" width="200"></td>
                                                <td>PERPA 744</td>
                                                <td>41320859</td>
                                                <td>TR85 0006 7010 0000 0041 3208 59</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('garanti.svg', 'bank_logo') }}" alt="Garanti BBVA" width="200"></td>
                                                <td>BEYOĞLU TİCARİ 1671</td>
                                                <td>6290956</td>
                                                <td>TR22 0006 2001 6710 0006 2909 56</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('qnb.svg', 'bank_logo') }}" alt="QNB Finansbank" width="150"></td>
                                                <td>PERPA 888</td>
                                                <td>60547560</td>
                                                <td>TR84 0011 1000 0000 0060 5475 60</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('isbank.svg', 'bank_logo') }}" alt="İş Bankası" width="200"></td>
                                                <td>PERPA 1188</td>
                                                <td>231361</td>
                                                <td>TR95 0006 4000 0011 1880 2313 61</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('akbank.svg', 'bank_logo') }}" alt="Akbank" width="200"></td>
                                                <td>ÇAĞLAYAN 352</td>
                                                <td>22547</td>
                                                <td>TR03 0004 6003 5288 8000 0225 47</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('denizbank.svg', 'bank_logo') }}" alt="Denizbank" width="200"></td>
                                                <td>TOPÇULAR 352</td>
                                                <td>2140949</td>
                                                <td>TR63 0013 4000 0021 4094 9000 01</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                            @if (app()->getLocale() == 'en')
                                <div class="review-title mb-4">
                                    <h3><strong>OUR TITLE:</strong> ÖZDOĞAN HIRDAVAT SAN.VE TİC. LTD. ŞTİ.</h3>
                                </div>
                                <div class="table-responsive">
                                    <table class="table order-tab-table">
                                        <thead>
                                            <tr>
                                                <th>Bank</th>
                                                <th>Branch Information</th>
                                                <th>TL Account No</th>
                                                <th>IBAN No</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><img src="{{ image_url('halkbank.svg', 'bank_logo') }}" alt="Halkbank" width="200"></td>
                                                <td>PERPA-862</td>
                                                <td>10260455</td>
                                                <td>TR15 0001 2009 8620 0010 2604 55</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('ziraatbankasi.svg', 'bank_logo') }}" alt="Ziraat Bankası" width="200"></td>
                                                <td>PERPA 1969</td>
                                                <td>63349976-5001</td>
                                                <td>TR78 0001 0019 6963 3499 7650 01</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('vakifbank.svg', 'bank_logo') }}" alt="VakıfBank" width="200"></td>
                                                <td>PERPA 323</td>
                                                <td>158007304528020</td>
                                                <td>TR26 0001 5001 5800 7304 5280 20</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('ziraatkatilim.svg', 'bank_logo') }}" alt="Ziraat Katılım" width="200"></td>
                                                <td>ÇAĞLAYAN 195</td>
                                                <td>1422199-3</td>
                                                <td>TR70 0020 9000 0142 2199 0000 03</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('teb.svg', 'bank_logo') }}" alt="TEB" width="150"></td>
                                                <td>PERPA 127</td>
                                                <td>34571602</td>
                                                <td>TR75 0003 2000 0000 0034 5716 02</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('yapikredi.svg', 'bank_logo') }}" alt="Yapı Kredi" width="200"></td>
                                                <td>PERPA 744</td>
                                                <td>41320859</td>
                                                <td>TR85 0006 7010 0000 0041 3208 59</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('garanti.svg', 'bank_logo') }}" alt="Garanti BBVA" width="200"></td>
                                                <td>BEYOĞLU TİCARİ 1671</td>
                                                <td>6290956</td>
                                                <td>TR22 0006 2001 6710 0006 2909 56</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('qnb.svg', 'bank_logo') }}" alt="QNB Finansbank" width="150"></td>
                                                <td>PERPA 888</td>
                                                <td>60547560</td>
                                                <td>TR84 0011 1000 0000 0060 5475 60</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('isbank.svg', 'bank_logo') }}" alt="İş Bankası" width="200"></td>
                                                <td>PERPA 1188</td>
                                                <td>231361</td>
                                                <td>TR95 0006 4000 0011 1880 2313 61</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('akbank.svg', 'bank_logo') }}" alt="Akbank" width="200"></td>
                                                <td>ÇAĞLAYAN 352</td>
                                                <td>22547</td>
                                                <td>TR03 0004 6003 5288 8000 0225 47</td>
                                            </tr>
                                            <tr>
                                                <td><img src="{{ image_url('denizbank.svg', 'bank_logo') }}" alt="Denizbank" width="200"></td>
                                                <td>TOPÇULAR 352</td>
                                                <td>2140949</td>
                                                <td>TR63 0013 4000 0021 4094 9000 01</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
