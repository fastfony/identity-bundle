<?php

namespace Fastfony\IdentityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FastfonyIdentityBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
