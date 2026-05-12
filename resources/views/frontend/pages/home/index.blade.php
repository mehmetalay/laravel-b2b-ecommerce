@extends('frontend.layouts.app')

@section('content')
    <div id="hp-sliders" data-hp="sliders"></div>
    <div id="hp-categories" data-hp="categories"></div>
    <div id="hp-blocks" data-hp="blocks"></div>
    <div id="hp-campaigns" data-hp="campaigns"></div>
    <div id="hp-brands" data-hp="brands"></div>
@endsection

@section('js')
    <script src="{{ mix('js/frontend/modules/home/index.js') }}"></script>
    <script src="{{ mix('js/frontend/modules/cart/add-to-cart.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Homepage.init();
        });
    </script>
@endsection
