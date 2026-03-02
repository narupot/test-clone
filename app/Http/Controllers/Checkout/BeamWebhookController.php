<?php

namespace App\Http\Controllers\Checkout;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\ApiLog;
use App\Order;

class BeamWebhookController extends Controller
{
    /**
     * Handle Beam Webhook events
     */
    public function handle(Request $request)
    {
        try {
            // ===== 1️⃣ อ่าน Header Event & Signature =====
            $beamEvent = $request->header('X-Beam-Event');
            if (!$beamEvent) {
                Log::warning('Beam Webhook: Missing X-Beam-Event header');
                return response()->json(['error' => 'Missing event header'], 400);
            }

            $signature = $request->header('X-Beam-Signature');
            if (!$signature) {
                Log::warning('Beam Webhook: Missing X-Beam-Signature header');
                return response()->json(['error' => 'Missing signature'], 400);
            }

            Log::info('Beam Webhook received', ['event' => $beamEvent]);

            // ===== 2️⃣ ตรวจสอบความถูกต้องของ Signature =====
            if (!$this->verifySignature($request, $signature)) {
                Log::warning('Beam Webhook: Invalid signature');
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            Log::info('Beam Webhook: Signature verified ✅');

            // ===== 3️⃣ แปลง JSON Payload =====
            $payload = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Beam Webhook: JSON Decode Error', [
                    'error' => json_last_error_msg(),
                ]);
                return response()->json(['error' => 'Invalid JSON payload'], 400);
            }

            // ===== 4️⃣ จัดการตามประเภท Event =====
            switch (strtolower($beamEvent)) {
                case 'charge.succeeded':
                    $chargeId = $payload['chargeId'] ?? null;
                    $referenceId = $payload['referenceId'] ?? null;
                    $status = $payload['status'] ?? null;
                    $amount = $payload['amount'] ?? null;

                    Log::info('Beam Charge Succeeded Event', [
                        'chargeId' => $chargeId,
                        'referenceId' => $referenceId,
                        'status' => $status,
                        'amount' => $amount,
                    ]);

                    $orderInfo = Order::query()
                    ->where('formatted_id', $referenceId)
                    ->where('payment_status', 0)
                    ->where('order_status', 1)
                    ->whereNull('end_shopping_date')
                    ->first();

                    if (empty($orderInfo)) {
                        Log::warning('Beam Webhook: Order not found or already processed', [
                            'referenceId' => $referenceId,
                        ]);
                        return response()->json([
                            'acknowledged' => true,
                            'message' => 'Order not found or already processed',
                            'referenceId' => $referenceId,
                            'orderInfo' => $orderInfo,
                        ], 200);
                    }

                    $updateResult = Order::updateOrderAfterPayment($orderInfo);
                    if ($updateResult) {
                        Log::info('Beam Webhook: Order updated after payment', [
                            'referenceId' => $referenceId,
                            'orderId' => $orderInfo->id,
                        ]);
                        return response()->json([
                            'acknowledged' => true,
                            'message' => 'Order updated successfully',
                            'referenceId' => $referenceId,
                            'orderInfo' => $orderInfo,
                        ], 200);
                    } else {
                        Log::error('Beam Webhook: Failed to update order after payment', [
                            'referenceId' => $referenceId,
                            'orderId' => $orderInfo->id,
                        ]);
                        return response()->json([
                            'acknowledged' => false,
                            'message' => 'Failed to update order',
                            'referenceId' => $referenceId,
                            'orderInfo' => $orderInfo,
                        ], 500);
                    }
                    break;

                default:
                    Log::info('Beam Webhook: Unhandled event', ['event' => $beamEvent]);
                    return response()->json([
                        'acknowledged' => true,
                        'message' => 'Event ignored',
                    ], 200);
                    
            }
            return response()->json(['acknowledged' => true], 200);

        } catch (\Exception $e) {
            Log::error('Beam Webhook Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['acknowledged' => false, 'error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * ✅ Verify the webhook authenticity using HMAC SHA256 + base64
     */
    private function verifySignature(Request $request, string $receivedSignature): bool
    {
        $envSecret = env('BEAM_WEBHOOK_SECRET');
        $secret = $this->normalizeSecret($envSecret);
        $rawPayload = $request->getContent();

        // Compute expected signature (HMAC SHA256 + base64)
        $expectedSignature = base64_encode(hash_hmac('sha256', $rawPayload, $secret, true));

        Log::debug('Beam Signature Verification', [
            'expected' => $expectedSignature,
            'received' => $receivedSignature,
        ]);

        return hash_equals($expectedSignature, $receivedSignature);
    }

    private function normalizeSecret(?string $secret): string
    {
        if (empty($secret)) {
            Log::error('Beam Webhook: BEAM_WEBHOOK_SECRET is missing or empty.');
            throw new \RuntimeException('Missing BEAM_WEBHOOK_SECRET');
        }

        // ตรวจสอบว่ามีลักษณะของ Base64 หรือไม่
        $isBase64 = preg_match('/^[A-Za-z0-9+\/=]+$/', $secret) &&
                    (strlen($secret) % 4 === 0);

        if ($isBase64) {
            $decoded = base64_decode($secret, true);

            if ($decoded !== false) {
                Log::debug('Beam Webhook: Secret detected as Base64 and decoded successfully.');
                return $decoded;
            }

            Log::warning('Beam Webhook: Base64 decode failed, fallback to raw secret.');
        } else {
            Log::debug('Beam Webhook: Secret is plain text (not Base64).');
        }

        // fallback: ใช้ raw secret เดิม
        return $secret;
    }
}
