<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Form\DonationType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\User;

class DonationController extends AbstractController
{
    #[Route('/don', name: 'donation')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $donation = new Donation();
        $user = $this->getUser();

        $prefillAmount = $request->query->get('amount');
        if ($prefillAmount !== null && is_numeric($prefillAmount) && (float) $prefillAmount > 0) {
            $donation->setAmount((float) $prefillAmount);
        } else {
            $donation->setAmount(100); // montant par défaut = celui mis en avant ("Populaire")
        }

        $donation->setType('puits'); 

        // Préremplissage des coordonnées déjà sauvegardées par l'utilisateur connecté
        if ($user instanceof User) {
            $donation->setEmail($user->getEmail());
            if ($user->getFirstName()) { $donation->setFirstName($user->getFirstName()); }
            if ($user->getLastName())  { $donation->setLastName($user->getLastName()); }
            if ($user->getAddress())   { $donation->setAddress($user->getAddress()); }
            if ($user->getCity())      { $donation->setCity($user->getCity()); }
            if ($user->getZip())       { $donation->setZip($user->getZip()); }
            if ($user->getCountry())   { $donation->setCountry($user->getCountry()); }
        }

        $form = $this->createForm(DonationType::class, $donation, [
            'show_save_info' => $user instanceof User,
        ]);

        $stripePublicKey = $this->getParameter('stripe_public_key');

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
        $saveInfo  = $request->request->getBoolean('saveInfo');

        // Validation simple côté serveur
        if (!$type || !$amount || floatval($amount) <= 0 || !$firstName || !$lastName || !$email) {
            return new JsonResponse(['error' => 'Champs invalides.'], 400);
        }

        $user = $this->getUser();

        // Création de l'objet Donation, en attente de confirmation du paiement
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
                ->setStatus(Donation::STATUS_PENDING)
                ->setDonor($user); // si l'utilisateur est connecté

        $em->persist($donation);

        // Sauvegarde des coordonnées sur le compte si la case est cochée
        if ($user instanceof User && $saveInfo) {
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setAddress($address);
            $user->setCity($city);
            $user->setZip($zip);
            $user->setCountry($country);
            $em->persist($user);
        }

        $em->flush(); // id disponible après flush

        // Stripe
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $successUrl = $this->generateUrl('donation_success', ['id' => $donation->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            . '?session_id={CHECKOUT_SESSION_ID}';

        $session = Session::create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => 'Don : ' . $type],
                    'unit_amount' => intval(floatval($amount) * 100), // centimes
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
	    'managed_payments' => ['enabled' => false],
            'customer_email' => $email, // facultatif, pré-remplit Stripe
            'client_reference_id' => (string) $donation->getId(),
            'success_url' => $successUrl,
            'cancel_url'  => $this->generateUrl('donation_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        $donation->setStripeSessionId($session->id);
        $em->flush();

        return new JsonResponse(['id' => $session->id]);
    }

    #[Route('/donation-success/{id}', name: 'donation_success')]
    public function success(Donation $donation, Request $request, EntityManagerInterface $em, ?LoggerInterface $logger = null): Response
    {
        $sessionId = $request->query->get('session_id');

        // On ne fait confiance au retour Stripe qu'après verification aupres de l'API Stripe :
        // le simple fait d'atterrir sur cette page ne prouve pas que le paiement a abouti.
        if (!$donation->isPaid() && $sessionId && $sessionId === $donation->getStripeSessionId()) {
            try {
                Stripe::setApiKey($this->getParameter('stripe_secret_key'));
                $session = Session::retrieve($sessionId);

                if ($session->payment_status === 'paid') {
                    $donation->setStatus(Donation::STATUS_PAID);
                    $em->flush();
                }
            } catch (\Throwable $e) {
                $logger?->error('Erreur lors de la verification du paiement Stripe pour le don #' . $donation->getId() . ' : ' . $e->getMessage());
            }
        }

        if ($donation->isPaid()) {
            $this->addFlash('success', 'Merci pour votre don !');
        } else {
            $this->addFlash('warning', 'Votre don est en attente de confirmation du paiement.');
        }

        // Un don peut être fait sans compte (donateur invité) : on ne renvoie vers
        // l'espace donateur (protégé, ROLE_USER) que si l'utilisateur est bien connecté.
        if ($this->getUser()) {
            return $this->redirectToRoute('donor_dashboard');
        }

        return $this->redirectToRoute('home');
    }

    #[Route('/donation-cancel', name: 'donation_cancel')]
    public function cancel()
    {
        $this->addFlash('danger', 'Le paiement a été annulé.');
        return $this->redirectToRoute('donation');
    }
}
