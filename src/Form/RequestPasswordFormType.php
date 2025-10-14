<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Form;

use Symfony\Component\Form\AbstractType;

/**
 * @extends AbstractType<array>
 */
class RequestPasswordFormType extends AbstractType
{
    public function getParent(): string
    {
        return LoginFormType::class;
    }
}
