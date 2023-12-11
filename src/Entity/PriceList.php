<?php

namespace App\Entity;

use App\Repository\PriceListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PriceListRepository::class)
 */
class PriceList
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=ProductPriceList::class, mappedBy="priceList", cascade={"persist"}, orphanRemoval=true)
     */
    private $productPriceLists;

    public function __construct()
    {
        $this->productPriceLists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, ProductPriceList>
     */
    public function getProductPriceLists(): Collection
    {
        return $this->productPriceLists;
    }

    public function addProductPriceList(ProductPriceList $productPriceList): self
    {
        if (!$this->productPriceLists->contains($productPriceList)) {
            $this->productPriceLists[] = $productPriceList;
            $productPriceList->setPriceList($this);
        }

        return $this;
    }

    public function removeProductPriceList(ProductPriceList $productPriceList): self
    {
        if ($this->productPriceLists->removeElement($productPriceList)) {
            // set the owning side to null (unless already changed)
            if ($productPriceList->getPriceList() === $this) {
                $productPriceList->setPriceList(null);
            }
        }

        return $this;
    }
}
