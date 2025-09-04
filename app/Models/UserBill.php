<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserBill extends Model
{
    use HasFactory;

    protected $table = 'user_bills';

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'amount',
        'status',
    ];

    // Relationship to the user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to payments
    public function payments()
    {
        return $this->hasMany(BillPayment::class, 'bill_id');
    }

    // Compute total paid
    public function getTotalPaidAttribute()
    {
        return $this->payments->sum('amount');
    }

    // Update status based on payments
    public function updateStatus()
    {
        $totalPaid = $this->payments->sum('amount');

        if ($totalPaid >= $this->amount) {
            $this->status = 'paid';
        } elseif ($totalPaid == 0) {
            $this->status = 'pending';
        } else {
            $this->status = 'partial';
        }

        $this->save();
    }
}
