<?php

namespace src\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use src\Repository\TariffRepository;

#[ORM\Entity(repositoryClass: TariffRepository::class)]
#[ORM\Table(name: 'tariffs')]
class Tariff
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 20, unique: true)]
    private string $name;

    #[ORM\Column(name: 'base_price', type: 'float', options: ['default' => 0])]
    private float $base_price = 0.0;
    
    #[ORM\Column(name: 'base_dist', type: 'float', options: ['default' => 0])]
    private float $base_dist = 0.0;
    
    #[ORM\Column(name: 'dist_cost', type: 'float', options: ['default' => 0])]
    private float $dist_cost = 0.0;

    #[ORM\OneToMany(targetEntity: Driver::class, mappedBy: 'tariff')]
    private Collection $drivers;

    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'tariff')]
    private Collection $orders;

    public const FIELDS = [
        'name',
        'base_price',
        'base_dist',
        'dist_cost',
    ];

    public function __construct()
    {
        $this->drivers = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getBasePrice(): float { return $this->base_price; }
    public function getBaseDist(): float { return $this->base_dist; }
    public function getDistCost(): float { return $this->dist_cost; }
    public function getDrivers(): Collection { return $this->drivers; }
    public function getOrders(): Collection { return $this->orders; }

    // Setters
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setBasePrice(float $base_price): self { $this->base_price = $base_price; return $this; }
    public function setBaseDist(float $base_dist): self { $this->base_dist = $base_dist; return $this; }
    public function setDistCost(float $dist_cost): self { $this->dist_cost = $dist_cost; return $this; }
}