<?php

namespace App\Traits;

trait ModelHelper
{
    public function scopeRecordActive($query)
    {
        return $query->where('status', RECORD_STATUS_ACTIVE);
    }

    public function scopeRecordInactive($query)
    {
        return $query->where('status', RECORD_STATUS_INACTIVE);
    }
}