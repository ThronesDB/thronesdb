<?php

namespace App\DataFixtures;

use App\Entity\Rarity;
use App\Entity\RarityInterface;
use App\Services\DataimportFileLocator;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

/**
 * Class LoadRarityData
 * @package App\DataFixtures\ORM
 */
class LoadRarityData extends AbstractFixture implements FixtureGroupInterface
{
    public function __construct(DataimportFileLocator $dataimportFileLocator)
    {
        parent::__construct($dataimportFileLocator, 'rarity');
    }

    /**
     * @return RarityInterface
     */
    protected function createEntity()
    {
        return new Rarity();
    }

    /**
     * @param RarityInterface $entity
     * @param array $data
     * @return RarityInterface
     *
     */
    protected function populateEntity($entity, array $data)
    {
        // `id`, `code`, `name`
        $entity->setId($data[0]);
        $entity->setCode($data[1]);
        $entity->setName($data[2]);
        return $entity;
    }

    /**
     * @inheritdoc
     */
    public static function getGroups(): array
    {
        return ['rarity'];
    }
}
