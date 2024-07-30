<?php

namespace App\Entity;

use App\Repository\ToolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ToolRepository::class)]
class Tool
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tools')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'tools')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'tools')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Brand $brand = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(mappedBy: 'tool', cascade: ['persist', 'remove'])]
    private ?ToolInfo $toolInfo = null;

    /**
     * @var Collection<int, ProductPlace>
     */
    #[ORM\OneToMany(targetEntity: ProductPlace::class, mappedBy: 'tool')]
    private Collection $productPlaces;

    public function __construct()
    {
        $this->productPlaces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): static
    {
        $this->brand = $brand;

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

    public function getToolInfo(): ?ToolInfo
    {
        return $this->toolInfo;
    }

    public function setToolInfo(ToolInfo $toolInfo): static
    {
        // set the owning side of the relation if necessary
        if ($toolInfo->getTool() !== $this) {
            $toolInfo->setTool($this);
        }

        $this->toolInfo = $toolInfo;

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
            $productPlace->setTool($this);
        }

        return $this;
    }

    public function removeProductPlace(ProductPlace $productPlace): static
    {
        if ($this->productPlaces->removeElement($productPlace)) {
            // set the owning side to null (unless already changed)
            if ($productPlace->getTool() === $this) {
                $productPlace->setTool(null);
            }
        }

        return $this;
    }
}
