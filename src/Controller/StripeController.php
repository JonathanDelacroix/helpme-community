<?php

namespace App\Controller;

use App\Entity\Donation;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
#[Route('/create-checkout-session', name: 'create_checkout_session', methods: ['POST'])]
public function checkout(Request $request, EntityManagerInterface $em): JsonResponse
{
    $type = $request->request->get('type');
    $amount = $request->request->get('amount');

    $donation = new Donation();
    $donation->setType($type);
    $donation->setAmount($amount);
    $donation->setDonor($this->getUser());

    // PERSISTE AVANT pour générer l'ID
    $em->persist($donation);
    $em->flush();

    // Stripe
    \Stripe\Stripe::setApiKey($this->getParameter('stripe_secret_key'));

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => 'Don ' . $type],
                'unit_amount' => (int)($amount * 100),
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $this->generateUrl('donation_success', ['id' => $donation->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
        'cancel_url' => $this->generateUrl('donation_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
    ]);

    return new JsonResponse(['id' => $session->id]);
}


    #[Route('/donation-success/{id}', name: 'donation_success')]
    public function success(Donation $donation)
    {
        $this->addFlash('success', 'Merci pour votre don !');
        return $this->redirectToRoute('donor_dashboard');
    }

    #[Route('/donation-cancel', name: 'donation_cancel')]
    public function cancel()
    {
        $this->addFlash('danger', 'Le paiement a été annulé.');
        return $this->redirectToRoute('donation');
    }

}
