<?php

namespace App\Entity;

use App\Repository\ChapterRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ChapterRepository::class)
 * @ORM\Table("chapters")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Chapter
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Training::class, inversedBy="chapters", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private ?Training $training = null;

    /**
     * @ORM\ManyToOne(targetEntity=Course::class, inversedBy="chapters", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private ?Course $course = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $position;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): self
    {
        $this->course = $course;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
