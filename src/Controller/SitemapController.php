<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'sitemap', defaults: ['_format' => 'xml'])]
    public function index(ProjectRepository $projectRepository, UrlGeneratorInterface $urlGenerator): Response
    {
        $urls = [];

        // Pages statiques principales
        $staticRoutes = ['home', 'donation', 'app_register', 'app_login'];
        foreach ($staticRoutes as $route) {
            $urls[] = [
                'loc' => $urlGenerator->generate($route, [], UrlGeneratorInterface::ABSOLUTE_URL),
                'priority' => $route === 'home' ? '1.0' : '0.6',
            ];
        }

        // Une entree par projet, mise a jour automatiquement si un projet
        // est ajoute/supprime depuis l'admin.
        foreach ($projectRepository->findAll() as $project) {
            $urls[] = [
                'loc' => $urlGenerator->generate('project_show', ['slug' => $project->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                'priority' => '0.8',
            ];
        }

        $response = $this->render('sitemap/index.xml.twig', ['urls' => $urls]);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}