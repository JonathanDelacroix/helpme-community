<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DonationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DonorController extends AbstractController
{
    #[Route('/donor/dashboard', name: 'donor_dashboard')]
    public function dashboard(DonationRepository $donationRepository)
    {
        $donor = $this->getUser();
        if (!$donor instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $donations = $donationRepository->findForUser($donor);

        return $this->render('donor/dashboard.html.twig', [
            'donations' => $donations
        ]);
    }
}
