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
use Seat\Eveapi\Models\Corporation\CorporationIssuedMedal;

class IssuedMedals extends EsiBase {

    protected $method = 'get';

    protected $endpoint = '/corporations/{corporation_id}/medals/issued/';

    protected $version = 'v1';

    protected $page = 1;

    public function handle() {

        while(true) {

            $medals = $this->retrieve([
                'corporation_id' => $this->getCorporationId(),
            ]);

            collect($medals)->each(function($medal){

                CorporationIssuedMedal::firstOrNew([
                    'corporation_id' => $this->getCorporationId(),
                    'medal_id'       => $medal->medal_id,
                    'character_id'   => $medal->character_id,
                ])->fill([
                    'reason'         => $medal->reason,
                    'status'         => $medal->status,
                    'issuer_id'      => $medal->issuer_id,
                    'issued_at'      => carbon($medal->issued_at),
                ])->save();

            });

            if (! $this->nextPage($medals->pages))
                break;

        }

    }

}