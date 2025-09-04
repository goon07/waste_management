<?php
// app/Events/PaymentUpdated.php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class PaymentUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function broadcastOn()
    {
        return new Channel('payments');
    }
}