<?php

namespace App\Controller;
use App\Entity\Registre;
use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\RegistreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
class RegistreController extends AbstractController
{
    #[Route('/registre/inscription', name: 'inscription_evenement', methods: ['POST'])]
    public function inscrire(Request $request,EvenementRepository $evenementRepository,UtilisateurRepository $utilisateurRepository, PersistenceManagerRegistry $doctrine): JsonResponse {
        $entityManager = $doctrine->getManager();
        $data = json_decode($request->getContent(), true);
        $data = json_decode($request->getContent(), true);

if (!isset($data['evenement_id']) || !isset($data['utilisateur_id']) || !isset($data['nom']) || !isset($data['adresse']) || !isset($data['tel'])) {
    return new JsonResponse(['message' => 'Données manquantes'], JsonResponse::HTTP_BAD_REQUEST);
}


   

        $evenement = $evenementRepository->find($data['evenement_id']);
        $utilisateur = $utilisateurRepository->find($data['utilisateur_id']);

        if (!$evenement || !$utilisateur) {
            return new JsonResponse(['message' => 'Événement ou utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Vérifie si l'utilisateur est de type "participant"
        if ($utilisateur->getRole() !== 'participant') {
            return new JsonResponse(['message' => "Seuls les participants peuvent s'inscrire à un événement"], JsonResponse::HTTP_FORBIDDEN);
        }

        // Vérifie si l'utilisateur est déjà inscrit à cet événement
        $existingRegistre = $entityManager->getRepository(Registre::class)->findOneBy([
            'evenement' => $evenement,
            'utilisateur' => $utilisateur,
        ]);

        if ($existingRegistre) {
            return new JsonResponse(['message' => 'Vous êtes déjà inscrit à cet événement'], JsonResponse::HTTP_CONFLICT);
        }

        // Enregistre l'inscription
        $registre = new Registre();
        $registre->setEvenement($evenement);
        $registre->setUtilisateur($utilisateur);
        $registre->setDateInscription(new \DateTime());

        $entityManager->persist($registre);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Inscription enregistrée avec succès']);
    }

    #[Route('/registre/liste', name: 'liste_registre', methods: ['GET'])]
    public function liste(RegistreRepository $registreRepository): JsonResponse
    {
        // Récupérer toutes les inscriptions dans le registre
        $inscriptions = $registreRepository->findAll();

        // Transformer les données en un tableau pour une réponse JSON
        $data = [];
        foreach ($inscriptions as $inscription) {
            $data[] = [
                'id' => $inscription->getId(),
                'utilisateur' => $inscription->getUtilisateur()->getNom(), // Supposant une relation avec l'entité Utilisateur
                'evenement' => $inscription->getEvenement()->getTitre(),    // Supposant une relation avec l'entité Evenement
                'dateInscription' => $inscription->getDateInscription()->format('Y-m-d H:i:s'), // Si tu as une date
            ];
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
}
