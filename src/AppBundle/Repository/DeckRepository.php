<?php

namespace AppBundle\Repository;

class DeckRepository extends TranslatableRepository
{
	function __construct($entityManager)
	{
		parent::__construct($entityManager, $entityManager->getClassMetadata('AppBundle\Entity\Deck'));
	}

	/**
	 * @inheritdoc
	 */
	public function find($id, $lockMode = null, $lockVersion = null)
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
