<?php

namespace App\Tests\Form;

use App\Entity\Project;
use App\Form\AdminProjectType;
use Symfony\Component\Form\Test\TypeTestCase;

class AdminProjectTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'title' => 'Construction de puits',
            'slug' => 'puits',
            'image' => 'img/puits.jpg',
            'description' => 'Description du projet',
        ];

        $model = new Project();
        $form = $this->factory->create(AdminProjectType::class, $model);

        $expected = new Project();
        $expected->setTitle('Construction de puits');
        $expected->setSlug('puits');
        $expected->setImage('img/puits.jpg');
        $expected->setDescription('Description du projet');

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected->getTitle(), $model->getTitle());
        $this->assertEquals($expected->getSlug(), $model->getSlug());
        $this->assertEquals($expected->getImage(), $model->getImage());
        $this->assertEquals($expected->getDescription(), $model->getDescription());
    }

    public function testFormHasDataJsonField(): void
    {
        $form = $this->factory->create(AdminProjectType::class, new Project());
        $this->assertTrue($form->has('dataJson'));
    }
}