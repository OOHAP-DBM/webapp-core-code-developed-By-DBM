<?php

namespace App\Traits;

use App\Models\BookingTimelineEvent;
use App\Services\BookingTimelineService;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTimeline
{
    /**
     * Boot the trait
     */
    protected static function bootHasTimeline()
    {
        // Auto-generate timeline when booking is created
        static::created(function ($model) {
            if (method_exists($model, 'shouldGenerateTimeline') && !$model->shouldGenerateTimeline()) {
                return;
            }

            $service = app(BookingTimelineService::class);
            $service->generateFullTimeline($model);
        });
    }

    /**
     * Get timeline events
     */
    public function timelineEvents(): HasMany
    {
        return $this->hasMany(BookingTimelineEvent::class, 'booking_id')
                    ->orderBy('order')
                    ->orderBy('created_at');
    }

    /**
     * Get timeline
     */
    public function getTimeline()
    {
        return $this->timelineEvents;
    }

    /**
     * Get timeline progress percentage
     */
    public function getTimelineProgress(): float
    {
        $service = app(BookingTimelineService::class);
        return $service->getProgress($this);
    }

    /**
     * Get current timeline stage
     */
    public function getCurrentStage(): ?BookingTimelineEvent
    {
        $service = app(BookingTimelineService::class);
        return $service->getCurrentStage($this);
    }

    /**
     * Get next timeline event
     */
    public function getNextEvent(): ?BookingTimelineEvent
    {
        $service = app(BookingTimelineService::class);
        return $service->getNextEvent($this);
    }

    /**
     * Rebuild timeline
     */
    public function rebuildTimeline(): void
    {
        $service = app(BookingTimelineService::class);
        $service->rebuildTimeline($this);
    }

    /**
     * Add timeline event
     */
    public function addTimelineEvent(string $eventType, string $title, array $options = []): BookingTimelineEvent
    {
        $service = app(BookingTimelineService::class);
        return $service->createEvent($this, $eventType, $title, $options);
    }

    /**
     * Start production stage
     */
    public function startProductionStage(string $stage): BookingTimelineEvent
    {
        $service = app(BookingTimelineService::class);
        return $service->startProductionEvent($this, $stage);
    }

    /**
     * Complete production stage
     */
    public function completeProductionStage(string $stage): BookingTimelineEvent
    {
        $service = app(BookingTimelineService::class);
        return $service->completeProductionEvent($this, $stage);
    }
}
