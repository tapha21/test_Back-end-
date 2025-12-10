<?php
namespace App\Controller;

use App\Entity\Tache;
use App\Entity\Utilisateur;
use App\Enum\StatutTache;
use Doctrine\ORM\EntityManagerInterface;
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
            "id"=>$t->getId(),
            "titre"=>$t->getTitre(),
            "description"=>$t->getDescription(),
            "statut"=>$t->getStatut(),
            "utilisateur"=>$t->getUtilisateur()->getEmail()
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
    #[Route('/api/taches/ajouter', name: 'tache_ajouter', methods: ['POST'])]
    public function ajouterTache(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $titre = $data['titre'] ?? null;
        $description = $data['description'] ?? null;
        $statut = $data['statut'] ?? StatutTache::A_FAIRE->name;
        $emailUser = $data['emailUser'] ?? null;

        if (!$titre || !$description || !$emailUser) {
            return $this->json(['error' => 'Champs obligatoires manquants'], 400);
        }

        $utilisateur = $this->em->getRepository(Utilisateur::class)->findOneBy(['email' => $emailUser]);
        if (!$utilisateur) {
            return $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $tache = new Tache();
        $tache->setTitre($titre)
              ->setDescription($description)
              ->setStatut(StatutTache::from($statut))
              ->setUtilisateur($utilisateur);

        $this->em->persist($tache);
        $this->em->flush();

        return $this->json(['success' => 'Tâche créée', 'id' => $tache->getId()]);
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
            "id"=>$tache->getId(),
            "titre"=>$tache->getTitre(),
            "description"=>$tache->getDescription(),
            "statut"=>$tache->getStatut(),
            "utilisateur"=>$tache->getUtilisateur()->getEmail()
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
}
