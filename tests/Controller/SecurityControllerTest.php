<?php

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validation;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;

class SecurityControllerTest extends TestCase
{
    private function buildControllerWithFlashAndRouterSupport(EmailVerifier $emailVerifier): SecurityController
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $requestStack->push($request);

        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->method('generate')->willReturn('/login');

        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->addExtension(new HttpFoundationExtension())
            ->getFormFactory();

        $container = new Container();
        $container->set('request_stack', $requestStack);
        $container->set('router', $router);
        $container->set('form.factory', $formFactory);

        $controller = new SecurityController($emailVerifier);
        $controller->setContainer($container);

        return $controller;
    }

    public function testVerifyUserEmailWithoutIdRedirectsToRegister(): void
    {
        $emailVerifier = $this->createMock(EmailVerifier::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('getRepository');

        $controller = $this->buildControllerWithFlashAndRouterSupport($emailVerifier);
        $request = new Request();

        $response = $controller->verifyUserEmail($request, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testVerifyUserEmailWithUnknownUserRedirectsToRegister(): void
    {
        $emailVerifier = $this->createMock(EmailVerifier::class);
        $emailVerifier->expects($this->never())->method('handleEmailConfirmation');

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $controller = $this->buildControllerWithFlashAndRouterSupport($emailVerifier);
        $request = new Request(['id' => '999']);

        $response = $controller->verifyUserEmail($request, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testVerifyUserEmailWithInvalidSignatureRedirectsToRegister(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');

        $emailVerifier = $this->createMock(EmailVerifier::class);
        $emailVerifier->method('handleEmailConfirmation')
            ->willThrowException(new InvalidSignatureException());

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->willReturn($user);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);
        $em->expects($this->never())->method('flush');

        $controller = $this->buildControllerWithFlashAndRouterSupport($emailVerifier);
        $request = new Request(['id' => '1']);

        $response = $controller->verifyUserEmail($request, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testVerifyUserEmailSuccessRedirectsToLogin(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');

        $emailVerifier = $this->createMock(EmailVerifier::class);
        $emailVerifier->expects($this->once())->method('handleEmailConfirmation');

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->willReturn($user);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);
        $em->expects($this->once())->method('flush');

        $controller = $this->buildControllerWithFlashAndRouterSupport($emailVerifier);
        $request = new Request(['id' => '1']);

        $response = $controller->verifyUserEmail($request, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testLogoutThrowsLogicException(): void
    {
        $emailVerifier = $this->createMock(EmailVerifier::class);
        $controller = new SecurityController($emailVerifier);

        $this->expectException(\LogicException::class);
        $controller->logout();
    }

    public function testRegisterWithValidDataCreatesUserAndSendsEmail(): void
    {
        $emailVerifier = $this->createMock(EmailVerifier::class);
        $emailVerifier->expects($this->once())->method('sendEmailConfirmation');

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')->willReturn('hashed_password');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $controller = $this->buildControllerWithFlashAndRouterSupport($emailVerifier);
        $request = new Request([], [
            'registration_form' => [
                'email' => 'nouveau@test.com',
                'plainPassword' => 'MotDePasse123!',
                'agreeTerms' => '1',
            ],
        ]);
        $request->setMethod('POST');

        $response = $controller->register($request, $passwordHasher, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}