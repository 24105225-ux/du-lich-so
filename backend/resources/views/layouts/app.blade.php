<!DOCTYPE html>
<html
    lang="vi"
    data-theme="{{ request()->cookie('theme', 'sang') }}"
>
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield(
            'title',
            'Du lịch học đường - CSE703073'
        )
    </title>

    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
</head>

<body>
    <a
        class="skip-link"
        href="#noi-dung"
    >
        Bỏ qua điều hướng
    </a>

    <x-site-header />

    @if (session('status'))
        <div
            class="alert alert--success"
            role="status"
        >
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div
            class="alert alert--danger"
            role="alert"
        >
            <strong>
                Có lỗi cần kiểm tra:
            </strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <main id="noi-dung">
        @yield('content')
    </main>

    <x-site-footer />
    <script type="module" src="{{ asset('assets/app.js') }}"></script>
</body>
</html>
