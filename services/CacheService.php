<?php
namespace Moktech\MockLoggerSDK\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Class CacheService
 * 
 * Monitor class for checking CPU usage.
 */

class CacheService
{   
    const EMAIL_COUNT_KEY = 'mocklogger.sent.email.count';
    const EMAIL_THROTTLE_KEY = 'mocklogger.email.throttle';

    public function get($key, $default)
    {
        return Cache::get($key, $default);
    }

    public function increment($key)
    {
        return Cache::increment($key);
    }

    public function resetCache(?int $emailInterval = null)
    {   
        $ttl = now()->addMinutes($emailInterval);
        Cache::forget(self::EMAIL_COUNT_KEY);
        Cache::put(self::EMAIL_THROTTLE_KEY, !is_null($emailInterval), $ttl);
    }
}