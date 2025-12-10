<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Tache;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use OpenApi\Annotations as OA;

#[Route('/api/admin')]
class AdminController extends AbstractController
{
    /**
     * @OA\Get(
     *     path="/api/admin/utilisateurs",
     *     summary="Lister tous les utilisateurs",
     *     security={{"bearer":{}}},
     *     @OA\Response(response=200, description="Liste des utilisateurs"),
     *     @OA\Response(response=403, description="Accès refusé")
     * )
     */
    #[Route('/utilisateurs', name: 'admin_liste_utilisateurs', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function listeUtilisateurs(EntityManagerInterface $em): JsonResponse
    {
        $users = $em->getRepository(Utilisateur::class)->findAll();
        return $this->json(['status'=>'success','data'=>$users]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/taches",
     *     summary="Lister toutes les tâches",
     *     security={{"bearer":{}}},
     *     @OA\Response(response=200, description="Liste des tâches"),
     *     @OA\Response(response=403, description="Accès refusé")
     * )
     */
    #[Route('/taches', name: 'admin_liste_taches', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function listeToutesTaches(EntityManagerInterface $em): JsonResponse
    {
        $taches = $em->getRepository(Tache::class)->findAll();
        return $this->json(['status'=>'success','data'=>$taches]);
    }
}
