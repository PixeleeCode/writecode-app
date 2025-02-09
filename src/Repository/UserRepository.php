<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use Omines\OAuth2\Client\Provider\GitlabResourceOwner;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);

        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findOrCreateFromGithubOauth(GithubResourceOwner $owner): User
    {
        /** @var User|null $user */
        $user = $this->createQueryBuilder('u')
            ->where('u.github_id = :githubId')
            ->orWhere('u.email = :email')
            ->setParameters([
                'githubId' => $owner->getId(),
                'email' => $owner->getEmail(),
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if ($user) {
            if (null === $user->getGithubId()) {
                $user->setGithubId($owner->getId());
                $this->_em->persist($user);
                $this->_em->flush();
            }

            return $user;
        }

        $githubUser = explode(' ', $owner->getName());
        $user = (new User())
            ->setGithubId($owner->getId())
            ->setEmail($owner->getEmail())
            ->setFirstname($githubUser[0] ?? $owner->getNickname())
            ->setLastname($githubUser[1] ?? null)
            ->setRgpd(true)
        ;

        $this->_em->persist($user);
        $this->_em->flush();

        return $user;
    }

    public function findOrCreateFromGitlabOauth(GitlabResourceOwner $owner): User
    {
        /** @var User|null $user */
        $user = $this->createQueryBuilder('u')
            ->where('u.gitlab_id = :gitlabId')
            ->orWhere('u.email = :email')
            ->setParameters([
                'gitlabId' => $owner->getId(),
                'email' => $owner->getEmail(),
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if ($user) {
            if (null === $user->getGithubId()) {
                $user->setGitlabId($owner->getId());
                $this->_em->persist($user);
                $this->_em->flush();
            }

            return $user;
        }

        $gitlabUser = explode(' ', $owner->getName());
        $user = (new User())
            ->setGitlabId($owner->getId())
            ->setEmail($owner->getEmail())
            ->setFirstname($gitlabUser[0] ?? $owner->getUsername())
            ->setLastname($gitlabUser[1] ?? null)
            ->setRgpd(true)
        ;

        $this->_em->persist($user);
        $this->_em->flush();

        return $user;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findForOauth(string $service, ?string $serviceId, ?string $email): ?User
    {
        if (null === $serviceId /* || null === $email */) {
            return null;
        }

        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->orWhere("u.{$service}_id = :serviceId")
            ->setMaxResults(1)
            ->setParameters([
                'email' => $email,
                'serviceId' => $serviceId,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
