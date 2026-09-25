<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        return [
            $this->allSubdomainsOfApplicationUrl(),
            'ongccsr.co.in',
            '^(.+\.)?ongccsr\.co\.in$',
            '103.35.165.225',
            '^103\.35\.165\.225$',
            '20.219.17.111',
            '^20\.219\.17\.111$',
            'localhost',
            '127.0.0.1',
        ];
    }
}
