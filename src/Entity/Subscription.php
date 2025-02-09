<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubscriptionRepository::class)
 * @ORM\Table("subscriptions")
 */
class Subscription
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="subscription", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user = null;

    /**
     * @ORM\ManyToOne(targetEntity=Price::class, inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Price $price = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $subscription_id = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $next_payment = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $date_end = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $is_active = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPrice(): ?Price
    {
        return $this->price;
    }

    public function setPrice(?Price $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getSubscriptionId(): ?string
    {
        return $this->subscription_id;
    }

    public function setSubscriptionId(string $subscription_id): self
    {
        $this->subscription_id = $subscription_id;

        return $this;
    }

    public function getNextPayment(): ?DateTimeInterface
    {
        return $this->next_payment;
    }

    public function setNextPayment(DateTimeInterface $next_payment): self
    {
        $this->next_payment = $next_payment;

        return $this;
    }

    public function getDateEnd(): ?DateTimeInterface
    {
        return $this->date_end;
    }

    public function setDateEnd(DateTimeInterface $date_end): self
    {
        $this->date_end = $date_end;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;

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
}
