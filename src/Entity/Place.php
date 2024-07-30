<?php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $locate = null;

    #[ORM\ManyToOne(inversedBy: 'places')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, ProductPlace>
     */
    #[ORM\OneToMany(targetEntity: ProductPlace::class, mappedBy: 'place')]
    private Collection $productPlaces;

    public function __construct()
    {
        $this->productPlaces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLocate(): ?string
    {
        return $this->locate;
    }

    public function setLocate(string $locate): static
    {
        $this->locate = $locate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, ProductPlace>
     */
    public function getProductPlaces(): Collection
    {
        return $this->productPlaces;
    }

    public function addProductPlace(ProductPlace $productPlace): static
    {
        if (!$this->productPlaces->contains($productPlace)) {
            $this->productPlaces->add($productPlace);
            $productPlace->setPlace($this);
        }

        return $this;
    }

    public function removeProductPlace(ProductPlace $productPlace): static
    {
        if ($this->productPlaces->removeElement($productPlace)) {
            // set the owning side to null (unless already changed)
            if ($productPlace->getPlace() === $this) {
                $productPlace->setPlace(null);
            }
        }

        return $this;
    }
}
