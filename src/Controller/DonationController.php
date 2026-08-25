<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Form\DonationType;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DonationController extends AbstractController
{
    #[Route('/don', name: 'donation')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $donation = new Donation();
        $form = $this->createForm(DonationType::class, $donation);
        $form->handleRequest($request);

        $stripePublicKey = $this->getParameter('stripe_public_key');

        if ($form->isSubmitted() && $form->isValid()) {
            $donation->setDonor($this->getUser());
            $em->persist($donation);
            $em->flush();
            // On ne redirige pas ici, le paiement se fera via Stripe
        }

        return $this->render('donation/index.html.twig', [
            'form' => $form->createView(),
            'stripePublicKey' => $stripePublicKey,
        ]);
    }

    #[Route('/create-checkout-session', name: 'create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Récupération des données depuis le formulaire
        $type      = $request->request->get('type');
        $amount    = $request->request->get('amount');
        $firstName = $request->request->get('firstName');
        $lastName  = $request->request->get('lastName');
        $email     = $request->request->get('email');
        $address   = $request->request->get('address');
        $city      = $request->request->get('city');
        $zip       = $request->request->get('zip');
        $country   = $request->request->get('country');

        // Validation simple côté serveur
        if (!$type || !$amount || floatval($amount) <= 0 || !$firstName || !$lastName || !$email) {
            return new JsonResponse(['error' => 'Champs invalides.'], 400);
        }

        // Création de l'objet Donation
        $donation = new Donation();
        $donation->setType($type)
                ->setAmount(floatval($amount))
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setEmail($email)
                ->setAddress($address)
                ->setCity($city)
                ->setZip($zip)
                ->setCountry($country)
                ->setDonor($this->getUser()); // si l'utilisateur est connecté

        $em->persist($donation);
        $em->flush(); // id disponible après flush

        // Stripe
        \Stripe\Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => 'Don : ' . $type],
                    'unit_amount' => intval(floatval($amount) * 100), // centimes
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $email, // facultatif, pré-remplit Stripe
            'success_url' => $this->generateUrl('donation_success', ['id' => $donation->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url'  => $this->generateUrl('donation_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
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
