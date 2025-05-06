<?php

namespace src\Entities;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Entity;

/**
 * @ORM\Entity
 * @ORM\Table(name="drivers")
 */
class Driver {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="bigint")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="driver")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", unique=true)
     */
    private User $user;

    /**
     * @ORM\Column(name="intership", type="smallint", options={"default": 0, "unsigned": true})
     */
    private $intership;

    /**
     * @ORM\Column(name="car_license", type="string", length=15)
     */
    private $car_license;

    /**
     * @ORM\Column(name="car_brand", type="string", length=50)
     */
    private $car_brand;

    /**
     * @ORM\Column(name="rating", type="float", options={"default": 0})
     */
    private $rating;

    /**
     * @ORM\OneToOne(targetEntity="Tariff", inversedBy="drivers")
     * @ORM\JoinColumn(name="tariff_id", referencedColumnName="id")
     */
    private ?Tariff $tariff = null;

    /**
     * @ORM\OneToMany(targetEntity="Order", mappedBy="driver")
     */
    private Collection $orders;

    const fields = array(
        'name',
        'phone',
        'email',
        'intership',
        'car_license',
        'car_brand',
        'tariff_id',
    );
    public function __construct(){
        $this->orders = new ArrayCollection();
    }

    public function getId(): int { return $this->id; }
    public function getUser(): User{ return $this->user;}
    public function getIntership(): int{ return $this->intership;}
    public function getCarLicense(): string{ return $this->car_license;}
    public function getCarBrand(): string{ return $this->car_brand;}
    public function getRating(): float{ return $this->rating;}
    public function getTariff(): ?Tariff{ return $this->tariff;}

    public function setUser(User $user): self { $this->user = $user; return $this;}
    public function setIntership(int $intership): self { $this->intership = $intership; return $this;}
    public function setCarLicense(string $car_license): self { $this->car_license = $car_license; return $this;}
    public function setCarBrand(string $car_brand): self { $this->car_brand = $car_brand; return $this;}
    public function setRating(float $rating): self { $this->rating = $rating; return $this;}
    public function setTariff(Tariff $tariff): self { $this->tariff = $tariff; return $this;}
}
?>