<?php

namespace App\Tests\Form;

use App\Entity\Donation;
use App\Entity\Project;
use App\Form\DonationType;
use App\Repository\ProjectRepository;
use Symfony\Component\Form\Test\TypeTestCase;

class DonationTypeTest extends TypeTestCase
{
    protected function getTypes(): array
    {
        $project = new Project();
        $project->setTitle('Construction de puits');
        $project->setSlug('puits');
        $project->setDescription('desc');

        $projectRepository = $this->createMock(ProjectRepository::class);
        $projectRepository->method('findAll')->willReturn([$project]);

        return [
            new DonationType($projectRepository),
        ];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'type' => 'puits',
            'amount' => 50,
            'firstName' => 'Jean',
            'lastName' => 'Dupont',
            'email' => 'jean@test.com',
            'address' => '1 rue Test',
            'city' => 'Paris',
            'zip' => '75001',
            'country' => 'FR',
        ];

        $model = new Donation();
        $form = $this->factory->create(DonationType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('puits', $model->getType());
        $this->assertEquals(50, $model->getAmount());
        $this->assertEquals('Jean', $model->getFirstName());
    }

    public function testShowSaveInfoOptionAddsField(): void
    {
        $model = new Donation();
        $form = $this->factory->create(DonationType::class, $model, ['show_save_info' => true]);

        $this->assertTrue($form->has('saveInfo'));
    }

    public function testChoicesAreBuiltFromProjectRepository(): void
    {
        $model = new Donation();
        $form = $this->factory->create(DonationType::class, $model);

        $config = $form->get('type')->getConfig();
        $choiceList = $config->getOption('choices');

        $this->assertArrayHasKey('Construction de puits', $choiceList);
        $this->assertEquals('puits', $choiceList['Construction de puits']);
    }
}