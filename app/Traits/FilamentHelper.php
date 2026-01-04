<?php

namespace App\Traits;

trait FilamentHelper
{
    public static function getNavigationLabel(): string
    {
        return __('common.' . str_replace("resource", "", strtolower(class_basename(static::class))));
    }

    public static function getNavigationGroup(): ?string
    {
        return __('common.resource_management');
    }

    public static function getModelLabel(): string
    {
        return __('common.' . str_replace("resource", "", strtolower(class_basename(static::class))));
    }
}
