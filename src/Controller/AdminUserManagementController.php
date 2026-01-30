<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminUserManagementController extends AbstractController
{
    #[Route('/admin/user/management', name: 'app_admin_user_management')]
    public function index(): Response
    {
        return $this->render('admin_user_management/index.html.twig', [
            'controller_name' => 'AdminUserManagementController',
        ]);
    }
}
