<?php

namespace App\Entity;

use App\Repository\CourseRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CourseRepository::class)
 * @ORM\Table(name="courses")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Course
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="courses", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le titre est requis", groups={"course:new", "course:edit"})
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Slug(fields={"title"})
     */
    private ?string $slug = null;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="L'image est requise", groups={"course:new"})
     * @Assert\Image(
     *     maxSize="1M",
     *     maxSizeMessage="Le poids de l'image est supérieur à {{ limit }}Mo",
     *     maxHeight=800,
     *     maxHeightMessage="La hauteur doit être de {{ max_height }} pixels",
     *     minHeight=800,
     *     minHeightMessage="La hauteur doit être de {{ min_height }} pixels",
     *     maxWidth=1280,
     *     maxWidthMessage="La largeur doit être de {{ max_width }} pixels",
     *     minWidth=1280,
     *     minWidthMessage="La largeur doit être de {{ min_width }} pixels",
     *     mimeTypes={"image/gif", "image/png", "image/jpeg"},
     *     mimeTypesMessage="Le type de l'image est invalide",
     *     sizeNotDetectedMessage="Les dimensions de l'image n'ont pu être déterminées",
     *     groups={"course:new", "course:edit"}
     * )
     */
    private ?string $picture = null;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(message="Le contenu est requis", groups={"course:new", "course:edit"})
     */
    private ?string $content = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $draft = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $premium = false;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @Assert\NotBlank(message="La date de publication est requise", groups={"course:new", "course:edit"})
     */
    private ?DateTimeInterface $created_at = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private ?DateTimeInterface $updated_at = null;

    /**
     * @ORM\ManyToMany(targetEntity=Technologie::class, inversedBy="courses", fetch="EXTRA_LAZY", cascade={"persist"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private Collection $technology;

    /**
     * @ORM\OneToMany(targetEntity=Chapter::class, mappedBy="course", fetch="EXTRA_LAZY", cascade={"persist", "remove"})
     * @OrderBy({"position" = "ASC"})
     */
    private Collection $chapters;

    /**
     * @ORM\OneToMany(targetEntity=RedirectPermanently::class, mappedBy="course", fetch="EXTRA_LAZY", cascade={"persist", "remove"})
     */
    private Collection $redirectPermanentlies;

    public function __construct()
    {
        $this->technology = new ArrayCollection();
        $this->chapters = new ArrayCollection();
        $this->redirectPermanentlies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDraft(): ?bool
    {
        return $this->draft;
    }

    public function setDraft(bool $draft): self
    {
        $this->draft = $draft;

        return $this;
    }

    public function getPremium(): ?bool
    {
        return $this->premium;
    }

    public function setPremium(bool $premium): self
    {
        $this->premium = $premium;

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
     * @return Collection|Technologie[]
     */
    public function getTechnology(): Collection
    {
        return $this->technology;
    }

    public function addTechnology(Technologie $technology): self
    {
        if (!$this->technology->contains($technology)) {
            $this->technology[] = $technology;
        }

        return $this;
    }

    public function removeTechnology(Technologie $technology): self
    {
        $this->technology->removeElement($technology);

        return $this;
    }

    /**
     * @return Collection|Chapter[]
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    public function addChapter(Chapter $chapter): self
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters[] = $chapter;
            $chapter->setCourse($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): self
    {
        if ($this->chapters->removeElement($chapter)) {
            // set the owning side to null (unless already changed)
            if ($chapter->getCourse() === $this) {
                $chapter->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RedirectPermanently>
     */
    public function getRedirectPermanentlies(): Collection
    {
        return $this->redirectPermanentlies;
    }

    public function addRedirectPermanently(RedirectPermanently $redirectPermanently): self
    {
        if (!$this->redirectPermanentlies->contains($redirectPermanently)) {
            $this->redirectPermanentlies[] = $redirectPermanently;
            $redirectPermanently->setCourse($this);
        }

        return $this;
    }

    public function removeRedirectPermanently(RedirectPermanently $redirectPermanently): self
    {
        if ($this->redirectPermanentlies->removeElement($redirectPermanently)) {
            // set the owning side to null (unless already changed)
            if ($redirectPermanently->getCourse() === $this) {
                $redirectPermanently->setCourse(null);
            }
        }

        return $this;
    }
}
