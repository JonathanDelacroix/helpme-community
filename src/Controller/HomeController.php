<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProjectRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAll();

        // Fusion des données de tous les projets
        $allData = [];
        foreach ($projects as $project) {
            if (!empty($project->getData())) {
                foreach ($project->getData() as $item) {
                    $allData[] = $item;
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'projects' => $projects,
            'allData' => $allData
        ]);
    }
}
