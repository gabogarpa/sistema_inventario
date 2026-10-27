<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WompiService
{
    public function configured(): bool
    {
        return filled(config('services.wompi.client_id'))
            && filled(config('services.wompi.client_secret'));
    }

    public function createPaymentLink(Order $order): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('Faltan WOMPI_CLIENT_ID y WOMPI_CLIENT_SECRET en el archivo .env.');
        }

        $configuration = [
            'emailsNotificacion' => $order->customer_email,
            'notificarTransaccionCliente' => true,
            'esMontoEditable' => false,
            'esCantidadEditable' => false,
            'cantidadPorDefecto' => 1,
        ];

        if ($this->hasPublicAppUrl()) {
            $configuration += [
                'urlRedirect' => route('payment.result', $order),
                'urlRetorno' => route('checkout.create'),
                'urlWebhook' => route('wompi.webhook'),
            ];
        }

        $response = Http::acceptJson()
            ->withoutVerifying() // <--- Agregado para omitir el error cURL 60 en local
            ->withToken($this->accessToken())
            ->timeout(20)
            ->retry(2, 300)
            ->post(rtrim(config('services.wompi.api_url'), '/').'/EnlacePago', [
                'identificadorEnlaceComercio' => $order->reference,
                'monto' => (float) $order->total,
                'nombreProducto' => 'Pedido '.$order->reference,
                'configuracion' => $configuration,
                'limitesDeUso' => [
                    'cantidadMaximaPagosExitosos' => 1,
                    'cantidadMaximaPagosFallidos' => 5,
                ],
            ])
            ->throw()
            ->json();

        if (blank($response['urlEnlace'] ?? null)) {
            throw new RuntimeException('Wompi no devolvio el enlace de pago esperado.');
        }

        return $response;
    }

    public function validWebhook(string $body, ?string $signature): bool
    {
        $secret = (string) config('services.wompi.client_secret');
        return $secret !== '' && filled($signature) && hash_equals(
            hash_hmac('sha256', $body, $secret),
            strtolower((string) $signature)
        );
    }

    public function validLinkRedirect(array $data): bool
    {
        $secret = (string) config('services.wompi.client_secret');
        $received = (string) ($data['hash'] ?? '');
        $plain = ($data['identificadorEnlaceComercio'] ?? '')
            .($data['idTransaccion'] ?? '')
            .($data['idEnlace'] ?? '')
            .($data['monto'] ?? '');

        return $secret !== '' && $received !== ''
            && hash_equals(hash_hmac('sha256', $plain, $secret), strtolower($received));
    }

    private function accessToken(): string
    {
        return Cache::remember('wompi.access_token', now()->addMinutes(50), function () {
            $response = Http::asForm()
                ->acceptJson()
                ->withoutVerifying() // <--- Agregado para omitir el error cURL 60 en local al pedir el token
                ->timeout(20)
                ->post(
                    config('services.wompi.token_url'),
                    [
                        'grant_type' => 'client_credentials',
                        'audience' => 'wompi_api',
                        'client_id' => config('services.wompi.client_id'),
                        'client_secret' => config('services.wompi.client_secret'),
                    ]
                )->throw()->json();

            $token = $response['access_token'] ?? null;
            if (! is_string($token) || $token === '') {
                throw new RuntimeException('Wompi no devolvio un token de acceso.');
            }

            return $token;
        });
    }

    private function hasPublicAppUrl(): bool
    {
        $url = parse_url((string) config('app.url'));
        $host = strtolower((string) ($url['host'] ?? ''));

        if (! in_array($url['scheme'] ?? null, ['http', 'https'], true) || $host === '') {
            return false;
        }

        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.test')) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) !== false;
        }

        return true;
    }
}