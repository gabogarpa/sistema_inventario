<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\WompiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function result(Request $request, Order $order, WompiService $wompi)
    {
        return view('payment.result', [
            'order' => $order->fresh(['items', 'payments']),
        ]);
    }

    public function demo(Order $order)
    {
        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            
            $locked->payments()->create([
                'provider' => 'wompi-demo',
                'external_id' => 'demo-' . uniqid(),
                'status' => 'approved',
                'amount' => $locked->total,
                'payload' => ['demo' => true],
            ]);

            if ($locked->status !== 'paid') {
                $locked->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }
        });

        return redirect()->route('payment.result', $order)->with('success', 'Pago de demostración completado con éxito.');
    }

    public function webhook(Request $request)
    {
        $raw = $request->getContent();
        $received = (string) $request->header('wompi_hash');
        $expected = hash_hmac('sha256', $raw, config('services.wompi.client_secret'));

        abort_unless($received !== '' && hash_equals($expected, $received), 401);

        $event = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
        $reference = data_get($event, 'EnlacePago.IdentificadorEnlaceComercio');
        $order = Order::where('reference', $reference)->firstOrFail();
        
        $approved = data_get($event, 'ResultadoTransaccion') === 'ExitosaAprobada';
        $sameAmount = abs((float) data_get($event, 'Monto') - (float) $order->total) < 0.001;

        DB::transaction(function () use ($order, $event, $approved, $sameAmount) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $transactionId = data_get($event, 'IdTransaccion');

            $payment = $locked->payments()->firstOrNew([
                'provider' => 'wompi', 
                'external_id' => $transactionId,
            ]);

            $payment->fill([
                'status' => $approved && $sameAmount ? 'approved' : 'rejected',
                'amount' => data_get($event, 'Monto'), 
                'payload' => $event,
            ])->save();

            if ($approved && $sameAmount && $locked->status !== 'paid') {
                $locked->update(['status' => 'paid', 'paid_at' => now()]);
            }
        });

        return response()->json(['received' => true]);
    }
}