<?php

namespace App\Service;

use App\Entity\Throttler;
use App\Repository\ThrottlerRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class ThrottlerService
{
    private const LIMIT = 5;
    private const TIME = 1;
    private const MINUTES = 10;

    private EntityManagerInterface $em;
    private ThrottlerRepository $throttlerRepository;

    public function __construct(EntityManagerInterface $entityManager, ThrottlerRepository $throttlerRepository)
    {
        $this->em = $entityManager;
        $this->throttlerRepository = $throttlerRepository;
    }

    public function check(string $addressIp, string $page)
    {
        $throttler = $this->throttlerRepository->findOneBy(['address_ip' => $addressIp, 'page' => $page]);
        if (!$throttler) {
            $throttler = $this->save($addressIp, $page);
        }

        $diff = date_diff(new DateTimeImmutable(), $throttler->getUpdatedAt());
        if ((($diff->i >= self::MINUTES || $diff->d > 0) && $throttler->getRateLimit() > self::LIMIT) ||
            ($diff->h > self::TIME && $throttler->getRateLimit() <= self::LIMIT)
        ) {
            $this->delete($throttler);

            return true;
        }

        if ($diff->h <= self::TIME && 0 === $diff->d && $throttler->getRateLimit() <= self::LIMIT) {
            $throttler->setRateLimit($throttler->getRateLimit() + 1);
            $this->update($throttler);

            return true;
        }

        return false;
    }

    /**
     * Sauvegarde la nouvelle adresse IP en table.
     */
    public function save(string $addressIp, string $page): Throttler
    {
        $throttler = (new Throttler())
            ->setAddressIp($addressIp)
            ->setRateLimit(1)
            ->setPage($page);

        $this->em->persist($throttler);
        $this->em->flush();

        return $throttler;
    }

    /**
     * Ajoute +1 à la limite.
     */
    public function update(Throttler $throttler): Throttler
    {
        $this->em->persist($throttler);
        $this->em->flush();

        return $throttler;
    }

    /**
     * Supprime une entrée.
     */
    public function delete(Throttler $throttler): Throttler
    {
        $this->em->remove($throttler);
        $this->em->flush();

        return $throttler;
    }

    /**
     * Vide la table.
     *
     * @throws Exception
     */
    public function truncate(): void
    {
        $this->em->getConnection()->executeStatement('TRUNCATE TABLE throttler');
    }
}
