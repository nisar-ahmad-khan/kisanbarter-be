<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offers extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'listing_id',
        'offered_by',
        'status',
        'message',
    ];

    /**
     * Get the listing that the offer is made for.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    /**
     * Get the user who made the offer.
     */
    public function offeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'offered_by');
    }

    /**
     * An offer can have multiple offer items (for multi-item trades).
     */
    // public function offerItems(): HasMany
    // {
    //     return $this->hasMany(OfferItem::class);
    // }

    /**
     * An offer has a thread of chat messages.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Check if the offer is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the offer is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
}