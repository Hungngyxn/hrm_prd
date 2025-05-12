@extends('layouts.app')

@section('head')
    @yield('_head')
@endsection

@section('content')
<div class="wrapper">
    @include('components.sidebar')
    <div class="content">
        @yield('_content')
    </div>
</div>

@yield('_script')
@endsection
