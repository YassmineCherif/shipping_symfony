<?php

namespace App\Repository;

use App\Entity\Livreur;
use App\Entity\MoyenDeTransport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MoyenDeTransport>
 *
 * @method MoyenDeTransport|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoyenDeTransport|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoyenDeTransport[]    findAll()
 * @method MoyenDeTransport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoyenDeTransportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoyenDeTransport::class);
    }

    public function save(MoyenDeTransport $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MoyenDeTransport $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }



    public function countAvailableMoyensDeTransport(): int
    {
        $qb = $this->createQueryBuilder('m');
        $qb->leftJoin('m.livreur', 'l')
            ->where($qb->expr()->isNull('l.id'));

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findByMarque(string $marque): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.marque LIKE :marque')
            ->setParameter('marque', '%'.$marque.'%')
            ->orderBy('m.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return MoyenDeTransport[] Returns an array of MoyenDeTransport objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MoyenDeTransport
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
