<?php

namespace App\Services;

class MegaStorageService
{
    protected $apiKey;
    protected $folder;

    public function __construct()
    {
        $this->apiKey = config('services.mega.api_key') ?? env('MEGA_API_KEY');
        $this->folder = config('services.mega.folder') ?? '/Formaneo';
    }

    // stub: uploader
    public function uploadVideo(string $localPath, string $remoteName): string
    {
        // implémenter l'API Mega ou S3 selon ta config.
        // Ici on renvoie une url fictive pour les seeders / dev.
        return "https://mega.mock/{$this->folder}/{$remoteName}";
    }
}
