<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Enum\RoleUtilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

#[Route('/api')]
class AuthentificationController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @OA\Post(
     *     path="/api/inscription",
     *     summary="Créer un nouvel utilisateur",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="MotDePasse123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Utilisateur créé"),
     *     @OA\Response(response=400, description="Champs obligatoires manquants")
     * )
     */
    #[Route('/inscription', name:'api_inscription', methods:['POST'])]
    public function inscription(Request $request, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json(['error'=>'Email et mot de passe obligatoires'], 400);
        }

        $user = new Utilisateur();
        $user->setEmail($data['email']);
        $user->setMotDePasse($hasher->hashPassword($user, $data['password']));
        $user->addRole(RoleUtilisateur::USER);

        $this->em->persist($user);
        $this->em->flush();

        return $this->json(['message'=>'Utilisateur créé']);
    }

    /**
     * @OA\Post(
     *     path="/api/connexion",
     *     summary="Connexion utilisateur",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="MotDePasse123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Connexion réussie"),
     *     @OA\Response(response=401, description="Mot de passe incorrect"),
     *     @OA\Response(response=404, description="Utilisateur non trouvé")
     * )
     */
    #[Route('/connexion', name:'api_connexion', methods:['POST'])]
    public function connexion(Request $request, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json(['error'=>'Email et mot de passe obligatoires'], 400);
        }

        $user = $this->em->getRepository(Utilisateur::class)->findOneBy(['email'=>$data['email']]);
        if (!$user) {
            return $this->json(['error'=>'Utilisateur non trouvé'], 404);
        }

        if (!$hasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error'=>'Mot de passe incorrect'], 401);
        }

        return $this->json([
            'message'=>'Connexion réussie',
            'user'=>[
                'id'=>$user->getId(),
                'email'=>$user->getEmail(),
                'roles'=>$user->getRoles()
            ]
        ]);
    }
}
