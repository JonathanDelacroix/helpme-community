<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProjectRepository;

class ProjectController extends AbstractController
{
    #[Route('/projet/{slug}', name: 'project_show')]
    public function show(ProjectRepository $projectRepository, string $slug): Response
    {
        // Récupère le projet par son slug
        $project = $projectRepository->findOneBy(['slug' => $slug]);

        if (!$project) {
            throw $this->createNotFoundException('Projet introuvable');
        }

        return $this->render('project/show.html.twig', [
            'project' => $project
        ]);
    }

    public function nav(ProjectRepository $projectRepository): Response
    {
        return $this->render('project/_nav_links.html.twig', [
            'projects' => $projectRepository->findAll()
        ]);
    }
}
