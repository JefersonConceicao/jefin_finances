@extends('layout.master')
    <div 
    id="nivel1" 
    class="modal fade text-left show" 
    tabindex="-1" 
    aria-labelledby="myModalLabel160"
    style="display:none; padding-right:17px"
    aria-modal="true"
    role="dialog"
    aria-hidden="true"
    data-bs-backdrop="static"
    >
        <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document"> 
            <div class="modal-content">
                @include('layout.modal')
            </div>
        </div>
    </div>

    <div 
        id="nivel2" 
        class="modal fade text-left show" 
        tabindex="-1" 
        aria-labelledby="myModalLabel160"
        style="display:none; padding-right:17px"
        aria-modal="true"
        role="dialog"
        aria-hidden="true"
        data-bs-backdrop="static"
    >
        <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document"> 
            <div class="modal-content">
                @include('layout.modal')
            </div>
        </div>
    </div>

@section('content')
   
    <div id="app">
        <div id="sidebar" class="active">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header">
                    <div class="d-flex justify-content-between">
                        <div class="logo">
                            <h4> Jefin Finances </h4>
                        </div>
                        <div class="toggler">
                            <a href="#" class="sidebar-hide d-xl-none d-block"> 
                                <i class="bi bi-x bi-middle"> </i> 
                            </a>
                        </div>
                    </div>
                </div>
                <div class="sidebar-menu">
                    @include('layout.sidebar')
                </div>
                <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
            </div>
        </div>
        <div id="main" class="layout-navbar">
            <header class="mb-3"> 
                <nav class="navbar navbar-expand navbar-light">
                    <div class="container-fluid">
                        <a href="#" class="burger-btn d-block">
                            <i class="bi bi-justify fs-3"> </i>
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            @include('layout.navbar')
                        </div>
                    </div>
                </nav>
            </header>
            <div id="main-content">
                <div class="page-heading">
                    <div class="page-title">
                        <div class="row">
                            <div class="col-12 col-md-6 order-md-1 order-last">
                                @yield('content_header')
                            </div>
                            <div class="col-12 col-md-6 order-md-2 order-first">
                                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                    @yield('breadcrumb')
                                </nav>
                            </div>
                        </div>
                    </div>
                    <section class="section">
                        @yield('content-page')
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection


