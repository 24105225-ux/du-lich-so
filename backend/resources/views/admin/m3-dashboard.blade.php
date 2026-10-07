@extends('layouts.app')

@section('title', 'M3 Admin Dashboard')

@section('content')
<div class="container" style="max-width:1200px;margin:40px auto;padding:20px;">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;">
        <div>
            <h1>M3 Admin Dashboard</h1>
            <p style="color:#667085;">
                Theo dõi đơn hàng, thanh toán thử nghiệm và doanh thu.
            </p>
        </div>

        <a href="{{ route('m3-admin-dashboard.export') }}"
           style="text-decoration:none;">
            Export CSV
        </a>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-top:24px;">

        <div style="padding:22px;border:1px solid #e4e7ec;border-radius:14px;">
            <div style="color:#667085;">Tổng đơn hàng</div>
            <div style="font-size:32px;font-weight:700;">
                {{ $metrics['total'] }}
            </div>
        </div>

        <div style="padding:22px;border:1px solid #e4e7ec;border-radius:14px;">
            <div style="color:#667085;">Đơn đã thanh toán</div>
            <div style="font-size:32px;font-weight:700;">
                {{ $metrics['paid'] }}
            </div>
        </div>

        <div style="padding:22px;border:1px solid #e4e7ec;border-radius:14px;">
            <div style="color:#667085;">Đơn chờ thanh toán</div>
            <div style="font-size:32px;font-weight:700;">
                {{ $metrics['pending'] }}
            </div>
        </div>

        <div style="padding:22px;border:1px solid #e4e7ec;border-radius:14px;">
            <div style="color:#667085;">Doanh thu</div>
            <div style="font-size:32px;font-weight:700;">
                {{ number_format($metrics['revenue'], 0, ',', '.') }} đ
            </div>
        </div>

    </div>

    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;margin-top:24px;">

        <div style="padding:22px;border:1px solid #e4e7ec;border-radius:14px;">
            <h2>Biểu đồ 1 – Trạng thái đơn</h2>

            @php
                $maxStatus = max(
                    max($metrics['statusChart']),
                    1
                );
            @endphp

            @foreach($metrics['statusChart'] as $status => $value)
                <div style="margin:16px 0;">

                    <div style="display:flex;justify-content:space-between;">
                        <span>{{ strtoupper($status) }}</span>
                        <strong>{{ $value }}</strong>
                    </div>

                    <div style="height:14px;background:#eef2f6;border-radius:99px;overflow:hidden;margin-top:6px;">
                        <div style="height:100%;width:{{ ($value / $maxStatus) * 100 }}%;background:currentColor;border-radius:99px;"></div>
                    </div>

                </div>
            @endforeach
        </div>

        <div style="padding:22px;border:1px solid #e4e7ec;border-radius:14px;">
            <h2>Biểu đồ 2 – Doanh thu 7 ngày</h2>

            @php
                $maxRevenue = max(
                    max($metrics['dailyRevenue']),
                    1
                );
            @endphp

            @foreach($metrics['dailyRevenue'] as $date => $value)
                <div style="margin:16px 0;">

                    <div style="display:flex;justify-content:space-between;">
                        <span>{{ $date }}</span>
                        <strong>
                            {{ number_format($value, 0, ',', '.') }} đ
                        </strong>
                    </div>

                    <div style="height:14px;background:#eef2f6;border-radius:99px;overflow:hidden;margin-top:6px;">
                        <div style="height:100%;width:{{ ($value / $maxRevenue) * 100 }}%;background:currentColor;border-radius:99px;"></div>
                    </div>

                </div>
            @endforeach
        </div>

    </div>

</div>
@endsection