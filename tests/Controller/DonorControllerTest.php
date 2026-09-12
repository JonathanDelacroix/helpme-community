<?php

namespace App\Tests\Controller;

use App\Controller\DonorController;
use App\Repository\DonationRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DonorControllerTest extends TestCase
{
    public function testDashboardThrowsAccessDeniedForNonUser(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn(null);

        $container = new Container();
        $container->set('security.token_storage', $tokenStorage);

        $controller = new DonorController();
        $controller->setContainer($container);

        $donationRepository = $this->createMock(DonationRepository::class);

        $this->expectException(AccessDeniedException::class);
        $controller->dashboard($donationRepository);
    }
}