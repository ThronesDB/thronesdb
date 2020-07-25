<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Serializable;

/**
  * @package App\Entity
 */
interface CycleInterface extends Serializable
{
    /**
     * @param $id
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();

    /**
     * @param string $code
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param int $position
     */
    public function setPosition($position);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param int $size
     */
    public function setSize($size);

    /**
     * @return int
     */
    public function getSize();

    /**
     * @param DateTime $dateCreation
     */
    public function setDateCreation($dateCreation);

    /**
     * @return DateTime
     */
    public function getDateCreation();

    /**
     * @param DateTime $dateUpdate
     */
    public function setDateUpdate($dateUpdate);

    /**
     * @return DateTime
     */
    public function getDateUpdate();

    /**
     * @param PackInterface $pack
     */
    public function addPack(PackInterface $pack);

    /**
     * @param PackInterface $pack
     */
    public function removePack(PackInterface $pack);

    /**
     * @return Collection
     */
    public function getPacks();
}
