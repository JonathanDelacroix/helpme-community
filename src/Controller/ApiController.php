<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\DonationRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Champs email et password requis.'], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Identifiants invalides.'], Response::HTTP_UNAUTHORIZED);
        }

        // Génération d'un token sécurisé
        $token = bin2hex(random_bytes(32));
        $user->setApiToken($token);
        $em->flush();

        return new JsonResponse([
            'token' => $token,
            'user' => $this->serializeUser($user),
        ]);
    }

    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = null;
        if ($this->container && $this->container->has('security.token_storage')) {
            $user = $this->getUser();
        }

        if (!$user instanceof User) {
            return new JsonResponse(
                ['error' => 'Vous devez être connecté avec un token d\'accès valide.'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return new JsonResponse($this->serializeUser($user, true));
    }

    #[Route('/projects', name: 'api_projects_index', methods: ['GET'])]
    public function getProjects(ProjectRepository $projectRepository): JsonResponse
    {
        $projects = $projectRepository->findAll();
        $data = array_map([$this, 'serializeProject'], $projects);

        return new JsonResponse($data);
    }

    #[Route('/projects/{id}', name: 'api_projects_show', methods: ['GET'])]
    public function getProject(?Project $project): JsonResponse
    {
        if (!$project) {
            return new JsonResponse(['error' => 'Project not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializeProject($project));
    }

    #[Route('/stats', name: 'api_stats', methods: ['GET'])]
    public function getStats(ProjectRepository $projectRepository, DonationRepository $donationRepository): JsonResponse
    {
        $projectsCount = $projectRepository->count([]);
        $donations = $donationRepository->findAll();
        $totalAmount = array_reduce($donations, static fn (float $sum, $d): float => $sum + (float) $d->getAmount(), 0.0);

        return new JsonResponse([
            'totalProjects' => $projectsCount,
            'totalDonationsCount' => count($donations),
            'totalDonationsAmount' => $totalAmount,
        ]);
    }

    private function serializeProject(Project $project): array
    {
        return [
            'id' => $project->getId(),
            'title' => $project->getTitle(),
            'description' => $project->getDescription(),
            'slug' => $project->getSlug(),
            'image' => $project->getImage(),
            'data' => $project->getData(),
        ];
    }

    private function serializeUser(User $user, bool $includeDonations = false): array
    {
        $userData = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ];

        if ($includeDonations) {
            $donations = [];
            foreach ($user->getDonations() as $donation) {
                $donations[] = [
                    'id' => $donation->getId(),
                    'type' => $donation->getType(),
                    'amount' => $donation->getAmount(),
                    'city' => $donation->getCity(),
                    'country' => $donation->getCountry(),
                ];
            }
            $userData['donations'] = $donations;
        }

        return $userData;
    }
}
