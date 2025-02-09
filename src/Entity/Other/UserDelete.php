<?php

namespace App\Entity\Other;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Type;

class UserDelete
{
    /**
     * @Type("string")
     *
     * @Assert\NotBlank(message="Le mot de passe est requis", groups={"user:delete"})
     * @SecurityAssert\UserPassword(message="Le mot de passe est invalide", groups={"user:delete"})
     */
    private ?string $password = null;

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
}
