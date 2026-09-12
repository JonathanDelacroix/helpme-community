<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;

class RegistrationFormTypeTest extends TypeTestCase
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
        $formData = [
            'email' => 'jean.dupont@test.com',
            'plainPassword' => 'MotDePasse123!',
            'agreeTerms' => true,
        ];

        $model = new User();
        $form = $this->factory->create(RegistrationFormType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('jean.dupont@test.com', $model->getEmail());
        $this->assertCount(0, $form->getErrors(true));
    }

    public function testSubmitWeakPasswordFailsValidation(): void
    {
        $formData = [
            'email' => 'jean.dupont@test.com',
            'plainPassword' => 'faible',
            'agreeTerms' => true,
        ];

        $model = new User();
        $form = $this->factory->create(RegistrationFormType::class, $model);

        $form->submit($formData);

        $this->assertGreaterThan(0, count($form->get('plainPassword')->getErrors()));
    }

    public function testSubmitWithoutAcceptingTermsFailsValidation(): void
    {
        $formData = [
            'email' => 'jean.dupont@test.com',
            'plainPassword' => 'MotDePasse123!',
            'agreeTerms' => false,
        ];

        $model = new User();
        $form = $this->factory->create(RegistrationFormType::class, $model);

        $form->submit($formData);

        $this->assertGreaterThan(0, count($form->get('agreeTerms')->getErrors()));
    }
}