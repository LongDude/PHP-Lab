<?php

namespace src\Entities;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="tariffs")
 */
class Tariff{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=20, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(name="base_price", type="float", options={"default": 0})
     */
    private $base_price;
    
    /**
     * @ORM\Column(name="base_dist", type="float", options={"default": 0})
     */
    private $base_dist;
    
    /**
     * @ORM\Column(name="dist_cost", type="float", options={"default": 0})
     */
    private $dist_cost;

    /**
     * @ORM\OneToMany(targetEntity="Driver", mappedBy="tariff")
     */
    private Collection $drivers;

    /**
     * @ORM\OneToMany(targetEntity="Order", mappedBy="tariff")
     */
    private Collection $orders;

    const fields = array(
        'name',
        'base_price',
        'base_dist',
        'dist_cost',
    );

    public function __construct()
    {
        $this->drivers = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): int { return $this->id;}
    public function getName(): string { return $this->name;}
    public function getBasePrice(): float { return $this->base_price;}
    public function getBaseDist(): float { return $this->base_dist;}
    public function getDistCost(): float { return $this->dist_cost;}
    public function getDrivers(): Collection { return $this->drivers;}
    public function getOrders(): Collection { return $this->orders;}

    public function setName($name): self { $this->name = $name; return $this; }
    public function setBasePrice($base_price): self { $this->base_price = $base_price; return $this; }
    public function setBaseDist($base_dist): self { $this->base_dist = $base_dist; return $this; }
    public function setDistCost($dist_cost): self { $this->dist_cost = $dist_cost; return $this; }
}