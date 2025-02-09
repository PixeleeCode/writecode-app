<?php

namespace App\DataFixtures;

use App\DataFixtures\Loader\AppNativeLoader;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): bool
    {
        $this->cleanFolders();
        $loader = new AppNativeLoader($this->encoder);
        $objectSet = $loader->loadFile(__DIR__.'/fixtures.yaml')->getObjects();

        foreach ($objectSet as $object) {
            $manager->persist($object);
        }

        $manager->flush();

        return true;
    }

    /**
     * Vide les dossiers contenant les images et les caches.
     */
    private function cleanFolders(): bool
    {
        $fileSystem = new Filesystem();
        $folders = $fileSystem->exists([
            './public/uploads',
            './var/storage',
        ]);

        if ($folders) {
            $fileSystem->remove([
                './public/uploads',
                './var/storage',
            ]);
        }

        $fileSystem->mkdir([
            './public/uploads/courses',
            './public/uploads/technologies',
            './public/uploads/users',
            './public/uploads/trainings',
            './var/storage',
        ]);

        return true;
    }
}
