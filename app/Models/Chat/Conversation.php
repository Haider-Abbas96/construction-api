<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Conversation extends Model
{
    protected $guarded = [];
    
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at')
            ->withTimestamps();
    }
    
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
    
    public function getOtherUserAttribute(): User
    {
        return $this->users()->where('user_id', '!=', auth()->id())->first();
    }
}
