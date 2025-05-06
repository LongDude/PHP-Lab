<?php

namespace src\Entities;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use src\Repository\DriverRepository;

#[ORM\Entity(repositoryClass: DriverRepository::class)]
#[ORM\Table(name: 'drivers')]
class Driver 
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(name: 'id', type: 'bigint')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'driver')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', unique: true)]
    private User $user;

    #[ORM\Column(name: 'intership', type: 'smallint', options: ['default' => 0, 'unsigned' => true])]
    private int $intership = 0;

    #[ORM\Column(name: 'car_license', type: 'string', length: 15)]
    private string $car_license;

    #[ORM\Column(name: 'car_brand', type: 'string', length: 50)]
    private string $car_brand;

    #[ORM\Column(name: 'rating', type: 'float', options: ['default' => 0])]
    private float $rating = 0.0;

    #[ORM\ManyToOne(targetEntity: Tariff::class, inversedBy: 'drivers')]
    #[ORM\JoinColumn(name: 'tariff_id', referencedColumnName: 'id')]
    private ?Tariff $tariff = null;

    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'driver')]
    private Collection $orders;

    public const FIELDS = [
        'name',
        'phone',
        'email',
        'intership',
        'car_license',
        'car_brand',
        'tariff_id',
    ];

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getIntership(): int { return $this->intership; }
    public function getCarLicense(): string { return $this->car_license; }
    public function getCarBrand(): string { return $this->car_brand; }
    public function getRating(): float { return $this->rating; }
    public function getTariff(): ?Tariff { return $this->tariff; }
    public function getOrders(): Collection { return $this->orders; }

    // Setters
    public function setUser(User $user): self { $this->user = $user; return $this; }
    public function setIntership(int $intership): self { $this->intership = $intership; return $this; }
    public function setCarLicense(string $car_license): self { $this->car_license = $car_license; return $this; }
    public function setCarBrand(string $car_brand): self { $this->car_brand = $car_brand; return $this; }
    public function setRating(float $rating): self { $this->rating = $rating; return $this; }
    public function setTariff(?Tariff $tariff): self { $this->tariff = $tariff; return $this; }
}