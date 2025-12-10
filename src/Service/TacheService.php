<?php
namespace App\Service;

use App\Entity\Tache;
use App\Entity\Utilisateur;
use App\Enum\StatutTache;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;

class TacheService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TacheRepository $repo
    ){}

    public function creerTache(Utilisateur $user, string $titre, string $desc, array $fichiers=[]): Tache
    {
        $tache = new Tache();
        $tache->setUtilisateur($user)
              ->setTitre($titre)
              ->setDescription($desc)
              ->setStatut(StatutTache::A_FAIRE)
              ->setFichiers($fichiers);

        $this->em->persist($tache);
        $this->em->flush();
        return $tache;
    }

    public function modifierTache(Tache $tache, ?string $titre=null, ?string $desc=null, ?StatutTache $statut=null, array $fichiers=[]): Tache
    {
        if($titre) $tache->setTitre($titre);
        if($desc) $tache->setDescription($desc);
        if($statut) $tache->setStatut($statut);
        if($fichiers) $tache->setFichiers(array_merge($tache->getFichiers(), $fichiers));

        $this->em->flush();
        return $tache;
    }

    public function supprimerTache(Tache $tache): void
    {
        $this->em->remove($tache);
        $this->em->flush();
    }

    public function getTachesParUtilisateur(Utilisateur $user): array
    {
        return $this->repo->findByUtilisateur($user->getId());
    }
}
