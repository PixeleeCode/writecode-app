<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="users")
 * @UniqueEntity(fields={"email"}, message="Cette adresse email est utilisée", groups={"user:new", "user:edit"})
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
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
     * @Assert\NotBlank(message="Le prénom est requis", groups={"user:new", "user:edit"})
     */
    private ?string $firstname = null;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     * @Assert\NotBlank(message="Le nom est requis", groups={"user:new", "user:edit"})
     */
    private ?string $lastname = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $picture = null;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     *
     * @Assert\NotBlank(message="L'adresse email est requise", groups={"user:new", "user:lost", "user:edit"})
     * @Assert\Email(message="L'adresse email est invalide", groups={"user:new", "user:lost", "user:edit"})
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string|null The hashed password
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\NotBlank(message="Le mot de passe est requis", groups={"user:new", "user:reset"})
     * @Assert\Length(min=6, minMessage="Le mot de passe doit contenir au minimum {{ limit }} caractères", groups={"user:new", "user:reset"})
     */
    private ?string $password = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $github_id = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $gitlab_id = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $discord_id = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $stripe_id = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $notifications = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\IsTrue(message="L'acceptation des conditions est requise")
     */
    private ?bool $rgpd = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $token = null;

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
     * @ORM\OneToMany(targetEntity=Course::class, mappedBy="user", orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private Collection $courses;

    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="user", orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
     */
    private Collection $trainings;

    /**
     * @ORM\OneToOne(targetEntity=Subscription::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private ?Subscription $subscription = null;

    public function __construct()
    {
        $this->courses = new ArrayCollection();
        $this->trainings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return ucwords($this->firstname).' '.ucwords($this->lastname);
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getGithubId(): ?string
    {
        return $this->github_id;
    }

    public function setGithubId(?string $github_id): self
    {
        $this->github_id = $github_id;

        return $this;
    }

    public function getGitlabId(): ?int
    {
        return $this->gitlab_id;
    }

    public function setGitlabId(?int $gitlab_id): self
    {
        $this->gitlab_id = $gitlab_id;

        return $this;
    }

    public function getDiscordId(): ?string
    {
        return $this->discord_id;
    }

    public function setDiscordId(?string $discord_id): self
    {
        $this->discord_id = $discord_id;

        return $this;
    }

    public function getStripeId(): ?string
    {
        return $this->stripe_id;
    }

    public function setStripeId(?string $stripe_id): self
    {
        $this->stripe_id = $stripe_id;

        return $this;
    }

    public function getNotifications(): ?bool
    {
        return $this->notifications;
    }

    public function setNotifications(bool $notifications): self
    {
        $this->notifications = $notifications;

        return $this;
    }

    public function getRgpd(): ?bool
    {
        return $this->rgpd;
    }

    public function setRgpd(bool $rgpd): self
    {
        $this->rgpd = $rgpd;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

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
            $course->setUser($this);
        }

        return $this;
    }

    public function removeCourse(Course $course): self
    {
        if ($this->courses->removeElement($course)) {
            // set the owning side to null (unless already changed)
            if ($course->getUser() === $this) {
                $course->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Training[]
     */
    public function getTrainings(): Collection
    {
        return $this->trainings;
    }

    public function addTraining(Training $training): self
    {
        if (!$this->trainings->contains($training)) {
            $this->trainings[] = $training;
            $training->setUser($this);
        }

        return $this;
    }

    public function removeTraining(Training $training): self
    {
        if ($this->trainings->removeElement($training)) {
            // set the owning side to null (unless already changed)
            if ($training->getUser() === $this) {
                $training->setUser(null);
            }
        }

        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(Subscription $subscription): self
    {
        // set the owning side of the relation if necessary
        if ($subscription->getUser() !== $this) {
            $subscription->setUser($this);
        }

        $this->subscription = $subscription;

        return $this;
    }
}
