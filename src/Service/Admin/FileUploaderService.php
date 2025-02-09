<?php

namespace App\Service\Admin;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploaderService
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function upload(string $path, UploadedFile $file, ?string $oldPicture = null): string
    {
        if ($oldPicture) {
            $this->remove($path, $oldPicture);
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug(strtolower($originalFilename));
        $fileName = $safeFilename.'-'.uniqid('', true).'.'.$file->guessExtension();

        try {
            $file->move($path, $fileName);
        } catch (FileException $e) {
            throw new FileException($e->getMessage());
        }

        return $fileName;
    }

    public function remove(string $path, string $file): bool
    {
        $pathFile = $path.'/'.$file;
        $filesystem = new Filesystem();
        if ($filesystem->exists($pathFile)) {
            $filesystem->remove([$pathFile]);
        }

        return true;
    }
}
