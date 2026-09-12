<?php

namespace App\Tests\Controller;

use App\Controller\AdminController;
use App\Entity\Project;
use App\Service\GeocodingService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;

class AdminControllerTest extends TestCase
{
    private function buildController(bool $csrfValid = true): AdminController
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $requestStack->push($request);

        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->method('generate')->willReturn('/admin');

        $csrfManager = $this->createMock(CsrfTokenManagerInterface::class);
        $csrfManager->method('isTokenValid')->willReturn($csrfValid);

        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->addExtension(new HttpFoundationExtension())
            ->getFormFactory();

        $container = new Container();
        $container->set('request_stack', $requestStack);
        $container->set('router', $router);
        $container->set('security.csrf.token_manager', $csrfManager);
        $container->set('form.factory', $formFactory);

        $controller = new AdminController();
        $controller->setContainer($container);

        return $controller;
    }

    public function testDeleteProjectWithValidCsrfRemovesProject(): void
    {
        $project = new Project();
        $project->setTitle('Test');
        $project->setDescription('desc');
        $project->setSlug('test');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('remove')->with($project);
        $em->expects($this->once())->method('flush');

        $controller = $this->buildController(true);
        $request = new Request([], ['_token' => 'valid_token']);

        $response = $controller->deleteProject($project, $request, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testDeleteProjectWithInvalidCsrfDoesNotRemoveProject(): void
    {
        $project = new Project();
        $project->setTitle('Test');
        $project->setDescription('desc');
        $project->setSlug('test');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('remove');
        $em->expects($this->never())->method('flush');

        $controller = $this->buildController(false);
        $request = new Request([], ['_token' => 'invalid_token']);

        $response = $controller->deleteProject($project, $request, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testNewProjectWithValidDataRedirectsToDashboard(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $geocodingService = $this->createMock(GeocodingService::class);
        $geocodingService->expects($this->never())->method('getCoordinatesForCountry');

        $controller = $this->buildController();
        $request = new Request([], [
            'admin_project' => [
                'title' => 'Construction de puits',
                'slug' => 'puits',
                'image' => '',
                'description' => 'Description du projet',
                'dataJson' => '[]',
            ],
        ], [], [], [], ['REQUEST_METHOD' => 'POST']);
        $request->setMethod('POST');

        $response = $controller->newProject($request, $em, $geocodingService);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testNewProjectWithInvalidJsonShowsError(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');

        $geocodingService = $this->createMock(GeocodingService::class);

        // On ajoute un service 'twig' minimal, car un JSON invalide renvoie
        // vers le formulaire (rendu Twig) au lieu de rediriger.
        $twig = $this->createMock(\Twig\Environment::class);
        $twig->method('render')->willReturn('<html></html>');

        $controller = $this->buildController();
        $refContainer = new \ReflectionProperty($controller, 'container');
        $refContainer->setAccessible(true);
        $existingContainer = $refContainer->getValue($controller);
        $existingContainer->set('twig', $twig);

        $request = new Request([], [
            'admin_project' => [
                'title' => 'Test',
                'slug' => 'test',
                'image' => '',
                'description' => 'Description',
                'dataJson' => '{invalide',
            ],
        ]);
        $request->setMethod('POST');

        $response = $controller->newProject($request, $em, $geocodingService);

        $this->assertEquals(200, $response->getStatusCode());
    }
}