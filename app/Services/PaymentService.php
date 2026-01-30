<?php

namespace App\Services;

use Razorpay\Api\Api;
use Exception;

class PaymentService
{
    protected $api;

    public function __construct()
    {
        $this->api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
    }

    public function createOrder($amount, $receiptId, $metadata = [])
    {
        try {
            $order = $this->api->order->create([
                'receipt' => $receiptId,
                'amount' => $amount * 100, // amount in paise
                'currency' => 'INR',
                'notes' => $metadata
            ]);
            return $order;
        } catch (Exception $e) {
            logger()->error('Razorpay Order Error: ' . $e->getMessage());
            return null;
        }
    }

    public function verifyPayment($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)
    {
        try {
            $attributes = [
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature
            ];
            $this->api->utility->verifyPaymentSignature($attributes);
            return true;
        } catch (Exception $e) {
            logger()->error('Razorpay Verification Error: ' . $e->getMessage());
            return false;
        }
    }
}
