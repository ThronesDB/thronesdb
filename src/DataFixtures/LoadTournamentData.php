<?php

namespace App\DataFixtures;

use App\Entity\Tournament;
use App\Entity\TournamentInterface;
use App\Services\DataimportFileLocator;

/**
 * Class LoadTournamentData
 * @package App\DataFixtures\ORM
 */
class LoadTournamentData extends AbstractFixture
{
    public function __construct(DataimportFileLocator $dataimportFileLocator)
    {
        parent::__construct($dataimportFileLocator, 'tournament');
    }

    /**
     * @return TournamentInterface
     */
    protected function createEntity()
    {
        return new Tournament();
    }

    /**
     * @param TournamentInterface $entity
     * @param array $data
     * @return TournamentInterface
     *
     */
    protected function populateEntity($entity, array $data)
    {
        // `id`, `description`, `active`
        $entity->setId($data[0]);
        $entity->setDescription($data[1]);
        $entity->setActive((bool) $data[2]);
        return $entity;
    }
}
