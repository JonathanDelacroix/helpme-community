<?php

namespace App\Tests\Form;

use App\Form\ChangePasswordFormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ChangePasswordFormTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData(): void
    {
        $form = $this->factory->create(ChangePasswordFormType::class);

        $form->submit([
            'plainPassword' => [
                'first' => 'MotDePasse123!',
                'second' => 'MotDePasse123!',
            ],
        ]);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('MotDePasse123!', $form->get('plainPassword')->getData());
    }

    public function testFormHasPlainPasswordField(): void
    {
        $form = $this->factory->create(ChangePasswordFormType::class);
        $this->assertTrue($form->has('plainPassword'));
    }
}