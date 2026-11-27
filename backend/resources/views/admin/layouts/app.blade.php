<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — ShopPulse Admin</title>

    <!-- Prevent dark-mode flash before CSS paints -->
    <script>
        (function () {
            if (localStorage.getItem('adminTheme') === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
        })();
    </script>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('admin/assets/images/favicon-32x32.png') }}" type="image/png" />

    <!-- Modern Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Plugins -->
    <link href="{{ asset('admin/assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin/assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin/assets/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet" />

    <!-- Loader -->
    <link href="{{ asset('admin/assets/css/pace.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('admin/assets/js/pace.min.js') }}"></script>

    <!-- Bootstrap & Core Template CSS -->
    <link href="{{ asset('admin/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/css/icons.css') }}" rel="stylesheet">

    <!-- Theme Style CSS -->
    <link rel="stylesheet" href="{{ asset('admin/assets/css/dark-theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/assets/css/semi-dark.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/assets/css/header-colors.css') }}" />

    <!-- Modern Admin UI Enhancements -->
    <link rel="stylesheet" href="{{ asset('admin/assets/css/admin-modern.css') }}" />

    @stack('styles')
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        @include('admin.layouts.sidebar')

        <!-- Topbar Header -->
        @include('admin.layouts.header')

        <!-- Main Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Mobile Sidebar Overlay -->
        <div class="overlay toggle-icon"></div>

        <!-- Back To Top Button -->
        <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>

        <!-- Footer -->
        @include('admin.layouts.footer')
    </div>

    <!-- Bootstrap JS -->
    <script src="{{ asset('admin/assets/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Plugins -->
    <script src="{{ asset('admin/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/metismenu/js/metisMenu.min.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('admin/assets/plugins/chartjs/js/Chart.min.js') }}"></script>
    <!-- App JS -->
    <script src="{{ asset('admin/assets/js/app.js') }}"></script>

    <!-- Persistent Light/Dark Theme Switcher -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggleBtn = document.getElementById('adminThemeToggle');
            var toggleIcon = document.getElementById('adminThemeIcon');

            function syncIcon() {
                if (!toggleIcon) return;
                var isDark = document.documentElement.classList.contains('dark-theme');
                toggleIcon.className = isDark ? 'bx bx-sun fs-5' : 'bx bx-moon fs-5';
            }

            syncIcon();

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    var isDark = document.documentElement.classList.toggle('dark-theme');
                    localStorage.setItem('adminTheme', isDark ? 'dark' : 'light');
                    syncIcon();
                });
            }
        });
    </script>

    @include('admin.components.ajax-form-handler')
    @include('admin.components.ajax-delete-file-handler')

    @stack('scripts')
</body>

</html>
