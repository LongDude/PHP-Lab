<?php

namespace src\Entities;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="orders")
 */
class Order {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="bigint")
     */
    private $id;

    private $from_loc;

    private $dest_loc;

    private $distance;

    private $price;

    private $orderedAt;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="orders")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private ?User $user = null;
   
    /**
     * @ORM\OneToOne(targetEntity="Driver", inversedBy="orders")
     * @ORM\JoinColumn(name="driver_id", referencedColumnName="id")
     */
    private ?Driver $driver = null;
   
    /**
     * @ORM\OneToOne(targetEntity="Tariff", inversedBy="orders")
     * @ORM\JoinColumn(name="tariff_id", referencedColumnName="id")
     */
    private ?Tariff $tariff = null;

    const fields = array(
        'from_loc',
        'dest_loc',
        'distance',
        'orderedAt',
        'driver_id',
        'user_id',
    );

    public function getId(): int { return $this->id;}
    public function getFromLoc(): string { return $this->from_loc;}
    public function getDestLoc(): string { return $this->dest_loc;}
    public function getDistance(): float { return $this->distance;}
    public function getPrice(): float { return $this->price;}
    public function getOrderedAt(): string { return $this->orderedAt;}
    public function getUser(): ?User { return $this->user;}
    public function getDriver(): ?Driver { return $this->driver;}
    public function getTariff(): ?Tariff { return $this->tariff;}

    public function setFromLoc($from_loc): self { $this->from_loc = $from_loc; return $this; }
    public function setDestLoc($dest_loc): self { $this->dest_loc = $dest_loc; return $this; }
    public function setDistance($distance): self { $this->distance = $distance; return $this; }
    public function setPrice($price): self { $this->price = $price; return $this; }
    public function setOrderedAt($orderedAt): self { $this->orderedAt = $orderedAt; return $this; }
    public function setUser(User $user): self { $this->user = $user; return $this; }
    public function setDriver(Driver $driver): self { $this->driver = $driver; return $this; }
    public function setTariff(Tariff $tariff): self { $this->tariff = $tariff; return $this; }   
}