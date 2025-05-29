<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageStatus extends Model
{
    use HasFactory;

    protected $table = 'message_status';

    protected $fillable = [
        'message_id',
        'user_id',
        'status',
    ];

    /**
     * Get the message that this status belongs to.
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the user this status is related to.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
