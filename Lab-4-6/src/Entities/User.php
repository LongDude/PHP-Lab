<?php

namespace src\Entities;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private string $phone;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private string $email;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private string $password;

    /**
     * @ORM\Column(type="string", length=10, options={"default": "client"})
     */
    private string $role = 'client';

    /**
     * @ORM\OneToOne(targetEntity="Driver", mappedBy="user")
     */
    private ?Driver $driver = null;

    /**
     * @ORM\OneToMany(targetEntity="Order", mappedBy="user")
     */
    private Collection $orders;

    public const fields = array(
        'name',
        'phone',
        'email',
        'password',
        'role',
    );

    public function __construct(){
        $this->orders = new ArrayCollection();
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getPhone(): string { return $this->phone; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getRole(): string { return $this->role; }
    public function getDriver(): ?Driver { return $this->driver; }
    public function getOrders(): Collection { return $this->orders; }
    

    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setPhone(string $phone): self { $this->phone = $phone; return $this; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function setPassword(string $password): self { $this->password = md5($password); return $this; }
    public function setRole(string $role): self { $this->role = $role; return $this; }
}