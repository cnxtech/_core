@extends('codex::layouts.codex-base')

@push('header')
    <div class="responsive-sidebar-nav">
        <a href="#" class="toggle-slide menu-link btn">&#9776;</a>
    </div>
    @section('menu-versions')
        @include('codex::partials.header.versions')
    @show

    @section('menu-projects')
        {!! $codex->projects->renderMenu() !!}
    @show
@endpush


@push('content')

    <a class="sidebar-toggle" data-action='sidebar-toggle' title='Toggle sidebar'><i class="fa fa-list"></i></a>
    @section('menu-sidebar')
        {!! $codex->projects->renderSidebar() !!}
    @show

    @section('breadcrumb')
        @parent
        @include('codex::partials.breadcrumb')
    @show

    <article class="content @yield('articleClass', '')" data-layout="article">
        @yield('content')
    </article>
@endpush

@push('footer')
    @section('scroll-to-top')
    <a href="#" class="scrollToTop"></a>
    @show
    @section('footer')
    <footer class="main" data-layout="footer">
        <p>Copyright &copy; {{ config('codex.display_name') }}.</p>
    </footer>
    @show
@endpush
