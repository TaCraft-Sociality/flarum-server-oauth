<?php

declare(strict_types=1);

namespace TacraftSociality\ServerOAuth\Support;

use Carbon\CarbonInterface;
use DateTimeInterface;

final class Date
{
    public static function format(DateTimeInterface|string|null $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->toIso8601String();
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : date(DATE_ATOM, $timestamp);
    }
}
