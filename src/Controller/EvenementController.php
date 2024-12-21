<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EvenementRepository;
use App\Entity\Evenement;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
class EvenementController extends AbstractController
{
    #[Route('/events', name: 'liste_evenements', methods: ['GET'])]
    public function list(EvenementRepository $evenementRepository): Response
    {
        // Récupération de tous les événements
        $evenements = $evenementRepository->findAll();

        // Conversion des événements en tableau pour la réponse JSON
        $data = [];
        foreach ($evenements as $evenement) {
            $data[] = [
                'id' => $evenement->getId(),
                'titre' => $evenement->getTitre(),
                'description' => $evenement->getDescription(),
                'date' => $evenement->getDate()->format('Y-m-d'),
                'heure' => $evenement->getHeure()->format('H:i'),
                'lieu' => $evenement->getLieu(),
                'categorie' => $evenement->getCategorie(),
                'type' => $evenement->getType(),
                'capacity' => $evenement->getCapacity(),
                'image' => $evenement->getImage(),
                'archive' => $evenement->getArchive(),
            ];
        }

        // Retourner les données en JSON
        return new JsonResponse($data);
    }
    #[Route('/evenement/add', name: 'add_evenement', methods: ['POST'])]
    public function add(Request $request, PersistenceManagerRegistry $doctrine): Response
    {
        // Récupération des données de la requête
        $data = json_decode($request->getContent(), true);
        $entityManager = $doctrine->getManager();
        if (!$data) {
            return new JsonResponse(['message' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        // Validation des données (ajouter plus de validations selon vos besoins)
        $requiredFields = ['titre', 'description', 'date', 'heure', 'lieu', 'categorie', 'type', 'capacity','archive'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse(['message' => "Le champ '$field' est manquant."], Response::HTTP_BAD_REQUEST);
            }
        }

        
            // Création de l'entité
            $evenement = new Evenement();
            $evenement->setTitre($data['titre']);
            $evenement->setDescription($data['description']);
            $evenement->setDate(new \DateTime($data['date']));
            $evenement->setHeure(new \DateTime($data['heure']));
            $evenement->setLieu($data['lieu']);
            $evenement->setCategorie($data['categorie']);
            $evenement->setType($data['type']);
            $evenement->setCapacity((int) $data['capacity']);
            $evenement->setImage($data['image'] ?? null); // Facultatif
            $evenement->setArchive($data['archive']);

            // Persistance dans la base de données
            $entityManager->persist($evenement);
            $entityManager->flush();
            return $this->json( $evenement);
            
    }
    #[Route('/evenements/edit/{id}', name: 'modifier_evenement', methods: ['PUT'])]
    public function update(Request $request, EvenementRepository $evenementRepository, int $id,PersistenceManagerRegistry $doctrine): JsonResponse
    {
        
    
        // Décoder le JSON de la requête
        $data = json_decode($request->getContent(), true);
        $entityManager=$doctrine->getManager();
        $evenement = $entityManager->getRepository(Evenement::class)->find($id);
        if (!$evenement) {
            return new JsonResponse(['message' => 'Événement non trouvé'], Response::HTTP_NOT_FOUND);
        }
        if (!$evenement) {
            return $this->json('No evenement found for id' . $id, 404);

        }
    
        // Mettre à jour les propriétés de l'événement
        if (isset($data['titre'])) {
            $evenement->setTitre($data['titre']);
        }
        if (isset($data['description'])) {
            $evenement->setDescription($data['description']);
        }
        if (isset($data['date'])) {
            $evenement->setDate(new \DateTime($data['date']));
        }
        if (isset($data['heure'])) {
            $evenement->setHeure(new \DateTime($data['heure']));
        }
        if (isset($data['lieu'])) {
            $evenement->setLieu($data['lieu']);
        }
        if (isset($data['categorie'])) {
            $evenement->setCategorie($data['categorie']);
        }
        if (isset($data['type'])) {
            $evenement->setType($data['type']);
        }
        if (isset($data['capacity'])) {
            $evenement->setCapacity((int) $data['capacity']);
        }
        if (isset($data['image'])) {
            $evenement->setImage($data['image']);
        }
        if (isset($data['archive'])) {
            $evenement->setArchive($data['archive']);
        }
        $entityManager->flush();
        
    
        return new JsonResponse(['message' => 'Evenement modifier avec succes'], Response::HTTP_OK);
    }
    #[Route('/evenements/archive/{id}', name: 'archive_evenement', methods: ['POST'])]
    public function archive(int $id,PersistenceManagerRegistry $doctrine): JsonResponse
    {
        $entityManager=$doctrine->getManager();
        // Récupérer l'événement via l'EntityManager
        $evenement = $entityManager->getRepository(Evenement::class)->find($id);

        if (!$evenement) {
            return new JsonResponse(['message' => 'Événement non trouvé'], 404);
        }

        // Modifier directement la propriété "archived" (ou équivalent dans votre entité)
        $evenement->archived = true; // Si `archived` est une propriété publique
        // ou
        $reflectionClass = new \ReflectionClass($evenement);
        $property = $reflectionClass->getProperty('archive');
        $property->setAccessible(true);
        $property->setValue($evenement, true);

        // Persister les modifications
        $entityManager->persist($evenement);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Evénement archiver avec succes']);
    }

    
        #[Route('/evenements/supprimer/{id}', name: 'supprimer_evenement', methods: ['DELETE'])]
        public function supprimer(int $id, PersistenceManagerRegistry $doctrine): JsonResponse
        {
            $entityManager = $doctrine->getManager();
            // Rechercher l'événement par son ID
            $evenement = $entityManager->getRepository(Evenement::class)->find($id);
    
            if (!$evenement) {
                return new JsonResponse(['message' => 'Événement non trouvé'], 404);
            }
    
            // Supprimer l'événement
            $entityManager->remove($evenement);
            $entityManager->flush();
    
            return new JsonResponse(['message' => 'Événement supprimé avec succès']);
        }

        #[Route('/evenements/filtrer', name: 'filtrer_evenements', methods: ['GET'])]
        public function filtrer(Request $request, PersistenceManagerRegistry $doctrine): JsonResponse
        {
            $entityManager = $doctrine->getManager();
            $criteria = [];
            
            // Récupération des paramètres de filtre
            $categorie = $request->query->get('categorie');
            $lieu = $request->query->get('lieu');
            $date = $request->query->get('date'); // Format attendu : YYYY-MM-DD
            
            // Ajouter des critères de filtre si présents
            if ($categorie) {
                $criteria['categorie'] = $categorie;
            }
    
            if ($lieu) {
                $criteria['lieu'] = $lieu;
            }
    
            if ($date) {
                $criteria['date'] = new \DateTime($date);
            }
    
            // Requête avec les critères
            $evenements = $entityManager->getRepository(Evenement::class)->findBy($criteria);
    
            // Conversion des résultats en JSON
            $data = [];
            foreach ($evenements as $evenement) {
                $data[] = [
                    'id' => $evenement->getId(),
                    'titre' => $evenement->getTitre(),
                    'description' => $evenement->getDescription(),
                    'date' => $evenement->getDate()->format('Y-m-d'),
                    'lieu' => $evenement->getLieu(),
                    'categorie' => $evenement->getCategorie(),
                ];
            }
    
            return new JsonResponse($data);
        }
}
