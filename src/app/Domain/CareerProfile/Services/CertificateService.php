<?php

namespace App\Domain\CareerProfile\Services;

use App\Models\Certificate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateService
{
    public function store($user, array $data, $file): Certificate
    {
        return DB::transaction(function () use ($user, $data, $file) {

            $path = $file->storeAs(
                'certificates/' . $user->id,
                Str::uuid() . '.' . $file->getClientOriginalExtension()
            );

            $certificate = Certificate::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'issuer' => $data['issuer'] ?? null,
                'issue_date' => $data['issue_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'file_mime' => $file->getMimeType(),
            ]);

            return $certificate;
        });
    }

    public function delete(Certificate $certificate): void
    {
        DB::transaction(function () use ($certificate) {

            if (Storage::exists($certificate->file_path)) {
                Storage::delete($certificate->file_path);
            }

            $certificate->delete();
        });
    }
}
