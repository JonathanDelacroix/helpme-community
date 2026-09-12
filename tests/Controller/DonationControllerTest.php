<?php

namespace App\Tests\Controller;

use App\Controller\DonationController;
use App\Entity\Donation;
use App\Entity\Project;
use App\Entity\User;
use App\Form\DonationType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validation;

class DonationControllerTest extends TestCase
{
    private function buildControllerWithFlashAndRouterSupport(): DonationController
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $requestStack->push($request);

        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->method('generate')->willReturn('/don');

        $container = new Container();
        $container->set('request_stack', $requestStack);
        $container->set('router', $router);

        $controller = new DonationController();
        $controller->setContainer($container);

        return $controller;
    }

    private function buildControllerForIndex(?User $loggedInUser = null): array
    {
        $project = new Project();
        $project->setTitle('Construction de puits');
        $project->setSlug('puits');
        $project->setDescription('desc');

        $projectRepository = $this->createMock(ProjectRepository::class);
        $projectRepository->method('findAll')->willReturn([$project]);

        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->addExtension(new HttpFoundationExtension())
            ->addType(new DonationType($projectRepository))
            ->getFormFactory();

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        if ($loggedInUser) {
            $token = $this->createMock(\Symfony\Component\Security\Core\Authentication\Token\TokenInterface::class);
            $token->method('getUser')->willReturn($loggedInUser);
            $tokenStorage->method('getToken')->willReturn($token);
        } else {
            $tokenStorage->method('getToken')->willReturn(null);
        }

        $twig = $this->createMock(\Twig\Environment::class);
        $twig->method('render')->willReturn('<html></html>');

        $container = new Container();

        $parameterBag = $this->createMock(\Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface::class);
        $parameterBag->method('get')->willReturn('pk_test_fake');

        $container->set('parameter_bag', $parameterBag);
        $container->set('form.factory', $formFactory);
        $container->set('security.token_storage', $tokenStorage);
        $container->set('twig', $twig);

        $entityRepository = $this->createMock(EntityRepository::class);
        $entityRepository->method('findOneBy')->willReturnCallback(
            function (array $criteria) use ($project) {
                if (isset($criteria['slug']) && $criteria['slug'] === 'puits') {
                    return $project;
                }
                return $project; // premier projet par defaut
            }
        );

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($entityRepository);

        $controller = new DonationController();
        $controller->setContainer($container);

        return [$controller, $em, $project];
    }

    public function testCreateCheckoutSessionMissingTypeReturnsBadRequest(): void
    {
        $request = new Request([], [
            'amount' => '50',
            'firstName' => 'Jean',
            'lastName' => 'Dupont',
            'email' => 'jean@test.com',
        ]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');

        $controller = new DonationController();
        $response = $controller->createCheckoutSession($request, $em);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Champs invalides.', $content['error']);
    }

    public function testCreateCheckoutSessionZeroAmountReturnsBadRequest(): void
    {
        $request = new Request([], [
            'type' => 'puits',
            'amount' => '0',
            'firstName' => 'Jean',
            'lastName' => 'Dupont',
            'email' => 'jean@test.com',
        ]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');

        $controller = new DonationController();
        $response = $controller->createCheckoutSession($request, $em);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateCheckoutSessionMissingEmailReturnsBadRequest(): void
    {
        $request = new Request([], [
            'type' => 'puits',
            'amount' => '50',
            'firstName' => 'Jean',
            'lastName' => 'Dupont',
        ]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');

        $controller = new DonationController();
        $response = $controller->createCheckoutSession($request, $em);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCancelWithoutSessionIdJustRedirects(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('getRepository');

        $controller = $this->buildControllerWithFlashAndRouterSupport();
        $request = new Request();

        $response = $controller->cancel($request, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testCancelMarksPendingDonationAsCancelled(): void
    {
        $donation = new Donation();
        $donation->setStatus(Donation::STATUS_PENDING);
        $donation->setStripeSessionId('cs_test_abc123');

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['stripeSessionId' => 'cs_test_abc123'])
            ->willReturn($donation);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);
        $em->expects($this->once())->method('flush');

        $controller = $this->buildControllerWithFlashAndRouterSupport();
        $request = new Request(['session_id' => 'cs_test_abc123']);

        $controller->cancel($request, $em);

        $this->assertEquals(Donation::STATUS_CANCELLED, $donation->getStatus());
    }

    public function testCancelDoesNotChangeAlreadyPaidDonation(): void
    {
        $donation = new Donation();
        $donation->setStatus(Donation::STATUS_PAID);
        $donation->setStripeSessionId('cs_test_abc123');

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn($donation);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);
        $em->expects($this->never())->method('flush');

        $controller = $this->buildControllerWithFlashAndRouterSupport();
        $request = new Request(['session_id' => 'cs_test_abc123']);

        $controller->cancel($request, $em);

        $this->assertEquals(Donation::STATUS_PAID, $donation->getStatus());
    }

    public function testIndexWithoutQueryParamsUsesDefaults(): void
    {
        [$controller, $em] = $this->buildControllerForIndex();
        $request = new Request();

        $response = $controller->index($request, $em);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testIndexWithAmountQueryParamPrefillsAmount(): void
    {
        [$controller, $em] = $this->buildControllerForIndex();
        $request = new Request(['amount' => '250']);

        $response = $controller->index($request, $em);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testIndexWithInvalidAmountFallsBackToDefault(): void
    {
        [$controller, $em] = $this->buildControllerForIndex();
        $request = new Request(['amount' => 'pas-un-nombre']);

        $response = $controller->index($request, $em);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testIndexWithTypeQueryParamPrefillsProject(): void
    {
        [$controller, $em] = $this->buildControllerForIndex();
        $request = new Request(['type' => 'puits']);

        $response = $controller->index($request, $em);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testIndexForLoggedInUserPrefillsPersonalInfo(): void
    {
        $user = new User();
        $user->setEmail('donor@test.com');
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');

        [$controller, $em] = $this->buildControllerForIndex($user);
        $request = new Request();

        $response = $controller->index($request, $em);

        $this->assertEquals(200, $response->getStatusCode());
    }
}