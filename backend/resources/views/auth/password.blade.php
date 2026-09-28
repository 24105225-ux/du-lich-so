@extends('layouts.app')

@section('title', 'Đổi mật khẩu')

@section('content')
<section class="section">
    <div class="container container--narrow">
        <div class="form-card">
            <h1>
                Đổi mật khẩu
            </h1>

            <form
                method="POST"
                action="{{ route(
                    'password.update'
                ) }}"
            >
                @csrf

                <div class="form-group">
                    <label for="current_password">
                        Mật khẩu hiện tại
                    </label>

                    <input
                        id="current_password"
                        type="password"
                        name="current_password"
                        autocomplete="current-password"
                        required
                    >

                    @error('current_password')
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
                        Mật khẩu mới
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
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

                <div class="form-group">
                    <label for="password_confirmation">
                        Nhập lại mật khẩu mới
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
                    class="button"
                    type="submit"
                >
                    Cập nhật mật khẩu
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
