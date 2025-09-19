<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionSchedule extends Model
{
    protected $table = 'collection_schedules';

    protected $fillable = [
        'collection_id',
        'scheduled_date',
        'assigned_collector_id',
        'assigned_by_admin_id',
        'status',
        'schedule_type',
        'recurring_day',
        'monthly_day',
        'start_date',
        'end_date',
        'notes',
    ];

    public function collection()
    {
        return $this->belongsTo(Collection::class, 'collection_id');
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'assigned_collector_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'assigned_by_admin_id');
    }
}