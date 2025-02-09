<?php

namespace App\Entity;

use App\Repository\TechnologieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TechnologieRepository::class)
 * @ORM\Table(name="technologies")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Technologie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=50)
     *
     * @Assert\NotBlank(message="Le nom est requis", groups={"technology:new", "technology:edit"})
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Slug(fields={"name"})
     */
    private ?string $slug = null;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="La description est requise", groups={"technology:new", "technology:edit"})
     * @Assert\Length (
     *     max=255,
     *     maxMessage="La description est limitée à {{ limit }} caractères",
     *     groups={"technology:new", "technology:edit"}
     * )
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="L'image est requise", groups={"technology:new"})
     * @Assert\Image(
     *     maxSize="1M",
     *     maxSizeMessage="Le poids de l'image est supérieur à {{ limit }}Mo",
     *     maxHeight=256,
     *     maxHeightMessage="La hauteur doit être de {{ max_height }} pixels",
     *     minHeight=256,
     *     minHeightMessage="La hauteur doit être de {{ min_height }} pixels",
     *     maxWidth=256,
     *     maxWidthMessage="La largeur doit être de {{ max_width }} pixels",
     *     minWidth=256,
     *     minWidthMessage="La largeur doit être de {{ min_width }} pixels",
     *     mimeTypes={"image/gif", "image/png", "image/jpeg"},
     *     mimeTypesMessage="Le type de l'image est invalide",
     *     sizeNotDetectedMessage="Les dimensions de l'image n'ont pu être déterminées",
     *     groups={"technology:new", "technology:edit"}
     * )
     */
    private ?string $picture = null;

    /**
     * @ORM\ManyToMany(targetEntity=Course::class, mappedBy="technology", fetch="EXTRA_LAZY")
     * @OrderBy({"created_at" = "DESC"})
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private Collection $courses;

    /**
     * @ORM\OneToMany(targetEntity=RedirectPermanently::class, mappedBy="technology", fetch="EXTRA_LAZY", cascade={"persist", "remove"})
     */
    private Collection $redirectPermanentlies;

    public function __construct()
    {
        $this->courses = new ArrayCollection();
        $this->redirectPermanentlies = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    /**
     * @return Collection|Course[]
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Course $course): self
    {
        if (!$this->courses->contains($course)) {
            $this->courses[] = $course;
            $course->addTechnology($this);
        }

        return $this;
    }

    public function removeCourse(Course $course): self
    {
        if ($this->courses->removeElement($course)) {
            $course->removeTechnology($this);
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
            $redirectPermanently->setTechnology($this);
        }

        return $this;
    }

    public function removeRedirectPermanently(RedirectPermanently $redirectPermanently): self
    {
        if ($this->redirectPermanentlies->removeElement($redirectPermanently)) {
            // set the owning side to null (unless already changed)
            if ($redirectPermanently->getTechnology() === $this) {
                $redirectPermanently->setTechnology(null);
            }
        }

        return $this;
    }
}
