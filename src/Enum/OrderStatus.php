<?php
namespace App\Enum;
enum OrderStatus: string {
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Canceled = 'canceled';
}