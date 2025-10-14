<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Tests\Form;

use Fastfony\IdentityBundle\Form\LoginFormType;
use Fastfony\IdentityBundle\Form\RegisterFormType;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;

#[CoversClass(RegisterFormType::class)]
#[CoversClass(LoginFormType::class)]
final class RegisterFormTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();
        return [
            new ValidatorExtension($validator),
            new PreloadedExtension([
                new LoginFormType(),
            ], []),
        ];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'foo@example.com',
            'agreeWithTerms' => true,
        ];

        $form = $this->factory->create(RegisterFormType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());
        $this->assertSame('foo@example.com', $form->get('email')->getData());
        $this->assertTrue($form->get('agreeWithTerms')->getData());
    }

    public function testSubmitInvalidData(): void
    {
        $formData = [
            'email' => '',
            'agreeWithTerms' => false,
        ];

        $form = $this->factory->create(RegisterFormType::class);
        $form->submit($formData);

        $this->assertFalse($form->isValid());
    }

    public function testFormFields(): void
    {
        $form = $this->factory->create(RegisterFormType::class);
        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('agreeWithTerms'));
    }
}
