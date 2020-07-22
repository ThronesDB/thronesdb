<?php

namespace App\DataFixtures;

use App\Entity\Type;
use App\Entity\TypeInterface;
use App\Services\DataimportFileLocator;

/**
 * Class LoadTypeData
 * @package App\DataFixtures\ORM
 */
class LoadTypeData extends AbstractFixture
{
    public function __construct(DataimportFileLocator $dataimportFileLocator)
    {
        parent::__construct($dataimportFileLocator, 'type');
    }

    /**
     * @return TypeInterface
     */
    protected function createEntity()
    {
        return new Type();
    }

    /**
     * @param TypeInterface $entity
     * @param array $data
     * @return TypeInterface
     */
    protected function populateEntity($entity, array $data)
    {
        //`id`,`code`,`name`
        $entity->setId($data[0]);
        $entity->setCode($data[1]);
        $entity->setName($data[2]);
        return $entity;
    }
}
