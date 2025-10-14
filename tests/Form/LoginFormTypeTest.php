<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Form;

use Fastfony\IdentityBundle\Form\LoginFormType;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;

#[CoversClass(LoginFormType::class)]
final class LoginFormTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();
        return [
            new ValidatorExtension($validator),
            new PreloadedExtension([], []),
        ];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'foo@example.com',
        ];

        $form = $this->factory->create(LoginFormType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());
        $this->assertSame('foo@example.com', $form->get('email')->getData());
    }

    public function testSubmitInvalidData(): void
    {
        $formData = [
            'email' => '',
        ];

        $form = $this->factory->create(LoginFormType::class);
        $form->submit($formData);

        $this->assertFalse($form->isValid());
    }

    public function testFormFields(): void
    {
        $form = $this->factory->create(LoginFormType::class);
        $this->assertTrue($form->has('email'));
    }
}
