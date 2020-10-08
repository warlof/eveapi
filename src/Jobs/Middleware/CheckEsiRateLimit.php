<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2020 Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Jobs\Middleware;

use Seat\Eveapi\Jobs\EsiBase;

/**
 * Class CheckEsiRateLimit.
 *
 * @package Seat\Eveapi\Jobs\Middleware
 */
class CheckEsiRateLimit implements IJobMiddleware
{
    /**
     * @param \Illuminate\Queue\InteractsWithQueue $job
     * @param $next
     */
    public function handle($job, $next)
    {
        // in case the job is not ESI related, bypass this check
        if (is_subclass_of($job, EsiBase::class)) {

            // in case ESI limit has been reached, delay the job
            if ($this->isEsiRateLimitReached($job)) {
                logger()->warning(
                    sprintf('EVE Online server seems to be unreachable. Job %s has been delayed by %d seconds.',
                        get_class($job), $job::RATE_LIMIT_DURATION));

                $job->release($job::RATE_LIMIT_DURATION);

                return;
            }
        }

        $next($job);
    }

    /**
     * @param \Seat\Eveapi\Jobs\EsiBase $job
     * @return bool
     */
    private function isEsiRateLimitReached(EsiBase $job): bool
    {
        $current = cache()->get($job::RATE_LIMIT_KEY) ?: 0;

        logger()->debug('Rate Limit Status', ['current' => $current, 'limit' => $job::RATE_LIMIT]);

        return $current >= $job::RATE_LIMIT;
    }
}
