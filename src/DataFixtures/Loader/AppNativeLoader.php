<?php

namespace App\DataFixtures\Loader;

use App\DataFixtures\Provider\MarkdownProvider;
use App\DataFixtures\Provider\PasswordProvider;
use App\DataFixtures\Provider\PicsumProvider;
use App\DataFixtures\Provider\PictureUserProvider;
use Faker\Generator;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppNativeLoader extends NativeLoader
{
    private UserPasswordHasherInterface $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
        parent::__construct();
    }

    protected function createFakerGenerator(): Generator
    {
        $generator = parent::createFakerGenerator();
        $generator->addProvider(new PicsumProvider($generator));
        $generator->addProvider(new PictureUserProvider($generator));
        $generator->addProvider(new PasswordProvider($generator, $this->encoder));
        $generator->addProvider(new MarkdownProvider($generator));

        return $generator;
    }
}
