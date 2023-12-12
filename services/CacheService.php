<?php

namespace Moktech\MockLoggerSDK\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Class CacheService
 * 
 * A service class for interacting with the Laravel Cache.
 * Provides methods for getting, incrementing, and resetting cache values.
 */
class CacheService
{
    /**
     * Key for storing the count of sent emails in the cache.
     */
    const EMAIL_COUNT_KEY = 'mocklogger.sent.email.count';

    /**
     * Key for storing the email throttling status in the cache.
     */
    const EMAIL_THROTTLE_KEY = 'mocklogger.email.throttle';

    /**
     * Get the value from the cache for the specified key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default)
    {
        return Cache::get($key, $default);
    }

    /**
     * Increment the value stored in the cache for the specified key.
     *
     * @param string $key
     * @return int
     */
    public function increment(string $key): int
    {
        return Cache::increment($key);
    }

    /**
     * Reset the cache by forgetting the email count and setting the email throttling status.
     *
     * @param int|null $emailInterval If provided, set the throttling status with the specified interval.
     * @return void
     */
    public function reset(?int $emailInterval = null): void
    {
        // Calculate the expiration time based on the provided email interval
        $ttl = now()->addMinutes($emailInterval);

        // Forget the email count and set the email throttling status
        Cache::forget(self::EMAIL_COUNT_KEY);
        Cache::put(self::EMAIL_THROTTLE_KEY, !is_null($emailInterval), $ttl);
    }
}
