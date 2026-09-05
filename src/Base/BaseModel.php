<?php

namespace HieuDev92264\LaravelModules\Base;

use HieuDev92264\LaravelModules\traits\HasBaseMetadata;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use HasBaseMetadata;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected function casts(): array
    {
        return $this->baseMetadataCasts();
    }
}
