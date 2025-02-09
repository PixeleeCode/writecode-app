<?php

namespace App\Entity;

use App\Repository\PageRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PageRepository::class)
 * @ORM\Table("pages")
 */
class Page
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Le titre est requis", groups={"page:new", "page:edit"})
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Slug(fields={"title"})
     */
    private ?string $slug = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $content = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $is_visible = false;

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
     * @ORM\OneToMany(targetEntity=RedirectPermanently::class, mappedBy="page", fetch="EXTRA_LAZY", cascade={"persist", "remove"})
     */
    private Collection $redirectPermanentlies;

    public function __construct()
    {
        $this->redirectPermanentlies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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
            $redirectPermanently->setPage($this);
        }

        return $this;
    }

    public function removeRedirectPermanently(RedirectPermanently $redirectPermanently): self
    {
        if ($this->redirectPermanentlies->removeElement($redirectPermanently)) {
            // set the owning side to null (unless already changed)
            if ($redirectPermanently->getPage() === $this) {
                $redirectPermanently->setPage(null);
            }
        }

        return $this;
    }
}
