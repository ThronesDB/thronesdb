<?php

namespace App\Repository;

use App\Entity\CycleInterface;
use Doctrine\ORM\EntityRepository;

class CycleRepository extends EntityRepository
{
    /**
     * @return CycleInterface[]
     */
    public function findAll()
    {
        $qb = $this->createQueryBuilder('y')
            ->select('y, p')
            ->leftJoin('y.packs', 'p')
            ->orderBy('y.position', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
