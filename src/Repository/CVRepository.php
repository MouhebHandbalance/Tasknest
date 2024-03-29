<?php

namespace App\Repository;

use App\Entity\CV;
use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CV>
 *
 * @method CV|null find($id, $lockMode = null, $lockVersion = null)
 * @method CV|null findOneBy(array $criteria, array $orderBy = null)
 * @method CV[]    findAll()
 * @method CV[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CVRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CV::class);
    }
    // Custom method to fetch CV and its associated skills by CV ID
    public function findCVWithSkillsByCVId(int $cvId): ?array
    {
        $qb = $this->createQueryBuilder('cv')
            ->leftJoin('cv.skills', 'skill')
            ->addSelect('skill')
            ->andWhere('cv.id = :cvId')
            ->setParameter('cvId', $cvId);

        $cvWithSkills = $qb->getQuery()->getOneOrNullResult();

        return $cvWithSkills ? ['cv' => $cvWithSkills, 'skills' => $cvWithSkills->getSkills()->toArray()] : null;
    }
    public function findSkillNamesByIds(array $selectedSkillIds): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('s.name')
           ->from(Skill::class, 's')
           ->where($qb->expr()->in('s.id', $selectedSkillIds));
    
        $query = $qb->getQuery();
        
        return array_map('current', $query->getResult()); // Extract skill names from the result
    }
    public function findCvsBySkillNames(array $selectedSkills): array
{
    $qb = $this->createQueryBuilder('cv');
    $qb->innerJoin('cv.skills', 's')
       ->andWhere($qb->expr()->in('s.name', ':selectedSkills'))
       ->setParameter('selectedSkills', $selectedSkills);
    
    return $qb->getQuery()->getResult();
}

    
    
    
    
//    /**
//     * @return CV[] Returns an array of CV objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CV
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
