<?php 

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DeckRepository extends EntityRepository
{
    public function find($id)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('d, f, ds, c')
            ->join('d.faction', 'f')
            ->leftJoin('d.slots', 'ds')
            ->leftJoin('ds.card', 'c')
            ->andWhere('d.id = ?1');

        $qb->setParameter(1, $id);
        return $qb->getQuery()->getOneOrNullResult();
    }
}
