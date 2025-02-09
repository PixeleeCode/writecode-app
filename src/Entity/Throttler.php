<?php

namespace App\Entity;

use App\Repository\ThrottlerRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass=ThrottlerRepository::class)
 */
class Throttler
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private ?string $address_ip = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $rate_limit = null;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private ?string $page = null;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private ?DateTimeInterface $created_at = null;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private ?DateTimeInterface $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddressIp(): ?string
    {
        return $this->address_ip;
    }

    public function setAddressIp(string $address_ip): self
    {
        $this->address_ip = $address_ip;

        return $this;
    }

    public function getRateLimit(): ?int
    {
        return $this->rate_limit;
    }

    public function setRateLimit(int $rate_limit): self
    {
        $this->rate_limit = $rate_limit;

        return $this;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    public function setPage(string $page): self
    {
        $this->page = $page;

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

    public function setUpdatedAt(DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
