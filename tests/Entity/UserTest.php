<?php

namespace App\Tests\Entity;

use App\Entity\Donation;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testConstructorInitializesDonationsCollection(): void
    {
        $user = new User();
        $this->assertCount(0, $user->getDonations());
    }

    public function testEmailGetterAndSetter(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('test@example.com', $user->getUserIdentifier());
    }

    public function testRolesAlwaysIncludeRoleUser(): void
    {
        $user = new User();
        $user->setRoles([]);

        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testRolesIncludeAdminWhenSet(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testRolesAreUnique(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER', 'ROLE_USER']);

        $roles = $user->getRoles();
        $this->assertCount(1, $roles);
    }

    public function testPasswordGetterAndSetter(): void
    {
        $user = new User();
        $user->setPassword('hashed_password_value');

        $this->assertEquals('hashed_password_value', $user->getPassword());
    }

    public function testApiTokenGetterAndSetter(): void
    {
        $user = new User();
        $user->setApiToken('some_hashed_token');

        $this->assertEquals('some_hashed_token', $user->getApiToken());
    }

    public function testApiTokenExpiresAtGetterAndSetter(): void
    {
        $user = new User();
        $date = new \DateTimeImmutable('+24 hours');
        $user->setApiTokenExpiresAt($date);

        $this->assertSame($date, $user->getApiTokenExpiresAt());
    }

    public function testPersonalInfoGettersAndSetters(): void
    {
        $user = new User();

        $user->setFirstName('Marie');
        $user->setLastName('Curie');
        $user->setAddress('1 rue de la Science');
        $user->setCity('Paris');
        $user->setZip('75005');
        $user->setCountry('France');

        $this->assertEquals('Marie', $user->getFirstName());
        $this->assertEquals('Curie', $user->getLastName());
        $this->assertEquals('1 rue de la Science', $user->getAddress());
        $this->assertEquals('Paris', $user->getCity());
        $this->assertEquals('75005', $user->getZip());
        $this->assertEquals('France', $user->getCountry());
    }

    public function testIsVerifiedDefaultsFalse(): void
    {
        $user = new User();
        $this->assertFalse($user->isVerified());
    }

    public function testSetVerified(): void
    {
        $user = new User();
        $user->setVerified(true);

        $this->assertTrue($user->isVerified());
    }

    public function testAddDonationLinksBothSides(): void
    {
        $user = new User();
        $donation = new Donation();

        $user->addDonation($donation);

        $this->assertCount(1, $user->getDonations());
        $this->assertTrue($user->getDonations()->contains($donation));
        $this->assertSame($user, $donation->getDonor());
    }

    public function testAddDonationTwiceDoesNotDuplicate(): void
    {
        $user = new User();
        $donation = new Donation();

        $user->addDonation($donation);
        $user->addDonation($donation);

        $this->assertCount(1, $user->getDonations());
    }

    public function testRemoveDonationUnlinksBothSides(): void
    {
        $user = new User();
        $donation = new Donation();

        $user->addDonation($donation);
        $user->removeDonation($donation);

        $this->assertCount(0, $user->getDonations());
        $this->assertNull($donation->getDonor());
    }

    public function testEraseCredentialsDoesNotThrow(): void
    {
        $user = new User();
        $user->eraseCredentials();

        $this->assertTrue(true);
    }
}