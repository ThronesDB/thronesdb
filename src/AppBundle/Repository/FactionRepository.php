<?php 

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class FactionRepository extends EntityRepository
{
    public function findAllAndOrderByName()
    {
        $qb = $this->createQueryBuilder('f')->orderBy('f.name', 'ASC');
        return $qb->getQuery()->getResult();
    }


    public function findPrimaries()
    {
        $qb = $this->createQueryBuilder('f')->andWhere('f.isPrimary = 1');
        return $qb->getQuery()->getResult();
    }

    public function findByCode($code)
    {
        $qb = $this->createQueryBuilder('f')->andWhere('f.code = ?1');
        $qb->setParameter(1, $code);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
