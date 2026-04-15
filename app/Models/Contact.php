<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_UNSUBSCRIBED,
    ];

    protected $fillable = ['name', 'email', 'status'];
    protected $casts = [
        'status' => 'string',
    ];

    public function contactLists(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ContactList::class);
    }
}
