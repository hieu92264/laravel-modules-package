<?php

namespace HieuDev92264\LaravelModules\traits;

use Illuminate\Database\Eloquent\Builder;

trait HasBaseMetadata
{
    public function initializeHasBaseMetadata(): void
    {
        $this->mergeFillable([
            'is_active',
            'user_name_created',
            'user_name_updated',
        ]);
    }

    protected static function bootHasBaseMetadata(): void
    {
        static::creating(function ($model): void {
            if ($model->getAttribute('is_active') === null) {
                $model->setAttribute('is_active', true);
            }
            $model->applyAuditColumns(true);
        });

        static::updating(function ($model): void {
            $model->applyAuditColumns();
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_active'), '=', true);
    }

    protected function baseMetadataCasts(): array
    {
        return [
            'is_active' => 'boolean',
            'user_name_created' => 'string',
            'user_name_updated' => 'string',
        ];
    }

    protected function applyAuditColumns(bool $isCreating = false): void
    {
        $actor = auth()->user();

        if ($actor === null) {
            return;
        }

        if ($isCreating && $this->getAttribute('user_name_created') === null) {
            $this->setAttribute('user_name_created', $actor->user_name ?? null);
        } else {
            $this->setAttribute('user_name_updated', $actor->user_name ?? null);
        }
    }
}
