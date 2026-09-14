<?php

namespace App\Enums;

enum PhotoConsentType: string
{
    case PublicSpace = 'public_space';
    case ModelReleaseOnFile = 'model_release_on_file';
    case NoIdentifiableFaces = 'no_identifiable_faces';

    public function label(): string
    {
        return match ($this) {
            self::PublicSpace => 'Public Space',
            self::ModelReleaseOnFile => 'Model Release on File',
            self::NoIdentifiableFaces => 'No Identifiable Faces',
        };
    }
}
