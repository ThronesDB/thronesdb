<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TypeRepository extends EntityRepository
{
    public function findAll()
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t')
            ->orderBy('t.name', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
