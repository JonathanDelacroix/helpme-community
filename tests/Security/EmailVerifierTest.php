<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\EmailVerifier;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Double de test manuel, car VerifyEmailHelperInterface declare
 * validateEmailConfirmationFromRequest() uniquement via un commentaire
 * @method (non reconnu par les mocks PHPUnit), et la classe concrete
 * VerifyEmailHelper est declaree "final" (donc non mockable non plus).
 */
class FakeVerifyEmailHelper implements VerifyEmailHelperInterface
{
    public array $generateSignatureCalls = [];
    public array $validateCalls = [];
    public bool $shouldThrowOnValidate = false;

    public function generateSignature(string $routeName, string $userId, string $userEmail, array $extraParams = []): VerifyEmailSignatureComponents
    {
        $this->generateSignatureCalls[] = [$routeName, $userId, $userEmail, $extraParams];

        return new VerifyEmailSignatureComponents(
            new \DateTimeImmutable('+1 hour'),
            'https://exemple.com/verify?token=abc',
            time()
        );
    }

    public function validateEmailConfirmation(string $signedUrl, string $userId, string $userEmail): void
    {
    }

    public function validateEmailConfirmationFromRequest(Request $request, string $userId, string $userEmail): void
    {
        $this->validateCalls[] = [$userId, $userEmail];

        if ($this->shouldThrowOnValidate) {
            throw new \SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException();
        }
    }
}

class EmailVerifierTest extends TestCase
{
    public function testSendEmailConfirmationGeneratesSignatureAndSendsMail(): void
    {
        $user = new User();
        $user->setEmail('donor@test.com');

        $fakeHelper = new FakeVerifyEmailHelper();

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send');

        $emailVerifier = new EmailVerifier($fakeHelper, $mailer);
        $email = new TemplatedEmail();

        $emailVerifier->sendEmailConfirmation('app_verify_email', $user, $email);

        $context = $email->getContext();
        $this->assertEquals('https://exemple.com/verify?token=abc', $context['signedUrl']);
        $this->assertCount(1, $fakeHelper->generateSignatureCalls);
        $this->assertEquals('app_verify_email', $fakeHelper->generateSignatureCalls[0][0]);
    }

    public function testHandleEmailConfirmationMarksUserAsVerified(): void
    {
        $user = new User();
        $user->setEmail('donor@test.com');
        $user->setVerified(false);

        $fakeHelper = new FakeVerifyEmailHelper();
        $mailer = $this->createMock(MailerInterface::class);

        $emailVerifier = new EmailVerifier($fakeHelper, $mailer);
        $request = new Request();

        $emailVerifier->handleEmailConfirmation($request, $user);

        $this->assertTrue($user->isVerified());
        $this->assertCount(1, $fakeHelper->validateCalls);
    }
}