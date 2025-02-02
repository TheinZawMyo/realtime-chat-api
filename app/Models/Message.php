<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Events\MessageEvent;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'attachment',
        'is_read',
        'read_at',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $dispatchesEvents = [
        'created' => MessageEvent::class,
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
