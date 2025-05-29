<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Message extends Model
{
    protected $guarded = [];
    
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function statuses(): HasMany
    {
        return $this->hasMany(MessageStatus::class);
    }
}
