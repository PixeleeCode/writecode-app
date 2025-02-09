<?php

namespace App\Entity;

use App\Repository\TrainingRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TrainingRepository::class)
 * @ORM\Table(name="trainings")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class Training
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="trainings", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="string", length=120)
     *
     * @Assert\NotBlank(message="Le titre est requis", groups={"training:new", "training:edit"})
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
     * @Assert\NotBlank(message="La description est requise", groups={"training:new", "training:edit"})
     * @Assert\Length (
     *     max=255,
     *     maxMessage="La description est limitée à {{ limit }} caractères",
     *     groups={"training:new", "training:edit"}
     * )
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\Length (
     *     max=255,
     *     maxMessage="L'information est limitée à {{ limit }} caractères",
     *     groups={"training:new", "training:edit"}
     * )
     */
    private ?string $infos = null;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="L'image est requise", groups={"training:new"})
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
     *     groups={"training:new", "training:edit"}
     * )
     */
    private ?string $picture = null;

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
     * @ORM\OneToMany(targetEntity=Chapter::class, mappedBy="training", fetch="EXTRA_LAZY", cascade={"persist", "remove"}, orphanRemoval=true)
     * @OrderBy({"position" = "ASC"})
     */
    private Collection $chapters;

    /**
     * @ORM\OneToMany(targetEntity=RedirectPermanently::class, mappedBy="training", fetch="EXTRA_LAZY", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $redirectPermanentlies;

    public function __construct()
    {
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

    public function getInfos(): ?string
    {
        return $this->infos;
    }

    public function setInfos(?string $infos): self
    {
        $this->infos = $infos;

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
            $chapter->setTraining($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): self
    {
        if ($this->chapters->removeElement($chapter)) {
            // set the owning side to null (unless already changed)
            if ($chapter->getTraining() === $this) {
                $chapter->setTraining(null);
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
            $redirectPermanently->setTraining($this);
        }

        return $this;
    }

    public function removeRedirectPermanently(RedirectPermanently $redirectPermanently): self
    {
        if ($this->redirectPermanentlies->removeElement($redirectPermanently)) {
            // set the owning side to null (unless already changed)
            if ($redirectPermanently->getTraining() === $this) {
                $redirectPermanently->setTraining(null);
            }
        }

        return $this;
    }
}
