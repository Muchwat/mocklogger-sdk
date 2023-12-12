<?php

namespace Moktech\MockLoggerSDK\Services;
use Illuminate\Support\Facades\Config;

/**
 * Class EmailThrottler
 */
class EmailThrottler
{
    /**
     * Cache service instance.
     *
     * @var CacheService
     */
    private $cacheService;

    /* Number of emails to be sent in a given time (minutes) interval.
    *
    * @var $emailCount
    */
    private $emailCount;

   /* Throttle interval in minites.
   *
   * @var $emailInterval
   */
  private $emailInterval;

    /**
     * EmailThrottler constructor.
     *
     * @param CacheService $cacheService
     */
    public function __construct(CacheService $cacheService)
    {   
        $this->emailCount = Config::get('mocklogger.monitor.email.count');
        $this->emailInterval = Config::get('mocklogger.monitor.email.interval');
        $this->cacheService = $cacheService;
    }

    /**
     * Check if an email can be sent based on count and interval.
     * @return bool
     */
    public function canSendEmail(): bool
    {    
        // Check if email sending is allowed
        if ($this->isEmailSendingAllowed()) {
            return true;
        }

        // Reset cache and prevent email sending
        $this->cacheService->resetCache($this->emailInterval);
        return false;
    }

    /**
     * Check if email sending is allowed based on cache.
     * @return bool
     */
    private function isEmailSendingAllowed(): bool
    {
        // Check if email sending is allowed based on cache
        $count = $this->cacheService->increment(CacheService::EMAIL_COUNT_KEY);
        return !$this->cacheService->get(CacheService::EMAIL_THROTTLE_KEY, false) && $count <= $this->emailCount;
    }
}