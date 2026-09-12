<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiTokenAccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        $hashedToken = hash('sha256', $accessToken);

        $user = $this->userRepository->findOneBy(['apiToken' => $hashedToken]);

        if (null === $user) {
            throw new BadCredentialsException('Invalid or expired API token.');
        }

        if (null === $user->getApiTokenExpiresAt() || $user->getApiTokenExpiresAt() < new \DateTimeImmutable()) {
            throw new BadCredentialsException('Invalid or expired API token.');
        }

        return new UserBadge($user->getUserIdentifier());
    }
}