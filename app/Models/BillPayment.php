<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillPayment extends Model
{
    use HasFactory;

    protected $table = 'bill_payments';

    protected $fillable = [
        'bill_id',
        'user_id',
        'amount',
        'payment_date',
        'status',
    ];

    public function bill()
    {
        return $this->belongsTo(UserBill::class, 'bill_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
