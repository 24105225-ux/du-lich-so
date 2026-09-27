@extends('layouts.app')

@section('title', 'Trang chủ | Du lịch học đường')

@section('meta_description')
Nền tảng quản lý chương trình trải nghiệm giáo dục cho nhà trường, phụ huynh và đơn vị tổ chức.
@endsection

@section('content')
<section class="hero">
    <div class="container hero__grid">
        <div>
            <span class="eyebrow">
                ĐT-17 • CSE703073
            </span>

            <h1>
                Nền tảng du lịch học đường
                và chương trình trải nghiệm giáo dục
            </h1>

            <p class="hero__text">
                Kết nối nhà trường, phụ huynh và đơn vị tổ chức
                trong quá trình xây dựng, đăng ký và quản lý
                chương trình trải nghiệm giáo dục.
            </p>

            <div class="hero__actions">
                <a
                    class="button"
                    href="{{ route('programs.index') }}"
                >
                    Xem chương trình
                </a>

                <a
                    class="button button--secondary"
                    href="{{ route('registrations.create') }}"
                >
                    Đăng ký theo lớp
                </a>
            </div>
        </div>

        <div class="hero__card">
            <strong>
                Chương trình đang công khai
            </strong>

            <span class="hero__number">
                {{ $programs->total() }}
            </span>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__heading">
            <h2>
                Chương trình nổi bật
            </h2>

            <a href="{{ route('programs.index') }}">
                Xem tất cả
            </a>
        </div>

        <div class="card-grid">
            @forelse ($programs as $program)
                <x-program-card
                    :program="$program"
                />
            @empty
                <div class="empty-state">
                    Chưa có chương trình được công khai.
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
