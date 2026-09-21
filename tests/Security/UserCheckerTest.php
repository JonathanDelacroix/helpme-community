<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends TestCase
{
    public function testCheckPreAuthBlocksUnverifiedUser(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setVerified(false);

        $checker = new UserChecker();

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $checker->checkPreAuth($user);
    }

    public function testCheckPreAuthAllowsVerifiedUser(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setVerified(true);

        $checker = new UserChecker();
        $checker->checkPreAuth($user);

        $this->assertTrue(true);
    }

    public function testCheckPreAuthIgnoresNonAppUserInstances(): void
    {
        $otherUser = $this->createMock(UserInterface::class);

        $checker = new UserChecker();
        $checker->checkPreAuth($otherUser);

        $this->assertTrue(true);
    }

    public function testCheckPostAuthDoesNothing(): void
    {
        $user = new User();
        $user->setEmail('test@test.com');

        $checker = new UserChecker();
        $checker->checkPostAuth($user);

        $this->assertTrue(true);
    }
}