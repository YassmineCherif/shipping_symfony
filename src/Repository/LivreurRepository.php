<?php

namespace App\Repository;

use App\Entity\Livreur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livreur>
 *
 * @method Livreur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Livreur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Livreur[]    findAll()
 * @method Livreur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LivreurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livreur::class);
    }

    public function save(Livreur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Livreur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findLivreurWithPartenaire($id_partenaire)
    {
        $qb = $this->createQueryBuilder('l')
            ->innerJoin('l.partenaire', 'p')
            ->where('p.id = :id_partenaire')
            ->setParameter('id_partenaire', $id_partenaire);
    
        return $qb->getQuery()->getResult();
    }
    

    public function findByNom(string $prenom): array
    {
        // crée un objet QueryBuilder qui permet de construire une requête
        return $this->createQueryBuilder('m')
            ->andWhere('m.prenom LIKE :prenom')
            ->setParameter('prenom', '%'.$prenom.'%')
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByNoms(string $nom): array
    {
        // crée un objet QueryBuilder qui permet de construire une requête
        return $this->createQueryBuilder('m')
            ->andWhere('m.nom LIKE :nom')
            ->setParameter('nom', '%'.$nom.'%')
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Livreur[] Returns an array of Livreur objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Livreur
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
