<?php


namespace Seat\Eveapi\Jobs\Middleware;


use Illuminate\Support\Facades\Redis;

class ThrottleSameTokenJobs implements IJobMiddleware
{
    /**
     * @param \Seat\Eveapi\Jobs\EsiBase $job
     * @param $next
     */
    public function handle($job, $next)
    {
        $redis_key = sprintf('jobs:throttler:token:%d', $job->getToken()->character_id);

        Redis::throttle($redis_key)->allow(1)->then(function () use ($job, $next) {
            logger()->debug('Processing job related to token', [
                'character_id' => $job->getToken()->character_id,
            ]);
            $next($job);
        }, function () use ($job) {
            logger()->debug('A job related to this token is already processing', [
                'character_id' => $job->getToken()->character_id,
            ]);

            // prevent release to increase current jobs attempts
            //$job->attempts -= 1;

            return $job->release(10);
        });
    }
}