<?php
namespace App\DataFixtures;

use App\Entity\Utilisateur;
use App\Entity\Tache;
use App\Enum\StatutTache;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

public function load(ObjectManager $manager): void
    {
        $statuts = array_column(StatutTache::cases(), 'value');

        $admin = new Utilisateur();
        $admin->setEmail('admin@tapha.sn')
              ->setMotDePasse($this->hasher->hashPassword($admin, '123'))
              ->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $user = new Utilisateur();
        $user->setEmail('user1@tapha.sn')
             ->setMotDePasse($this->hasher->hashPassword($user, '123'))
             ->setRoles(['ROLE_USER']);
        $manager->persist($user);

        for ($i = 1; $i <= 6; $i++) {
        $tache = new Tache();
        $tache->setUtilisateur($user)
            ->setTitre("Tâche $i")
            ->setDescription("Description de la tâche $i")
            ->setStatut(\App\Enum\StatutTache::from($statuts[($i-1) % count($statuts)]))
            ->setFichiers([]);
        $manager->persist($tache);
    }

        $manager->flush();
    }
}
