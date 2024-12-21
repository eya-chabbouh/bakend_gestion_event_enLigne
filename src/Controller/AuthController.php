<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthController extends AbstractController
{
    #[Route('/auth', name: 'auth_user', methods: ['POST'])] 
    public function authenticateUser(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les données envoyées dans la requête
        $data = json_decode($request->getContent(), true);
        $mail = $data['mail'] ?? null;  // Utiliser 'mail' au lieu de 'nom'
        $motdepasse = $data['motdepasse'] ?? null;

        // Vérifier si le mail ou mot de passe sont manquants
        if (!$mail || !$motdepasse) {
            return new JsonResponse(['error' => 'Nom ou mot de passe manquant.'], 400);
        }

        // Récupérer l'utilisateur depuis la base de données en fonction de l'adresse email
        $user = $entityManager->getRepository(Utilisateur::class)->findOneBy(['mail' => $mail]);

        // Si l'utilisateur n'est pas trouvé
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé.'], 404);
        }

        // Vérifier le mot de passe
        if ($user->getMotdepasse() !== $motdepasse) {
            return new JsonResponse(['error' => 'Mot de passe incorrect.'], 401);
        }

        // Vérifier le rôle et déterminer la redirection appropriée
        $role = $user->getRole();
        switch ($role) {
            case 'admin':
                $redirectUrl = '/admin/home';
                break;
            case 'organisateur':
                $redirectUrl = '/organisateur/dashboard';
                break;
            case 'participant':
                $redirectUrl = '/participant/dashboard';
                break;
            default:
                return new JsonResponse(['error' => 'Rôle inconnu.'], 403);
        }

        // Répondre avec un message de succès et la redirection
        return new JsonResponse([
            'message' => 'Authentification réussie',
            'role' => $user->getRole(),
            'redirect_url' => $redirectUrl
        ], 200);
    }        
}
