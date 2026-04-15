<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SENDING,
        self::STATUS_SENT,
    ];

    protected $table = 'campaigns';

    protected $fillable = ['subject', 'body', 'contact_list_id', 'status', 'scheduled_at', 'reply_to'];

    protected $casts = [
        'status' => 'string',
        'scheduled_at' => 'datetime',
        'reply_to' => 'string',
    ];

    public function contactList(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ContactList::class);
    }

    public function sends(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CampaignSend::class);
    }

    public function scopeWithSendStats(Builder $query): Builder
    {
        return $query->withCount([
            'sends as pending_sends_count' => fn (Builder $query) => $query->where('status', CampaignSend::STATUS_PENDING),
            'sends as sent_sends_count' => fn (Builder $query) => $query->where('status', CampaignSend::STATUS_SENT),
            'sends as failed_sends_count' => fn (Builder $query) => $query->where('status', CampaignSend::STATUS_FAILED),
            'sends as total_sends_count',
        ]);
    }

    public function getStatsAttribute(): array
    {
        return [
            CampaignSend::STATUS_PENDING => (int) ($this->pending_sends_count ?? 0),
            CampaignSend::STATUS_SENT => (int) ($this->sent_sends_count ?? 0),
            CampaignSend::STATUS_FAILED => (int) ($this->failed_sends_count ?? 0),
            'total' => (int) ($this->total_sends_count ?? 0),
        ];
    }
}
