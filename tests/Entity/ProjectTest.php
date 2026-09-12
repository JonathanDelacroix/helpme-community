<?php

namespace App\Tests\Entity;

use App\Entity\Project;
use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    public function testGetIdIsNullBeforePersist(): void
    {
        $project = new Project();
        $this->assertNull($project->getId());
    }

    public function testSettersAndGetters(): void
    {
        $project = new Project();

        $project->setTitle('Construction de puits');
        $project->setDescription('Accès à l\'eau potable pour tous.');
        $project->setSlug('puits');
        $project->setImage('https://exemple.com/image.jpg');
        $project->setData([['country' => 'Maroc', 'count' => 40]]);

        $this->assertEquals('Construction de puits', $project->getTitle());
        $this->assertEquals('Accès à l\'eau potable pour tous.', $project->getDescription());
        $this->assertEquals('puits', $project->getSlug());
        $this->assertEquals('https://exemple.com/image.jpg', $project->getImage());
        $this->assertCount(1, $project->getData());
    }

    public function testImageIsNullableByDefault(): void
    {
        $project = new Project();
        $this->assertNull($project->getImage());
    }

    public function testDataDefaultsToEmptyArray(): void
    {
        $project = new Project();
        $this->assertEquals([], $project->getData());
    }
}