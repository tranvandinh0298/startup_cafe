<?php

namespace App\Services;

use App\Models\InventoryLot;
use Illuminate\Support\Facades\DB;

class InventoryReceiveService
{
    public function receive(array $data)
    {
        return DB::transaction(function () use ($data) {
            return InventoryLot::insert(
                $data
            );
        });
    }
}
