<?php
namespace App\Controller;
use App\Entity\Tache;
use App\Entity\Utilisateur;
use App\Enum\StatutTache;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

#[Route('/api/taches')]
class TacheController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @OA\Get(
     *     path="/api/taches",
     *     summary="Lister toutes les tâches",
     *     @OA\Response(response=200, description="Liste des tâches")
     * )
     */
    #[Route('', name: 'taches_liste', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $taches = $this->em->getRepository(Tache::class)->findAll();
            $data = array_map(fn(Tache $t) => [
            "id" => $t->getId(),
            "titre" => $t->getTitre(),
            "description" => $t->getDescription(),
            "statut" => $t->getStatut(),
            "utilisateur" => [
                "email" => $t->getUtilisateur()->getEmail(),
                "nom" => $t->getUtilisateur()->getNom(),
                "prenom" => $t->getUtilisateur()->getPrenom()
            ]
        ], $taches);

        return $this->json($data);
    }

 /**
 * @OA\Post(
 *     path="/api/taches/ajouter",
 *     summary="Ajouter une nouvelle tâche",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="titre", type="string", example="Nouvelle tâche"),
 *             @OA\Property(property="description", type="string", example="Description de la tâche"),
 *             @OA\Property(property="statut", type="string", example="A_FAIRE"),
 *             @OA\Property(property="emailUser", type="string", example="user@example.com")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Tâche créée"),
 *     @OA\Response(response=400, description="Champs obligatoires manquants"),
 *     @OA\Response(response=404, description="Utilisateur introuvable")
 * )
 */
#[Route('/ajouter', methods: ['POST'])]
public function ajouter(Request $request, EntityManagerInterface $em, LoggerInterface $logger): JsonResponse
{
    try {
        // --- Récupération des données JSON ---
        $data = json_decode($request->getContent(), true) ?: [];

        $titre = $data['titre'] ?? null;
        $description = $data['description'] ?? '';
        $statut = $data['statut'] ?? 'A_FAIRE';
        $emailUser = $data['emailUser'] ?? null;
        $fichiersBase64 = $data['fichiers'] ?? []; // tableau de strings base64

        $logger->info('Données reçues pour ajouter une tâche', [
            'titre' => $titre,
            'description' => $description,
            'statut' => $statut,
            'emailUser' => $emailUser,
            'nbFichiers' => count($fichiersBase64)
        ]);

        if (!$titre) {
            return $this->json(['success' => false, 'error' => 'Le titre est requis'], 400);
        }

        if (!$emailUser) {
            return $this->json(['success' => false, 'error' => 'Email de l’utilisateur requis'], 400);
        }

        // --- Récupération de l'utilisateur ---
        $utilisateur = $em->getRepository(Utilisateur::class)->findOneBy(['email' => $emailUser]);
        if (!$utilisateur) {
            return $this->json(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
        }

        // --- Création de la tâche ---
        $tache = new Tache();
        $tache->setTitre($titre)
              ->setDescription($description)
              ->setStatut(\App\Enum\StatutTache::from($statut))
              ->setUtilisateur($utilisateur);

        // --- Stockage des fichiers base64 (facultatif) ---
        // Tu peux directement stocker les chaînes base64 dans la colonne JSON
        $tache->setFichiers($fichiersBase64);

        $em->persist($tache);
        $em->flush();

        return $this->json([
            'success' => true,
            'id' => $tache->getId(),
            'fichiers' => $tache->getFichiers()
        ], 201);

    } catch (\Exception $e) {
        $logger->error('Erreur lors de l\'ajout de la tâche', ['exception' => $e]);
        return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
    }
}











    /**
     * @OA\Get(
     *     path="/api/taches/{id}",
     *     summary="Voir une tâche par ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Détails de la tâche"),
     *     @OA\Response(response=404, description="Tâche non trouvée")
     * )
     */
    #[Route('/{id}', name: 'tache_detail', methods: ['GET'], requirements:['id'=>'\d+'])]
    public function detail(int $id): JsonResponse
    {
        $tache = $this->em->getRepository(Tache::class)->find($id);
        if(!$tache){
            return $this->json(['error'=>'Tâche non trouvée'],404);
        }
       return $this->json([
            "id" => $tache->getId(),
            "titre" => $tache->getTitre(),
            "description" => $tache->getDescription(),
            "statut" => $tache->getStatut(),
            "utilisateur" => [
                "email" => $tache->getUtilisateur()->getEmail(),
                "nom" => $tache->getUtilisateur()->getNom(),
                "prenom" => $tache->getUtilisateur()->getPrenom()
            ]
        ]);

    }

    /**
     * @OA\Delete(
     *     path="/api/taches/supprimer/{id}",
     *     summary="Supprimer une tâche",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Tâche supprimée"),
     *     @OA\Response(response=404, description="Tâche non trouvée")
     * )
     */
    #[Route('/supprimer/{id}', name: 'tache_supprimer', methods: ['DELETE'], requirements:['id'=>'\d+'])]
    public function supprimer(int $id): JsonResponse
    {
        $tache = $this->em->getRepository(Tache::class)->find($id);
        if(!$tache){
            return $this->json(['error'=>'Tâche non trouvée'],404);
        }
        $this->em->remove($tache);
        $this->em->flush();
        return $this->json(['success'=>'Tâche supprimée']);
    }
    #[Route('/update/{id}', name: 'tache_update', methods: ['PUT'])]
public function update(Request $request, int $id, EntityManagerInterface $em): JsonResponse
{
    try {
        $tache = $em->getRepository(Tache::class)->find($id);
        if (!$tache) {
            return $this->json(['success' => false, 'error' => 'Tâche non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true) ?: [];
        $statut = $data['statut'] ?? null;
        
        if (!$statut || !in_array($statut, ['A_FAIRE', 'EN_COURS', 'FAIT'])) {
            return $this->json(['success' => false, 'error' => 'Statut invalide'], 400);
        }

        $tache->setStatut(\App\Enum\StatutTache::from($statut));
        
        $em->flush();

        return $this->json([
            'success' => true,
            'id' => $tache->getId(),
            'statut' => $tache->getStatut()->value
        ]);

    } catch (\Exception $e) {
        return $this->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 400);
    }
}

}
