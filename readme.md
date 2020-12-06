# SocketBus Laravel Library


## Installation

```bash
composer require socketbus/socketbus-laravel
```

## Configuration

Add the SocketBus driver in your `config/broadcasting.php`.

```php
return [
    'connections' => [
        /** ... */
        'socketbus' => [
            'driver' => 'socketbus',
            'app_id' => env('SOCKET_BUS_APP_ID'),
            'secret' => env('SOCKET_BUS_SECRET'),
            'custom_encryption_key' => env('SOCKET_BUS_ENCRYPTION_KEY')
        ]
    ]
];
```

In your .env file change `BROADCAST_DRIVER` to `socketbus`.

Define in your .env `SOCKET_BUS_APP_ID`, `SOCKET_BUS_SECRET`. If the setting End-to-end Encryption is enabled, add the `SOCKET_BUS_ENCRYPTION_KEY` with a random unique string. This key is used to encrypt and decrypt the payloads.

## Broadcasting

Define your event
```php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MyEventEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel("my-event");
    }
}
```

Send the event in real-time

```php
use App\Events\MyEventEvent;

// sends a realtime message to browser
broadcast(new MyEventEvent());
```