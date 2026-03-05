<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $app_settings['app_name'] ?? 'CPB System')</title>

    @if(isset($app_settings['app_favicon']) && $app_settings['app_favicon'])
    <link rel="icon" href="{{ Storage::url($app_settings['app_favicon']) }}?v={{ time() }}" type="image/x-icon">
    @endif

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    @stack('styles')
</head>

<body class="hold-transition layout-top-nav layout-navbar-fixed">
    <div class="wrapper">

        @include('layouts.navbar')

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                @yield('breadcrumb')
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="icon fas fa-check"></i> {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="icon fas fa-ban"></i> {{ session('error') }}
                    </div>
                    @endif

                    @yield('content')
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; {{ date('Y') }} CPB Management System.</strong>
            All rights reserved.
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <script src="{{ asset('js/app.js') }}?v={{ time() }}"></script>

    <script>
        $(document).ready(function() {
            
            // 1. FITUR AUTO-CLOSE MENU MOBILE
            // Menyembunyikan dropdown menu hp saat user klik di luar area navbar
            $(document).click(function(event) {
                var clickover = $(event.target);
                // Cek apakah menu sedang terbuka (memiliki class 'show')
                var isOpened = $("#navbarCollapse").hasClass("show");
                
                // Jika terbuka dan user mengklik elemen di luar navbar, maka tutup menu
                if (isOpened === true && !clickover.closest('.navbar').length) {
                    $("button.navbar-toggler").click();
                }
            });

            // 2. FITUR AUTO-HIDE NAVBAR (SMART SCROLL)
            // Menyembunyikan navbar saat scroll ke bawah, munculkan saat scroll ke atas
            var lastScrollTop = 0;
            var navbar = $('.main-header');
            var navbarHeight = navbar.outerHeight();

            $(window).scroll(function() {
                var st = $(this).scrollTop();
                
                // Pastikan user sudah scroll melewati tinggi navbar
                if (st > navbarHeight) {
                    if (st > lastScrollTop) {
                        // Scroll ke bawah -> Sembunyikan Navbar ke atas layar
                        navbar.css({
                            'transform': 'translateY(-100%)',
                            'transition': 'transform 0.3s ease-in-out'
                        });
                    } else {
                        // Scroll ke atas -> Munculkan kembali Navbar
                        navbar.css({
                            'transform': 'translateY(0)',
                            'transition': 'transform 0.3s ease-in-out'
                        });
                    }
                }
                lastScrollTop = st;
            });
        });
    </script>
    @stack('scripts')
</body>

</html>