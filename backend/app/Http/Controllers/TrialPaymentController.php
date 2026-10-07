<?php

namespace App\Http\Controllers;

use App\Models\ClassRegistration;
use App\Models\Order;
use App\Services\TrialPaymentService;
use Illuminate\Http\Request;
use Throwable;

class TrialPaymentController extends Controller
{
    public function __construct(
        private TrialPaymentService $payments
    ) {
    }

    private function allowUser(): void
    {
        $user = auth()->user();

        abort_unless($user, 401);

        abort_if(
            $user->status !== 'active',
            403,
            'Tài khoản không hoạt động.'
        );
    }

    private function authorizeOrder(Order $order): void
    {
        $user = auth()->user();

        abort_unless($user, 401);

        if (
            $user->role !== 'admin' &&
            (int)$order->user_id !== (int)$user->id
        ) {
            abort(403);
        }
    }

    public function createFromRegistration(
        ClassRegistration $registration
    ) {
        $this->allowUser();

        $order = $this->payments->createOrder(
            auth()->user(),
            $registration
        );

        return redirect()->route(
            'trial-payments.show',
            $order
        );
    }

    public function show(Order $order)
    {
        $this->allowUser();
        $this->authorizeOrder($order);

        $order->load([
            'registration.schedule',
            'payments' => fn ($q) => $q->latest('id'),
        ]);

        return view(
            'payments.trial',
            compact('order')
        );
    }

    public function success(Order $order)
    {
        $this->allowUser();
        $this->authorizeOrder($order);

        try {

            $order = $this->payments->pay($order);

            return redirect()
                ->route(
                    'trial-payments.show',
                    $order
                )
                ->with(
                    'success',
                    'Thanh toán thử nghiệm thành công.'
                );

        } catch (Throwable $e) {

            return back()
                ->withErrors(
                    ['payment' => $e->getMessage()]
                );
        }
    }

    public function cancel(Order $order)
    {
        $this->allowUser();
        $this->authorizeOrder($order);

        try {

            $this->payments->cancelPayment($order);

            return redirect()
                ->route(
                    'trial-payments.show',
                    $order
                )
                ->with(
                    'success',
                    'Giao dịch thử nghiệm đã bị hủy. Đơn vẫn ở trạng thái pending để có thể thử lại.'
                );

        } catch (Throwable $e) {

            return back()
                ->withErrors(
                    ['payment' => $e->getMessage()]
                );
        }
    }

    public function failed(Order $order)
    {
        $this->allowUser();
        $this->authorizeOrder($order);

        try {

            $this->payments->failPayment($order);

            return redirect()
                ->route(
                    'trial-payments.show',
                    $order
                )
                ->with(
                    'success',
                    'Đã mô phỏng thanh toán thất bại.'
                );

        } catch (Throwable $e) {

            return back()
                ->withErrors(
                    ['payment' => $e->getMessage()]
                );
        }
    }

    public function refund(Order $order)
    {
        $this->allowUser();
        $this->authorizeOrder($order);

        try {

            $this->payments->refund($order);

            return redirect()
                ->route(
                    'trial-payments.show',
                    $order
                )
                ->with(
                    'success',
                    'Hoàn tiền thử nghiệm thành công.'
                );

        } catch (Throwable $e) {

            return back()
                ->withErrors(
                    ['payment' => $e->getMessage()]
                );
        }
    }
}