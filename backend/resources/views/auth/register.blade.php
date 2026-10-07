@extends('layouts.app')

@section('title', 'Đăng ký | Du lịch học đường')

@section('content')
<section class="section">
    <div class="container container--narrow">
        <div class="form-card">
            <span class="eyebrow">
                CSE703073
            </span>

            <h1>
                Đăng ký tài khoản
            </h1>

            <p>
                Tài khoản đăng ký mới mặc định thuộc vai trò phụ huynh.
            </p>

            <form
                method="POST"
                action="{{ route('register.store') }}"
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
                        autocomplete="email"
                        required
                    >

                    @error('email')
                        <p class="field-error" role="alert">
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
                        autocomplete="new-password"
                        required
                    >

                    @error('password')
                        <p class="field-error" role="alert">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">
                        Xác nhận mật khẩu
                    </label>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="button"
                >
                    Đăng ký
                </button>
            </form>

            <p>
                Đã có tài khoản?
                <a href="{{ route('login') }}">
                    Đăng nhập
                </a>
            </p>
        </div>
    </div>
</section>
@endsection