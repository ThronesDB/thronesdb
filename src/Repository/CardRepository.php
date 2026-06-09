<?php

namespace App\Repository;

use App\Entity\CardInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Class CardRepository
 * @package App\Repository
 */
class CardRepository extends EntityRepository
{
    /**
     * @return CardInterface[]
     */
    public function findAll()
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, t, f, p, y')
            ->join('c.type', 't')
            ->join('c.faction', 'f')
            ->join('c.pack', 'p')
            ->join('p.cycle', 'y')
            ->orderBy('c.code', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $type
     * @return CardInterface[]
     */
    public function findByType($type)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, p')
            ->join('c.pack', 'p')
            ->join('c.type', 't')
            ->andWhere('t.code = ?1')
            ->orderBy('c.code', 'ASC');

        $qb->setParameter(1, $type);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $code
     * @return CardInterface|null
     * @throws NonUniqueResultException
     */
    public function findByCode($code)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->andWhere('c.code = ?1');

        $qb->setParameter(1, $code);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $codes
     * @return CardInterface[]
     */
    public function findAllByCodes($codes)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, t, f, p, y')
            ->join('c.type', 't')
            ->join('c.faction', 'f')
            ->join('c.pack', 'p')
            ->join('p.cycle', 'y')
            ->andWhere('c.code in (?1)')
            ->orderBy('c.code', 'ASC');

        $qb->setParameter(1, $codes);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param CardInterface $card
     * @param int $position
     * @return CardInterface|null
     * @throws NonUniqueResultException
     */
    public function findByRelativePosition(CardInterface $card, int $position)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->join('c.pack', 'p')
            ->andWhere('p.code = ?1')
            ->andWhere('c.position = ?2');

        $qb->setParameter(1, $card->getPack()->getCode());
        $qb->setParameter(2, $card->getPosition() + $position);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $card
     * @return CardInterface|null
     * @throws NonUniqueResultException
     */
    public function findPreviousCard($card)
    {
        return $this->findByRelativePosition($card, -1);
    }

    /**
     * @param $card
     * @return CardInterface|null
     * @throws NonUniqueResultException
     */
    public function findNextCard($card)
    {
        return $this->findByRelativePosition($card, 1);
    }

    /**
     * @return array
     */
    public function findTraits()
    {
        $qb = $this->createQueryBuilder('c')
            ->select('DISTINCT c.traits')
            ->andWhere("c.traits != ''");
        return $qb->getQuery()->getResult();
    }

    /**
     * Retrieves all agendas eligible for constructed deck building.
     * @param array $excludedAgendas a list of codes of agendas to exclude.
     * @return array
     */
    public function getAgendasForNewDeckWizard($excludedAgendas = array()): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, p')
            ->join('c.pack', 'p')
            ->join('c.type', 't')
            ->where('t.code = :type')
            ->orderBy('c.name', 'ASC');

        $qb->setParameter(':type', 'agenda');

        if (! empty($excludedAgendas)) {
            $qb->andWhere($qb->expr()->notIn('c.code', ':codes'));
            $qb->setParameter(':codes', $excludedAgendas, Connection::PARAM_STR_ARRAY);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Retrieves all agendas eligible for variant game modes deck building.
     * @param array $variantAgendas a list of codes of agendas to include.
     * @return array
     */
    public function getVariantAgendasForNewDeckWizard($variantAgendas = array()): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, p')
            ->join('c.pack', 'p')
            ->join('c.type', 't')
            ->where('t.code = :type')
            ->orderBy('p.code', 'ASC');

        $qb->setParameter(':type', 'agenda');

        if (! empty($variantAgendas)) {
            $qb->andWhere($qb->expr()->in('c.code', ':codes'));
            $qb->setParameter(':codes', $variantAgendas, Connection::PARAM_STR_ARRAY);
        }

        return $qb->getQuery()->getResult();
    }
}
