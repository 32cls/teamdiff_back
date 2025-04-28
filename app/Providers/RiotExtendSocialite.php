<?php

declare(strict_types=1);

namespace App\Providers;

use SocialiteProviders\Manager\SocialiteWasCalled;

class RiotExtendSocialite
{
    public function handle(SocialiteWasCalled $socialiteWasCalled): void
    {
        $socialiteWasCalled->extendSocialite('riot', RiotProvider::class);
    }
}
