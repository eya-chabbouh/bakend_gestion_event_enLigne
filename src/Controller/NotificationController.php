<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Notification;
use App\Entity\Registre;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
class NotificationController extends AbstractController
{
    #[Route('/organisateur/envoyer-notification/{evenementId}', name: 'envoyer_notification', methods: ['POST'])]
    public function envoyerNotification(int $evenementId, Request $request, EntityManagerInterface $entityManager, UserInterface $user)
    {
        // Vérifier que l'utilisateur est un organisateur
        if ($user->getRole() !== 'organisateur') {
            return $this->json(['message' => 'Vous devez être un organisateur pour envoyer des notifications.'], 403);
        }

        // Récupérer l'événement
        $evenement = $entityManager->getRepository(Evenement::class)->find($evenementId);

        if (!$evenement) {
            return $this->json(['message' => 'Événement non trouvé.'], 404);
        }

        // Récupérer tous les utilisateurs ayant participé à l'événement
        $registreRepository = $entityManager->getRepository(Registre::class);
        $participants = $registreRepository->findBy(['evenement' => $evenement]);

        if (empty($participants)) {
            return $this->json(['message' => 'Aucun participant trouvé pour cet événement.'], 404);
        }

        // Récupérer le message envoyé par l'organisateur
        $message = $request->request->get('message');

        if (!$message) {
            return $this->json(['message' => 'Le message ne peut pas être vide.'], 400);
        }

        // Créer et envoyer la notification à chaque participant
        foreach ($participants as $registre) {
            $participant = $registre->getUtilisateur();
            if ($participant->getRole() === 'participant') {
                $notification = new Notification();
                $notification->setExpediteur($user) // L'organisateur qui envoie la notification
                             ->setDestinataire($participant) // Le participant qui reçoit la notification
                             ->setEvenement($evenement) // L'événement associé à la notification
                             ->setMessage($message) // Le message à envoyer
                             ->setDateEnvoi(new \DateTime()); // La date d'envoi de la notification

                // Enregistrer la notification
                $entityManager->persist($notification);
            }
        }

        // Sauvegarder les notifications
        $entityManager->flush();

        return $this->json(['message' => 'Les notifications ont été envoyées avec succès.']);
    }
}
