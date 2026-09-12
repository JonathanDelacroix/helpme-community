<?php

namespace App\Tests\Entity;

use App\Entity\Donation;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class DonationTest extends TestCase
{
    public function testConstructorSetsCreatedAt(): void
    {
        $donation = new Donation();
        $this->assertInstanceOf(\DateTimeImmutable::class, $donation->getCreatedAt());
    }

    public function testDefaultStatusIsPending(): void
    {
        $donation = new Donation();
        $this->assertEquals(Donation::STATUS_PENDING, $donation->getStatus());
        $this->assertFalse($donation->isPaid());
    }

    public function testSettersAndGetters(): void
    {
        $donation = new Donation();
        $donor = new User();

        $donation->setType('puits');
        $donation->setAmount(150.0);
        $donation->setDonor($donor);
        $donation->setFirstName('Jean');
        $donation->setLastName('Dupont');
        $donation->setEmail('jean.dupont@test.com');
        $donation->setAddress('12 rue de Paris');
        $donation->setCity('Paris');
        $donation->setZip('75001');
        $donation->setCountry('France');
        $donation->setStripeSessionId('cs_test_abc123');

        $this->assertEquals('puits', $donation->getType());
        $this->assertEquals(150.0, $donation->getAmount());
        $this->assertSame($donor, $donation->getDonor());
        $this->assertEquals('Jean', $donation->getFirstName());
        $this->assertEquals('Dupont', $donation->getLastName());
        $this->assertEquals('jean.dupont@test.com', $donation->getEmail());
        $this->assertEquals('12 rue de Paris', $donation->getAddress());
        $this->assertEquals('Paris', $donation->getCity());
        $this->assertEquals('75001', $donation->getZip());
        $this->assertEquals('France', $donation->getCountry());
        $this->assertEquals('cs_test_abc123', $donation->getStripeSessionId());
    }

    public function testStatusTransitionToPaid(): void
    {
        $donation = new Donation();
        $donation->setStatus(Donation::STATUS_PAID);

        $this->assertTrue($donation->isPaid());
        $this->assertEquals(Donation::STATUS_PAID, $donation->getStatus());
    }

    public function testStatusTransitionToCancelled(): void
    {
        $donation = new Donation();
        $donation->setStatus(Donation::STATUS_CANCELLED);

        $this->assertFalse($donation->isPaid());
        $this->assertEquals(Donation::STATUS_CANCELLED, $donation->getStatus());
    }

    public function testSetCreatedAt(): void
    {
        $donation = new Donation();
        $date = new \DateTimeImmutable('2026-01-15');
        $donation->setCreatedAt($date);

        $this->assertSame($date, $donation->getCreatedAt());
    }

    public function testGetIdIsNullBeforePersist(): void
    {
        $donation = new Donation();
        $this->assertNull($donation->getId());
    }
}