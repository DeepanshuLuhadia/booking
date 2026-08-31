<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'admin_reply',
        'replied_at',
        'replied_by',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /**
     * Enquiries the admin has not opened yet — drives the sidebar badge.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Tailwind classes for the status pill, kept next to the states they describe.
     */
    public function statusClasses(): string
    {
        return match ($this->status) {
            'new'     => 'bg-sky-500/15 text-sky-400 border-sky-500/20',
            'read'    => 'bg-amber-500/15 text-amber-400 border-amber-500/20',
            'replied' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
            default   => 'bg-white/10 text-white/50 border-white/10',
        };
    }
}
