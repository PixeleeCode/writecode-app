<?php

namespace App\Entity\Other;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Type;

class UserPassword
{
    /**
     * @Type("string")
     *
     * @Assert\NotBlank(message="Le mot de passe est requis", groups={"user:edit:password", "user:edit:password-social"})
     * @Assert\Length(min=6, minMessage="Le mot de passe doit contenir au minimum {{ limit }} caractères",
     * groups={
     *     "user:edit:password", "user:edit:password-social"
     * })
     */
    private ?string $password = null;

    /**
     * @Type("string")
     *
     * @Assert\NotBlank(message="Le mot de passe actuel est requis", groups={"user:edit:password"})
     * @SecurityAssert\UserPassword(message="Le mot de passe ne correspond pas avec l'actuel", groups={"user:edit:password"})
     */
    private ?string $oldPassword = null;

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

    public function getOldPassword(): string
    {
        return (string) $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }
}
