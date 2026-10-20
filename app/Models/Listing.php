<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * 
     * (Remove this if you fix the table name typo in your migration to 'listings')
     *
     * @var string
     */
    protected $table = 'lisitings';

    /**
     * Default model attribute values.
     */
    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'listing_id',
        'category_id',
        'trade_type',
        'status',
        'title',
        'description',
        'qty',
        'price',
        'wanted_description',
    ];

    /**
     * Get the user that owns the listing.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A listing can have many images.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ListingImages::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
