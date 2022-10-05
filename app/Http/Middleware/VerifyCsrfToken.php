<?php 

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */

    // Added to exclude csrf token | Start
    protected $except = [
    	'en/admin/modules/*', // Added to exclude csrf token  
    	'en/PaymentModule2C2P/payment/*', // Added to exclude csrf token for 2c2p payment module 
        'en/synchronizeBroadcasts',
        '/checkout/tracking',
        'checkout/tracking',
        '/checkout/payplus/tracking',
        'checkout/payplus/tracking',
        'paymentgateway/kbank/v1/odd/checkout/tracking',
        'payment-gateway/kbank/v1/odd/checkout/tracking'
    ];

   
}
