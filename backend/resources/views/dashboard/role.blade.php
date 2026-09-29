@extends('layouts.app')

@section(
    'title',
    $title . ' | CSE703073'
)

@section('content')
<section class="section">
    <div class="container">
        <span class="eyebrow">
            QUYỀN TRUY CẬP
        </span>

        <h1>
            {{ $title }}
        </h1>

        <p class="lead">
            Vai trò hiện tại:
            <strong>{{ $role }}</strong>
        </p>

        @if ($role === 'school')
            <div class="card">
                <div class="card__body">
                    <h2>
                        Nhà trường
                    </h2>

                    <p>
                        Quản lý lớp, đăng ký chương trình,
                        danh sách học sinh và quy trình
                        tổ chức chuyến đi.
                    </p>
                </div>
            </div>
        @elseif ($role === 'parent')
            <div class="card">
                <div class="card__body">
                    <h2>
                        Phụ huynh
                    </h2>

                    <p>
                        Theo dõi thông tin chuyến đi,
                        phiếu đồng ý và thông báo hành trình.
                    </p>
                </div>
            </div>
        @elseif ($role === 'organizer')
            <div class="card">
                <div class="card__body">
                    <h2>
                        Đơn vị tổ chức
                    </h2>

                    <p>
                        Quản lý chương trình trải nghiệm,
                        lịch trình và hồ sơ an toàn.
                    </p>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card__body">
                    <h2>
                        Quản trị
                    </h2>

                    <p>
                        Quản lý toàn bộ hệ thống.
                    </p>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
