<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Kalau baru di-publish (sebelumnya draft)
        if ($data['status'] === 'published' && ! $this->record->published_at) {
            $data['published_by'] = auth()->id();
            $data['published_at'] = now();
        }

        return $data;
    }
}
