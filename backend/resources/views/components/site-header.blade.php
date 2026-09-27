<header class="site-header">
    <div class="container nav-wrap">
        <a
            class="brand"
            href="{{ route('home') }}"
        >
            <span class="brand__title">
                Du lịch học đường
            </span>

            <span class="brand__subtitle">
                CSE703073
            </span>
        </a>

        <nav
            class="main-nav"
            aria-label="Điều hướng chính"
        >
            <a href="{{ route('home') }}">
                Trang chủ
            </a>

            <a href="{{ route('programs.index') }}">
                Chương trình
            </a>

            <a href="{{ route('registrations.create') }}">
                Đăng ký theo lớp
            </a>
        </nav>
    </div>
</header>
