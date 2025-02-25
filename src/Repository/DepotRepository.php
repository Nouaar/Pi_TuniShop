<?php

namespace App\Repository;

use App\Entity\Depot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Depot>
 */
class DepotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Depot::class);
    }

    //    /**
    //     * @return Depot[] Returns an array of Depot objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Depot
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function fetchDepotLogs(): array
    {
        $conn = $this->getEntityManager()->getConnection();
    
        $sql = "
            SELECT 
                DATE(version.created_at) AS log_date,
                COUNT(CASE WHEN version.action = 'insert' THEN 1 ELSE NULL END) AS created_count,
                COUNT(CASE WHEN version.action = 'update' THEN 1 ELSE NULL END) AS updated_count,
                COUNT(CASE WHEN version.action = 'remove' THEN 1 ELSE NULL END) AS deleted_count
            FROM ext_log_entries version
            WHERE version.object_class = 'App\Entity\Depot'
            GROUP BY log_date
            ORDER BY log_date ASC
        ";
    
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
    
        return $resultSet->fetchAllAssociative();
    }
    

    
    
    
public function countDepotStatus(): array
{
    return $this->createQueryBuilder('d')
        ->select("d.status, COUNT(d.id) as count")
        ->where('d.deletedAt IS NULL') // Exclude soft-deleted depots
        ->groupBy('d.status')
        ->getQuery()
        ->getResult();
}

}
