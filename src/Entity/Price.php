<?php

namespace App\Entity;

use App\Repository\PriceRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PriceRepository::class)
 * @ORM\Table(name="prices")
 */
class Price
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     *
     * @Assert\NotBlank(message="Le prix est requis", groups={"price:new"})
     * @Assert\Type(type="scalar", message="Le prix est incorrect", groups={"price:new"})
     */
    private ?string $amount = null;

    /**
     * @ORM\Column(type="string", length=35)
     *
     * @Assert\NotBlank(message="La récurrence est requise", groups={"price:new"})
     * @Assert\Choice({"month", "year"}, message="Le choix de la récurrence est incorrect", groups={"price:new"})
     */
    private ?string $recurring = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $is_visible = true;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private ?string $product_id = null;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private ?string $price_id = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $promote = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $promotional_text = null;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private ?DateTimeInterface $created_at = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private ?DateTimeInterface $updated_at = null;

    /**
     * @ORM\OneToMany(targetEntity=Subscription::class, mappedBy="price", orphanRemoval=true)
     */
    private Collection $subscriptions;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getRecurring(): ?string
    {
        return $this->recurring;
    }

    public function setRecurring(?string $recurring): self
    {
        $this->recurring = $recurring;

        return $this;
    }

    public function getIsVisible(): ?bool
    {
        return $this->is_visible;
    }

    public function setIsVisible(bool $is_visible): self
    {
        $this->is_visible = $is_visible;

        return $this;
    }

    public function getProductId(): ?string
    {
        return $this->product_id;
    }

    public function setProductId(string $product_id): self
    {
        $this->product_id = $product_id;

        return $this;
    }

    public function getPriceId(): ?string
    {
        return $this->price_id;
    }

    public function setPriceId(string $price_id): self
    {
        $this->price_id = $price_id;

        return $this;
    }

    public function getPromote(): ?bool
    {
        return $this->promote;
    }

    public function setPromote(bool $promote): self
    {
        $this->promote = $promote;

        return $this;
    }

    public function getPromotionalText(): ?string
    {
        return $this->promotional_text;
    }

    public function setPromotionalText(?string $promotional_text): self
    {
        $this->promotional_text = $promotional_text;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection|Subscription[]
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): self
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions[] = $subscription;
            $subscription->setPrice($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): self
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getPrice() === $this) {
                $subscription->setPrice(null);
            }
        }

        return $this;
    }
}
