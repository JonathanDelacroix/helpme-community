<?php

namespace App\Tests\Controller;

use App\Controller\ApiController;
use App\Entity\Donation;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\DonationRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Security\ApiTokenAccessTokenHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiControllerTest extends TestCase
{
    public function testGetProjectsReturnsJsonArray(): void
    {
        $project = new Project();
        $project->setTitle('Projet Eau');
        $project->setDescription('Accès eau potable');
        $project->setSlug('projet-eau');
        $project->setData([['country' => 'Maroc']]);

        $projectRepository = $this->createMock(ProjectRepository::class);
        $projectRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$project]);

        $controller = new ApiController();
        $response = $controller->getProjects($projectRepository);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertEquals('Projet Eau', $content[0]['title']);
        $this->assertEquals('projet-eau', $content[0]['slug']);
    }

    public function testGetProjectReturnsProjectDetails(): void
    {
        $project = new Project();
        $project->setTitle('Scolarité');
        $project->setDescription('Fournitures scolaires');
        $project->setSlug('scolarite');

        $controller = new ApiController();
        $response = $controller->getProject($project);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Scolarité', $content['title']);
    }

    public function testGetProjectNotFoundReturns404(): void
    {
        $controller = new ApiController();
        $response = $controller->getProject(null);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Project not found', $content['error']);
    }

    public function testGetStatsReturnsMetrics(): void
    {
        $projectRepository = $this->createMock(ProjectRepository::class);
        $projectRepository->expects($this->once())
            ->method('count')
            ->with([])
            ->willReturn(3);

        $donation1 = new Donation();
        $donation1->setAmount(50.0);

        $donation2 = new Donation();
        $donation2->setAmount(100.0);

        $donationRepository = $this->createMock(DonationRepository::class);
        $donationRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$donation1, $donation2]);

        $controller = new ApiController();
        $response = $controller->getStats($projectRepository, $donationRepository);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals(3, $content['totalProjects']);
        $this->assertEquals(2, $content['totalDonationsCount']);
        $this->assertEquals(150.0, $content['totalDonationsAmount']);
    }

    public function testLoginWithoutCredentialsReturnsBadRequest(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([]));
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $controller = new ApiController();
        $response = $controller->login($request, $userRepository, $passwordHasher, $em);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Champs email et password requis.', $content['error']);
    }

    public function testLoginWithInvalidCredentialsReturnsUnauthorized(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => 'unknown@test.com',
            'password' => 'wrongpass'
        ]));

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'unknown@test.com'])
            ->willReturn(null);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $controller = new ApiController();
        $response = $controller->login($request, $userRepository, $passwordHasher, $em);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Identifiants invalides.', $content['error']);
    }

    public function testLoginWithValidCredentialsGeneratesToken(): void
    {
        $user = new User();
        $user->setEmail('donor@test.com');

        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => 'donor@test.com',
            'password' => 'secret123'
        ]));

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'donor@test.com'])
            ->willReturn($user);

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, 'secret123')
            ->willReturn(true);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $controller = new ApiController();
        $response = $controller->login($request, $userRepository, $passwordHasher, $em);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('token', $content);
        $this->assertNotEmpty($content['token']);
        $this->assertEquals('donor@test.com', $content['user']['email']);
        $this->assertEquals($content['token'], $user->getApiToken());
    }

    public function testMeUnauthenticatedReturnsUnauthorized(): void
    {
        $controller = new ApiController();
        $response = $controller->me();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Vous devez être connecté avec un token d\'accès valide.', $content['error']);
    }

    public function testApiTokenAccessTokenHandlerValidToken(): void
    {
        $user = new User();
        $user->setEmail('apiuser@test.com');
        $user->setApiToken('valid_token_123');

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['apiToken' => 'valid_token_123'])
            ->willReturn($user);

        $handler = new ApiTokenAccessTokenHandler($userRepository);
        $badge = $handler->getUserBadgeFrom('valid_token_123');

        $this->assertInstanceOf(UserBadge::class, $badge);
        $this->assertEquals('apiuser@test.com', $badge->getUserIdentifier());
    }

    public function testApiTokenAccessTokenHandlerInvalidTokenThrowsException(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['apiToken' => 'invalid_token'])
            ->willReturn(null);

        $handler = new ApiTokenAccessTokenHandler($userRepository);

        $this->expectException(BadCredentialsException::class);
        $handler->getUserBadgeFrom('invalid_token');
    }
}
