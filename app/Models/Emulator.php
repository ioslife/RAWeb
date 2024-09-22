<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Database\Eloquent\BaseModel;
use App\Support\Database\Eloquent\BasePivot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Emulator extends BaseModel implements HasMedia
{
    use SoftDeletes;
    use SortableTrait;
    use InteractsWithMedia;
    use LogsActivity {
        LogsActivity::activities as auditLog;
    }

    protected $fillable = [
        'name',
        'original_name',
        'description',
        'active',
        'documentation_url',
        'download_url',
        'source_url',
    ];

    // audit activity log

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'original_name',
                'description',
                'active',
                'documentation_url',
                'download_url',
                'source_url',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // == media

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('2xl')
                    ->nonQueued()
                    ->format('png')
                    ->fit(Fit::Max, 500, 500);
                $this->addMediaConversion('32')
                    ->nonQueued()
                    ->format('png')
                    ->fit(Fit::Max, 64, 64);
                $this->addMediaConversion('64')
                    ->nonQueued()
                    ->format('png')
                    ->fit(Fit::Max, 64, 64);
            });
    }

    // == accessors

    // == mutators

    // == relations

    /**
     * @return BelongsToMany<System>
     */
    public function systems(): BelongsToMany
    {
        return $this->belongsToMany(System::class, 'system_emulators')
            ->using(BasePivot::class)
            ->withTimestamps();
    }

    /**
     * @return HasOne<EmulatorRelease>
     */
    public function latestRelease(): HasOne
    {
        return $this->hasOne(EmulatorRelease::class)
            ->where('stable', true)
            ->orderBy('created_at', 'DESC');
    }

    /**
     * @return HasOne<EmulatorRelease>
     */
    public function minimumSupportedRelease(): HasOne
    {
        return $this->hasOne(EmulatorRelease::class)
            ->where('minimum', true)
            ->orderBy('created_at', 'DESC');
    }

    /**
     * @return HasOne<EmulatorRelease>
     */
    public function latestBetaRelease(): HasOne
    {
        return $this->hasOne(EmulatorRelease::class)
            ->where('stable', false)
            ->orderBy('created_at', 'DESC');
    }

    /**
     * @return HasMany<EmulatorRelease>
     */
    public function releases(): HasMany
    {
        return $this->hasMany(EmulatorRelease::class);
    }

    // == scopes

    /**
     * @param Builder<Emulator> $query
     * @return Builder<Emulator>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * @param Builder<Emulator> $query
     * @return Builder<Emulator>
     */
    public function scopeForSystem(Builder $query, int $systemId): Builder
    {
        return $query->whereHas('systems', function ($query) use ($systemId) {
            $query->where('system_id', $systemId);
        });
    }
}
