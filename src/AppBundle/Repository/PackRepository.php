<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PackRepository extends EntityRepository
{
    public function findAll()
    {
        $qb = $this->createQueryBuilder('p')
                ->select('p, y')
                ->join('p.cycle', 'y')
                ->orderBy('p.dateRelease', 'ASC')
                ->addOrderBy('p.position', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findByCode($code)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p')
            ->andWhere('p.code = ?1');

        $qb->setParameter(1, $code);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
