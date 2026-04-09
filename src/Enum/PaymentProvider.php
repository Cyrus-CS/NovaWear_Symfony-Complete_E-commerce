<?php
namespace App\Enum;
enum PaymentProvider: string {
    case Stripe = 'stripe';
    case PayPal = 'paypal';
    case Fedapay = 'fedapay';
}        