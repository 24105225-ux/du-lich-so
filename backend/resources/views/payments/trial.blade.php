@extends('layouts.app')

@section('title', 'Thanh toán thử nghiệm')

@section('content')
<div class="container" style="max-width: 920px; margin: 40px auto; padding: 20px;">

    @if(session('success'))
        <div style="padding:14px;border-radius:10px;background:#e8f7ee;margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="padding:14px;border-radius:10px;background:#fdecec;margin-bottom:16px;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div style="display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;">
        <div>
            <h1 style="margin-bottom:8px;">Thanh toán thử nghiệm M3</h1>
            <p style="margin:0;color:#667085;">
                Sandbox nội bộ – không kết nối cổng thanh toán thật.
            </p>
        </div>

        <a href="{{ route('dashboard') }}"
           style="text-decoration:none;">
            Dashboard
        </a>
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:24px;">

        <div style="padding:20px;border:1px solid #e4e7ec;border-radius:14px;">
            <div style="color:#667085;">Mã đơn</div>
            <strong>{{ $order->order_no }}</strong>
        </div>

        <div style="padding:20px;border:1px solid #e4e7ec;border-radius:14px;">
            <div style="color:#667085;">Số tiền</div>
            <strong>{{ number_format((float)$order->amount, 0, ',', '.') }} đ</strong>
        </div>

        <div style="padding:20px;border:1px solid #e4e7ec;border-radius:14px;">
            <div style="color:#667085;">Trạng thái đơn</div>
            <strong>{{ strtoupper($order->status) }}</strong>
        </div>

        <div style="padding:20px;border:1px solid #e4e7ec;border-radius:14px;">
            <div style="color:#667085;">Registration</div>
            <strong>#{{ $order->class_registration_id }}</strong>
        </div>

    </div>

    <div style="margin-top:28px;padding:22px;border:1px solid #e4e7ec;border-radius:14px;">
        <h2 style="margin-top:0;">State machine</h2>

        <div style="font-size:16px;line-height:1.8;">
            <code>PENDING</code>
            →
            <code>PAID</code>
            →
            <code>REFUNDED</code>
        </div>

        <div style="font-size:16px;line-height:1.8;">
            <code>PENDING</code>
            →
            <code>CANCELLED</code>
        </div>

        <p style="color:#667085;margin-bottom:0;">
            Hủy/thất bại ở bước thử nghiệm không làm biến đổi inventory và
            giữ đơn ở trạng thái pending để có thể thử lại.
        </p>
    </div>

    @if($order->status === 'pending')
        <div style="margin-top:28px;display:flex;gap:12px;flex-wrap:wrap;">

            <form method="POST"
                  action="{{ route('trial-payments.success', $order) }}">
                @csrf
                <button type="submit"
                        style="padding:12px 18px;border:0;border-radius:10px;cursor:pointer;">
                    Thanh toán thành công
                </button>
            </form>

            <form method="POST"
                  action="{{ route('trial-payments.failed', $order) }}">
                @csrf
                <button type="submit"
                        style="padding:12px 18px;border:0;border-radius:10px;cursor:pointer;">
                    Mô phỏng thất bại
                </button>
            </form>

            <form method="POST"
                  action="{{ route('trial-payments.cancel', $order) }}">
                @csrf
                <button type="submit"
                        style="padding:12px 18px;border:0;border-radius:10px;cursor:pointer;">
                    Hủy giao dịch
                </button>
            </form>

            <form method="POST"
                  action="{{ route('trial-payments.refund', $order) }}">
                @csrf
                <button type="submit"
                        style="padding:12px 18px;border:0;border-radius:10px;cursor:pointer;">
                    Thử refund trước thanh toán
                </button>
            </form>

        </div>
    @endif

    @if($order->status === 'paid')
        <div style="margin-top:28px;">
            <form method="POST"
                  action="{{ route('trial-payments.refund', $order) }}">
                @csrf

                <button type="submit"
                        style="padding:12px 18px;border:0;border-radius:10px;cursor:pointer;">
                    Refund thử nghiệm
                </button>
            </form>
        </div>
    @endif

    <div style="margin-top:28px;">
        <h2>Lịch sử giao dịch</h2>

        @forelse($order->payments as $payment)
            <div style="padding:14px 0;border-bottom:1px solid #eaecf0;">
                <strong>{{ strtoupper($payment->status) }}</strong>
                —
                {{ $payment->transaction_code }}
                —
                {{ number_format((float)$payment->amount, 0, ',', '.') }} đ
            </div>
        @empty
            <p style="color:#667085;">
                Chưa có giao dịch.
            </p>
        @endforelse
    </div>

</div>
@endsection