<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        // Kalau status published, isi published_by dan published_at
        if ($data['status'] === 'published') {
            $data['published_by'] = auth()->id();
            $data['published_at'] = now();
        }

        return $data;
    }
}
