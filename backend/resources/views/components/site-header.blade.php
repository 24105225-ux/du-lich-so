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

            @auth
                <a href="{{ route('dashboard') }}">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}">
                    Đăng nhập
                </a>
            @endauth
        </nav>

        <div class="header-actions">
            <form
                method="POST"
                action="{{ route('theme.switch') }}"
            >
                @csrf

                <input
                    type="hidden"
                    name="theme"
                    value="sang"
                >

                <button
                    type="submit"
                    class="button button--secondary"
                >
                    Sáng
                </button>
            </form>

            <form
                method="POST"
                action="{{ route('theme.switch') }}"
            >
                @csrf

                <input
                    type="hidden"
                    name="theme"
                    value="toi"
                >

                <button
                    type="submit"
                    class="button button--secondary"
                >
                    Tối
                </button>
            </form>

            @auth
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="button"
                    >
                        Đăng xuất
                    </button>
                </form>
            @endauth
        </div>
    </div>
</header>
