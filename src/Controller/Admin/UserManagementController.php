<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserManagementController extends AbstractController
{
    #[Route('', name: 'app_admin_users')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $search = $request->query->get('search', '');
        
        if ($search) {
            $users = $userRepository->search($search);
        } else {
            $users = $userRepository->findBy([], ['createdAt' => 'DESC']);
        }

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_show', requirements: ['id' => '\d+'])]
    public function show(User $user): Response
    {
        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/ban', name: 'app_admin_user_ban', methods: ['POST'])]
    public function ban(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('ban-user-' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_users');
        }

        // Empêcher de se bannir soi-même
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas vous bannir vous-même.');
            return $this->redirectToRoute('app_admin_users');
        }

        // Bannir l'utilisateur
        $user->setIsBanned(true);
        $entityManager->flush();

        $this->addFlash('success', sprintf('L\'utilisateur "%s" a été banni.', $user->getUsername()));

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/{id}/unban', name: 'app_admin_user_unban', methods: ['POST'])]
    public function unban(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('unban-user-' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_users');
        }

        // Débannir l'utilisateur
        $user->setIsBanned(false);
        $entityManager->flush();

        $this->addFlash('success', sprintf('L\'utilisateur "%s" a été débanni.', $user->getUsername()));

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/{id}/promote', name: 'app_admin_user_promote', methods: ['POST'])]
    public function promote(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('promote-user-' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_users');
        }

        // Ajouter le rôle admin
        $roles = $user->getRoles();
        if (!in_array('ROLE_ADMIN', $roles)) {
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles($roles);
            $entityManager->flush();
            $this->addFlash('success', sprintf('L\'utilisateur "%s" a été promu administrateur.', $user->getUsername()));
        } else {
            $this->addFlash('warning', 'Cet utilisateur est déjà administrateur.');
        }

        return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()]);
    }

    #[Route('/{id}/demote', name: 'app_admin_user_demote', methods: ['POST'])]
    public function demote(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('demote-user-' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_admin_users');
        }

        // Empêcher de se rétrograder soi-même
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas vous rétrograder vous-même.');
            return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()]);
        }

        // Retirer le rôle admin
        $roles = array_filter($user->getRoles(), fn($role) => $role !== 'ROLE_ADMIN');
        $user->setRoles(array_values($roles));
        $entityManager->flush();

        $this->addFlash('success', sprintf('L\'utilisateur "%s" n\'est plus administrateur.', $user->getUsername()));

        return $this->redirectToRoute('app_admin_user_show', ['id' => $user->getId()]);
    }
}
