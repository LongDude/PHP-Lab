<?php

namespace src\Entities;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use src\Repository\OrderRepository;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order 
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(name: 'from_loc', type: 'string', length: 255)]
    private string $from_loc;

    #[ORM\Column(name: 'dest_loc', type: 'string', length: 255)]
    private string $dest_loc;

    #[ORM\Column(name: 'distance', type: 'float')]
    private float $distance;

    #[ORM\Column(name: 'price', type: 'float')]
    private float $price;

    #[ORM\Column(name: 'orderedAt', type: 'datetime_immutable')]
    private ?DateTimeImmutable $orderedAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?User $user = null;
   
    #[ORM\ManyToOne(targetEntity: Driver::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id')]
    private ?Driver $driver = null;
   
    #[ORM\ManyToOne(targetEntity: Tariff::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'tariff_id', referencedColumnName: 'id')]
    private ?Tariff $tariff = null;

    public const FIELDS = [
        'from_loc',
        'dest_loc',
        'distance',
        'orderedAt',
        'driver_id',
        'user_id',
    ];

    public function __construct()
    {
        $this->orderedAt = new DateTimeImmutable();
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getFromLoc(): string { return $this->from_loc; }
    public function getDestLoc(): string { return $this->dest_loc; }
    public function getDistance(): float { return $this->distance; }
    public function getPrice(): float { return $this->price; }
    public function getOrderedAt(): DateTimeImmutable { return $this->orderedAt; }
    public function getUser(): ?User { return $this->user; }
    public function getDriver(): ?Driver { return $this->driver; }
    public function getTariff(): ?Tariff { return $this->tariff; }

    // Setters
    public function setFromLoc(string $from_loc): self { $this->from_loc = $from_loc; return $this; }
    public function setDestLoc(string $dest_loc): self { $this->dest_loc = $dest_loc; return $this; }
    public function setDistance(float $distance): self { $this->distance = $distance; return $this; }
    public function setPrice(float $price): self { $this->price = $price; return $this; }
    public function setOrderedAt(DateTimeImmutable $orderedAt): self { $this->orderedAt = $orderedAt; return $this; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function setDriver(?Driver $driver): self { $this->driver = $driver; return $this; }
    public function setTariff(?Tariff $tariff): self { $this->tariff = $tariff; return $this; }
}