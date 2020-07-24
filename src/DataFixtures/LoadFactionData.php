<?php

namespace App\DataFixtures;

use App\Entity\Faction;
use App\Entity\FactionInterface;
use App\Services\DataimportFileLocator;

/**
 * Class LoadFactionData
 * @package App\DataFixtures\ORM
 */
class LoadFactionData extends AbstractFixture
{
    public function __construct(DataimportFileLocator $dataimportFileLocator)
    {
        parent::__construct($dataimportFileLocator, 'faction');
    }

    /**
     * @return FactionInterface
     */
    protected function createEntity()
    {
        return new Faction();
    }

    /**
     * @param FactionInterface $entity
     * @param array $data
     * @return FactionInterface
     */
    protected function populateEntity($entity, array $data)
    {
        // `id`,`code`,`name`,`is_primary`,`octgn_id`
        $entity->setId($data[0]);
        $entity->setCode($data[1]);
        $entity->setName($data[2]);
        $entity->setIsPrimary((bool) $data[3]);
        $entity->setOctgnId($data[4]);
        return $entity;
    }
}
