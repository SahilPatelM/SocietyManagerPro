<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DocumentService
{
    public function upload(User $user, array $data, UploadedFile $file): Document
    {
        return DB::transaction(function () use ($user, $data, $file) {
            $path = $file->store("societies/{$user->society_id}/documents", 'public');

            return Document::create([
                'society_id' => $user->society_id,
                'uploaded_by' => $user->id,
                'title' => $data['title'],
                'category' => $data['category'],
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        });
    }
}
