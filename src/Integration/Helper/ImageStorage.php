<?php

declare(strict_types=1);

namespace App\Integration\Helper;

use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;

final class ImageStorage
{
    public function __construct(
        private readonly string $storagePath,
        private readonly string $publicPrefix = '/uploads/ads/'
    ) {
        $directory = rtrim($this->storagePath, '/\\');
        if ($directory === '') {
            throw new InvalidArgumentException('Storage path for uploads cannot be empty.');
        }

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }

    /**
     * @param array<int, UploadedFileInterface|mixed>|UploadedFileInterface|null $files
     * @return string[]
     */
    public function store(array|UploadedFileInterface|null $files): array
    {
        if ($files instanceof UploadedFileInterface) {
            $files = [$files];
        }

        if (!is_array($files)) {
            return [];
        }

        $paths = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFileInterface || $file->getError() !== \UPLOAD_ERR_OK) {
                continue;
            }

            $paths[] = $this->storeSingleFile($file);
        }

        return $paths;
    }

    private function storeSingleFile(UploadedFileInterface $file): string
    {
        $clientName = $file->getClientFilename() ?? '';
        $extension = pathinfo($clientName, PATHINFO_EXTENSION);
        $safeExtension = $extension !== '' ? '.' . preg_replace('/[^a-zA-Z0-9]/', '', $extension) : '';
        $uniqueName = bin2hex(random_bytes(12)) . $safeExtension;
        $targetPath = rtrim($this->storagePath, '/\\') . '/' . $uniqueName;

        $file->moveTo($targetPath);

        return rtrim($this->publicPrefix, '/') . '/' . $uniqueName;
    }
}
