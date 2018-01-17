<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017, 2018  Leon Jacobs
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

namespace Seat\Eveapi\Jobs\Corporation;

use Seat\Eveapi\Jobs\EsiBase;
use Seat\Eveapi\Models\Corporation\CorporationContainerLog;

class ContainerLogs extends EsiBase {

    protected $method = 'get';

    protected $endpoint = '/corporations/{corporation_id}/containers/logs/';

    protected $version = 'v1';

    protected $page = 1;

    public function handle() {

        while (true) {

            $logs = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            collect($logs)->each(function($log){

                CorporationContainerLog::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'container_id' => $log->container_id,
                    'logged_at' => carbon($log->logged_at),
                ])->fill([
                    'container_type_id' => $log->container_type_id,
                    'character_id' => $log->character_id,
                    'location_id' => $log->location_id,
                    'action' => $log->action,
                    'location_flag' => $log->location_flag,
                    'password_type' => $log->password_type,
                ])->save();

            });

            if (! $this->nextPage($logs->pages))
                break;

        }

    }

}