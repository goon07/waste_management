<?php

namespace App\Exports;

use App\Models\Collection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class PickupsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;
    protected $councilId;

    public function __construct(Request $request, $councilId)
    {
        $this->request = $request;
        $this->councilId = $councilId;
    }

    public function query()
    {
        $query = Collection::where('council_id', $this->councilId)
            ->with(['wasteType', 'user', 'collector']);

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        if ($this->request->filled('waste_type_id')) {
            $query->where('waste_type_id', $this->request->waste_type_id);
        }

        if ($this->request->filled('collector_id')) {
            $query->where('collector_id', $this->request->collector_id);
        }

        if ($this->request->filled('date_range')) {
            [$start, $end] = explode(' to ', $this->request->date_range);
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('scheduled_date', [$start, $end])
                  ->orWhereBetween('created_at', [$start, $end]);
            });
        }

        $sort = $this->request->input('sort', 'created_at');
        $direction = $this->request->input('direction', 'desc');
        if ($sort == 'user_name') {
            $query->join('users', 'collections.user_id', '=', 'users.id')
                  ->orderBy('users.name', $direction);
        } elseif ($sort == 'waste_type') {
            $query->join('waste_types', 'collections.waste_type_id', '=', 'waste_types.id')
                  ->orderBy('waste_types.name', $direction);
        } elseif ($sort == 'collector_name') {
            $query->leftJoin('users as collectors', 'collections.collector_id', '=', 'collectors.id')
                  ->orderBy('collectors.name', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'User',
            'Waste Type',
            'Status',
            'Scheduled Date',
            'Collector',
            'Rating',
            'Feedback',
            'Address',
            'Created At',
        ];
    }

    public function map($pickup): array
    {
        return [
            $pickup->id,
            $pickup->user->name ?? 'Unknown',
            ucfirst($pickup->wasteType->name ?? $pickup->waste_type),
            ucfirst($pickup->status),
            $pickup->scheduled_date ? \Carbon\Carbon::parse($pickup->scheduled_date)->format('Y-m-d H:i') : ($pickup->status == 'pending' ? \Carbon\Carbon::parse($pickup->created_at)->format('Y-m-d H:i') : 'N/A'),
            $pickup->collector ? $pickup->collector->name : 'N/A',
            $pickup->rating ?? 'N/A',
            $pickup->feedback ?? 'N/A',
            $pickup->user->address ?? 'N/A',
            \Carbon\Carbon::parse($pickup->created_at)->format('Y-m-d H:i'),
        ];
    }
}