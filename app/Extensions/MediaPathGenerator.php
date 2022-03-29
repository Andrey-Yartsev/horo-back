<?php

namespace App\Extensions;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class MediaPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $pathParts = [];

        for ($index = 0; true; ++$index) {
            $division = (int) floor(($media->id - 1) / pow(256, $index + 1));

            if ($division === 0) {
                break;
            }

            $pathParts[] = sprintf('%02x', $division);
        }

        $pathParts[] = substr(md5($media->id . config('app.key')), 0, 16) . sprintf('%x', $media->id);

        return implode('/', $pathParts) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'c/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'r/';
    }
}
