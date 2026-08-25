<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\AdminProjectType;
use App\Repository\DonationRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\GeocodingService;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function dashboard(ProjectRepository $projectRepository, DonationRepository $donationRepository, UserRepository $userRepository): Response
    {
        $donations = $donationRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/dashboard.html.twig', [
            'projects' => $projectRepository->findBy([], ['id' => 'DESC']),
            'donations' => $donations,
            'users' => $userRepository->findBy([], ['id' => 'DESC']),
            'totalDonations' => array_reduce($donations, static fn (float $total, $donation): float => $total + (float) $donation->getAmount(), 0.0),
        ]);
    }

    #[Route('/projects/new', name: 'admin_project_new')]
    public function newProject(Request $request, EntityManagerInterface $entityManager, GeocodingService $geocodingService): Response
    {
        $project = new Project();
        $project->setData([]);

        return $this->handleProjectForm($request, $entityManager, $geocodingService, $project, 'Projet cree.');
    }

    #[Route('/projects/{id}/edit', name: 'admin_project_edit')]
    public function editProject(Project $project, Request $request, EntityManagerInterface $entityManager, GeocodingService $geocodingService): Response
    {
        return $this->handleProjectForm($request, $entityManager, $geocodingService, $project, 'Projet modifie.');
    }

    #[Route('/projects/{id}/delete', name: 'admin_project_delete', methods: ['POST'])]
    public function deleteProject(Project $project, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_project_' . $project->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();
            $this->addFlash('success', 'Projet supprime.');
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    private function handleProjectForm(Request $request, EntityManagerInterface $entityManager, GeocodingService $geocodingService, Project $project, string $successMessage): Response
    {
        $form = $this->createForm(AdminProjectType::class, $project);
        $form->get('dataJson')->setData(json_encode($project->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dataJson = trim((string) $form->get('dataJson')->getData());
            $decodedData = $dataJson === '' ? [] : json_decode($dataJson, true);

            if ($decodedData === null && json_last_error() !== JSON_ERROR_NONE) {
                $this->addFlash('danger', 'Le JSON des donnees est invalide.');
            } else {
                if (is_array($decodedData)) {
                    foreach ($decodedData as &$item) {
                        if (is_array($item) && !empty($item['country'])) {
                            // Géocodage automatique via API externe si lat/lng non fournies ou en cas de mise à jour du pays
                            if (!isset($item['lat']) || !isset($item['lng'])) {
                                $coords = $geocodingService->getCoordinatesForCountry($item['country']);
                                if ($coords) {
                                    $item['lat'] = $coords['lat'];
                                    $item['lng'] = $coords['lng'];
                                }
                            }
                        }
                    }
                    unset($item);
                }

                $project->setData($decodedData);
                $entityManager->persist($project);
                $entityManager->flush();
                $this->addFlash('success', $successMessage);

                return $this->redirectToRoute('admin_dashboard');
            }
        }

        return $this->render('admin/project_form.html.twig', [
            'form' => $form->createView(),
            'project' => $project,
        ]);
    }
}
