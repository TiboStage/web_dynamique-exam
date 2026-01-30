<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'app_admin_dashboard')]
    public function index(UserRepository $userRepository): Response
    {
        // Récupérer les statistiques
        $stats = $userRepository->getStatistics();
        
        // Derniers inscrits
        $latestUsers = $userRepository->findLatestRegistered(5);

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'latestUsers' => $latestUsers,
        ]);
    }
}
