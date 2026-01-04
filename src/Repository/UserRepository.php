<?php

namespace App\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function getInactiveUsers(DateTime $createdOnOrBefore = null, $excludeDeactivated = true): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->distinct()
            ->leftJoin('u.decks', 'd')
            ->leftJoin('u.decklists', 'dl')
            ->leftJoin('u.comments', 'c')
            ->leftJoin('u.reviews', 'r')
            ->leftJoin('u.reviewcomments', 'rc')
            ->leftJoin('u.reviewvotes', 'rv')
            ->leftJoin('u.favorites', 'f')
            ->leftJoin('u.votes', 'v')
            ->where('d.id IS NULL')
            ->andWhere('dl.id IS NULL')
            ->andWhere('c.id IS NULL')
            ->andWhere('r.id IS NULL')
            ->andWhere('rc.id IS NULL')
            ->andWhere('rv.id IS NULL')
            ->andWhere('f.id IS NULL')
            ->andWhere('v.id IS NULL');

        if ($createdOnOrBefore) {
            $qb->andWhere('u.dateCreation <= :date_created');
            $qb->setParameter(':date_created', $createdOnOrBefore->format('Y-m-d H:i:s'));
        }
        if ($excludeDeactivated) {
            $qb->andWhere('u.enabled = TRUE');
        }

        return $qb->getQuery()->getResult();
    }
}
