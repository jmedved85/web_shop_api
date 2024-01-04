<?php

namespace App\Entity;

use App\Repository\ProductPriceListRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductPriceListRepository::class)
 */
class ProductPriceList
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="productPriceLists")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity=PriceList::class, inversedBy="productPriceLists")
     */
    private $priceList;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=2)
     */
    private $price;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getPriceList(): ?PriceList
    {
        return $this->priceList;
    }

    public function setPriceList(?PriceList $priceList): self
    {
        $this->priceList = $priceList;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }
}
