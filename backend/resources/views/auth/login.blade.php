@extends('layouts.app')

@section('title', 'Đăng nhập | Du lịch học đường')

@section('content')
<section class="section">
    <div class="container container--narrow">
        <div class="form-card">
            <span class="eyebrow">
                CSE703073
            </span>

            <h1>
                Đăng nhập hệ thống
            </h1>

            <p>
                Sử dụng tài khoản mô phỏng của dự án
                để truy cập đúng vai trò.
            </p>

            <form
                method="POST"
                action="{{ route('login.store') }}"
            >
                @csrf

                <div class="form-group">
                    <label for="email">
                        Email
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        required
                    >

                    @error('email')
                        <p
                            class="field-error"
                            role="alert"
                        >
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">
                        Mật khẩu
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >

                    @error('password')
                        <p
                            class="field-error"
                            role="alert"
                        >
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="button"
                >
                    Đăng nhập
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
