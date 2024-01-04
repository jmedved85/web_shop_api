<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $SKU;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=2)
     */
    private $netPrice;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $published;

    /**
     * @ORM\OneToMany(targetEntity=ProductPriceList::class, mappedBy="product", cascade={"persist"}, orphanRemoval=true, fetch="EAGER")
     */
    private $productPriceLists;

    /**
     * @ORM\OneToMany(targetEntity=ProductCategory::class, mappedBy="product", cascade={"persist"}, orphanRemoval=true, fetch="EAGER")
     */
    private $productCategories;

    /**
     * @ORM\OneToMany(targetEntity=ContractList::class, mappedBy="product", cascade={"persist"}, orphanRemoval=true, fetch="EAGER")
     */
    private $contractLists;

    /**
     * @ORM\OneToMany(targetEntity=OrderProduct::class, mappedBy="product", cascade={"persist"}, orphanRemoval=true, fetch="EAGER")
     */
    private $orderProducts;

    public function __construct()
    {
        $this->productPriceLists = new ArrayCollection();
        $this->productCategories = new ArrayCollection();
        $this->contractLists = new ArrayCollection();
        $this->orderProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSKU(): ?string
    {
        return $this->SKU;
    }

    public function setSKU(string $SKU): self
    {
        $this->SKU = $SKU;

        return $this;
    }

    public function getNetPrice(): ?string
    {
        return $this->netPrice;
    }

    public function setNetPrice(string $netPrice): self
    {
        $this->netPrice = $netPrice;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(?bool $published): self
    {
        $this->published = $published;

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
            $productPriceList->setProduct($this);
        }

        return $this;
    }

    public function removeProductPriceList(ProductPriceList $productPriceList): self
    {
        if ($this->productPriceLists->removeElement($productPriceList)) {
            // set the owning side to null (unless already changed)
            if ($productPriceList->getProduct() === $this) {
                $productPriceList->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public function getProductCategories(): Collection
    {
        return $this->productCategories;
    }

    public function addProductCategory(ProductCategory $productCategory): self
    {
        if (!$this->productCategories->contains($productCategory)) {
            $this->productCategories[] = $productCategory;
            $productCategory->setProduct($this);
        }

        return $this;
    }

    public function removeProductCategory(ProductCategory $productCategory): self
    {
        if ($this->productCategories->removeElement($productCategory)) {
            // set the owning side to null (unless already changed)
            if ($productCategory->getProduct() === $this) {
                $productCategory->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ContractList>
     */
    public function getContractLists(): Collection
    {
        return $this->contractLists;
    }

    public function addContractList(ContractList $contractList): self
    {
        if (!$this->contractLists->contains($contractList)) {
            $this->contractLists[] = $contractList;
            $contractList->setProduct($this);
        }

        return $this;
    }

    public function removeContractList(ContractList $contractList): self
    {
        if ($this->contractLists->removeElement($contractList)) {
            // set the owning side to null (unless already changed)
            if ($contractList->getProduct() === $this) {
                $contractList->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrderProduct>
     */
    public function getOrderProducts(): Collection
    {
        return $this->orderProducts;
    }

    public function addOrderProduct(OrderProduct $orderProduct): self
    {
        if (!$this->orderProducts->contains($orderProduct)) {
            $this->orderProducts[] = $orderProduct;
            $orderProduct->setProduct($this);
        }

        return $this;
    }

    public function removeOrderProduct(OrderProduct $orderProduct): self
    {
        if ($this->orderProducts->removeElement($orderProduct)) {
            // set the owning side to null (unless already changed)
            if ($orderProduct->getProduct() === $this) {
                $orderProduct->setProduct(null);
            }
        }

        return $this;
    }
}
