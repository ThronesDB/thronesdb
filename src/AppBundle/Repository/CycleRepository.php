<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CycleRepository extends EntityRepository
{
    public function findAll()
    {
        $qb = $this->createQueryBuilder('y')
            ->select('y, p')
            ->leftJoin('y.packs', 'p')
            ->orderBy('y.position', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
