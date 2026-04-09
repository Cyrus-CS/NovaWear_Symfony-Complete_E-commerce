<?php
namespace App\Enum;
enum ShipmentStatus: string {
    case Pending = 'pending';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Returned = 'returned';
    case Cancelled = 'cancelled'; 
}    