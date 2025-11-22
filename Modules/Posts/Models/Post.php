<?php

namespace Modules\Posts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public const COVER_IMAGE_COLLECTION = 'cover';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'image',
    ];

    /**
     * Configure the model factory.
     */
    protected static function newFactory()
    {
        return \Modules\Posts\Database\Factories\PostFactory::new();
    }

    /**
     * Register the cover image collection.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::COVER_IMAGE_COLLECTION)
            ->singleFile();
    }

    /**
     * Register responsive conversions without queueing.
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(900)
            ->nonQueued();
    }

    /**
     * Accessor for the cover image URL.
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if ($mediaUrl = $this->getFirstMediaUrl(self::COVER_IMAGE_COLLECTION)) {
            return $mediaUrl;
        }

        if ($this->image) {
            return filter_var($this->image, FILTER_VALIDATE_URL)
                ? $this->image
                : asset('storage/' . $this->image);
        }

        return null;
    }

    /**
     * Accessor for the cover image thumbnail URL.
     */
    public function getCoverImageThumbUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia(self::COVER_IMAGE_COLLECTION);

        if ($media) {
            return $media->getUrl('thumb');
        }

        return null;
    }

    /**
     * Get the user that owns the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(\Modules\Comments\Models\Comment::class);
    }

    /**
     * Scope a query to search posts by title or content.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%");
        });
    }
}
