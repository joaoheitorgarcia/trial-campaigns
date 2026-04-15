<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactList extends Model
{
    use HasFactory;

    protected $table = 'contact_lists';

    protected $fillable = ['name'];
    protected $casts = [
        'name' => 'string',
    ];

    public function campaigns(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function contacts(): \Illuminate\Database\Eloquent\Relations\belongsToMany
    {
        return $this->belongsToMany(Contact::class);
    }
}
