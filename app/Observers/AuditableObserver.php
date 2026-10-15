<?php

namespace App\Observers;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function __construct(private AuditLogger $audit) {}

    public function created(Model $model): void
    {
        $this->audit->recordModelEvent($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->audit->recordModelEvent($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->audit->recordModelEvent($model, 'deleted');
    }
}
