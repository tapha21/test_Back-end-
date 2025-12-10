<?php
namespace App\Service;

use App\Entity\Utilisateur;
use App\Enum\RoleUtilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UtilisateurRepository $repo,
        private UserPasswordHasherInterface $hasher
    ){}

    public function creerUtilisateur(string $email, string $motDePasse, array $roles=[]): Utilisateur
    {
        $user = new Utilisateur();
        $user->setEmail($email)
             ->setMotDePasse($this->hasher->hashPassword($user, $motDePasse))
             ->setRoles($roles ?: [RoleUtilisateur::USER->value]);

        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function getUtilisateurByEmail(string $email): ?Utilisateur
    {
        return $this->repo->findByEmail($email);
    }

    public function getTousUtilisateurs(): array
    {
        return $this->repo->findAll();
    }
}
