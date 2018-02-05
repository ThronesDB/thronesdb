<?php 

namespace AppBundle\Repository;

class DeckRepository extends TranslatableRepository
{
    public function __construct($entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Deck'));
    }

    public function find($id)
    {
        $qb = $this->createQueryBuilder('d')
            ->select('d, f, ds, c')
            ->join('d.faction', 'f')
            ->leftJoin('d.slots', 'ds')
            ->leftJoin('ds.card', 'c')
            ->andWhere('d.id = ?1');

        $qb->setParameter(1, $id);
        return $this->getOneOrNullResult($qb);
    }
}
