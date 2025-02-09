<?php

namespace App\Entity;

use App\Repository\RedirectPermanentlyRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RedirectPermanentlyRepository::class)
 */
class RedirectPermanently
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $old_slug = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $new_slug = null;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private ?string $type = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private ?DateTimeImmutable $created_at = null;

    /**
     * @ORM\ManyToOne(targetEntity=Course::class, inversedBy="redirectPermanentlies", fetch="EXTRA_LAZY")
     */
    private ?Course $course;

    /**
     * @ORM\ManyToOne(targetEntity=Training::class, inversedBy="redirectPermanentlies", fetch="EXTRA_LAZY")
     */
    private ?Training $training;

    /**
     * @ORM\ManyToOne(targetEntity=Technologie::class, inversedBy="redirectPermanentlies", fetch="EXTRA_LAZY")
     */
    private ?Technologie $technology;

    /**
     * @ORM\ManyToOne(targetEntity=Page::class, inversedBy="redirectPermanentlies", fetch="EXTRA_LAZY")
     */
    private ?Page $page;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOldSlug(): ?string
    {
        return $this->old_slug;
    }

    public function setOldSlug(string $old_slug): self
    {
        $this->old_slug = $old_slug;

        return $this;
    }

    public function getNewSlug(): ?string
    {
        return $this->new_slug;
    }

    public function setNewSlug(string $new_slug): self
    {
        $this->new_slug = $new_slug;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getTraining(): ?Training
    {
        return $this->training;
    }

    public function setTraining(?Training $training): self
    {
        $this->training = $training;

        return $this;
    }

    public function getTechnology(): ?Technologie
    {
        return $this->technology;
    }

    public function setTechnology(?Technologie $technology): self
    {
        $this->technology = $technology;

        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): self
    {
        $this->page = $page;

        return $this;
    }
}
