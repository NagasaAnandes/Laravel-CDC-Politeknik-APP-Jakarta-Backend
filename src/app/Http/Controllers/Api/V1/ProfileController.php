<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Requests\Api\V1\Profile\StoreCvRequest;
use App\Models\Experience;
use App\Models\Education;
use App\Models\Certificate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Support\ApiResponse;
use App\Domain\CareerProfile\Services\ExperienceService;
use App\Domain\CareerProfile\Services\CertificateService;
use App\Http\Requests\Api\V1\Profile\StoreExperienceRequest;
use App\Http\Requests\Api\V1\Profile\UpdateExperienceRequest;
use App\Http\Requests\Api\V1\Profile\StoreEducationRequest;
use App\Http\Requests\Api\V1\Profile\StoreCertificateRequest;

class ProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    public function show(Request $request)
    {
        $user = $request->user();

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->value,
            'profile' => [
                'phone' => $user->phone,
                'linkedin_url' => $user->linkedin_url,
                'graduation_year' => $user->graduation_year,
                'program_study' => $user->program_study,
                'has_cv' => $user->hasCv(),
                'cv_url' => $user->cv_url,
            ],
        ]);
    }

    public function storeCv(StoreCvRequest $request)
    {
        $user = $request->user();
        $oldCvPath = $user->cv_path;

        $file = $request->file('file');
        $directory = 'cvs/user-' . $user->id;
        $filename = Str::uuid() . '.' . $file->extension();
        $path = Storage::disk('private')->putFileAs($directory, $file, $filename);

        DB::transaction(function () use ($user, $path, $oldCvPath) {
            $user->forceFill(['cv_path' => $path])->save();

            if ($oldCvPath && Storage::disk('private')->exists($oldCvPath)) {
                Storage::disk('private')->delete($oldCvPath);
            }
        });

        return ApiResponse::success(
            [
                'cv_url' => $user->fresh()->cv_url,
                'has_cv' => true,
            ],
            'CV uploaded successfully',
            201
        );
    }

    public function downloadCv(Request $request)
    {
        $user = $request->user();

        if (!$user->hasCv() || !Storage::disk('private')->exists($user->cv_path)) {
            return ApiResponse::notFound('File not found');
        }

        return response()->download(Storage::disk('private')->path($user->cv_path));
    }

    public function deleteCv(Request $request)
    {
        $user = $request->user();

        if ($user->cv_path && Storage::disk('private')->exists($user->cv_path)) {
            Storage::disk('private')->delete($user->cv_path);
        }

        $user->forceFill(['cv_path' => null])->save();

        return ApiResponse::success(
            null,
            'CV deleted successfully'
        );
    }

    public function update(UpdateProfileRequest $request)
    {
        $request->user()->update($request->validated());

        return ApiResponse::success(
            null,
            'Profile updated successfully'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EXPERIENCE
    |--------------------------------------------------------------------------
    */

    public function experiences(Request $request)
    {
        $this->authorize('viewAny', Experience::class);

        $data = Experience::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return ApiResponse::success($data);
    }

    public function storeExperience(
        StoreExperienceRequest $request,
        ExperienceService $service
    ) {
        $this->authorize('create', Experience::class);

        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $experience = $service->create($data);

        return ApiResponse::success(
            $experience,
            'Experience created successfully',
            201
        );
    }

    public function updateExperience(
        UpdateExperienceRequest $request,
        int $id,
        ExperienceService $service
    ) {
        $experience = Experience::findOrFail($id);

        $this->authorize('update', $experience);

        $data = $request->validated();

        $experience = $service->update($experience, $data);

        return ApiResponse::success(
            $experience,
            'Experience updated successfully'
        );
    }

    public function deleteExperience(int $id)
    {
        $experience = Experience::findOrFail($id);

        $this->authorize('delete', $experience);

        $experience->delete();

        return ApiResponse::success(
            null,
            'Experience deleted successfully'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDUCATION
    |--------------------------------------------------------------------------
    */

    public function educations(Request $request)
    {
        $this->authorize('viewAny', Education::class);

        $data = Education::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return ApiResponse::success($data);
    }

    public function storeEducation(StoreEducationRequest $request)
    {
        $this->authorize('create', Education::class);

        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $education = Education::create($data);

        return ApiResponse::success(
            $education,
            'Education created successfully',
            201
        );
    }

    public function deleteEducation(int $id)
    {
        $education = Education::findOrFail($id);

        $this->authorize('delete', $education);

        $education->delete();

        return ApiResponse::success(
            null,
            'Education deleted successfully'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CERTIFICATE
    |--------------------------------------------------------------------------
    */

    public function storeCertificate(
        StoreCertificateRequest $request,
        CertificateService $service
    ) {
        $this->authorize('create', Certificate::class);

        $data = $request->validated();

        $certificate = $service->store(
            $request->user(),
            $data,
            $request->file('file')
        );

        return ApiResponse::success(
            $certificate,
            'Certificate uploaded successfully',
            201
        );
    }

    public function downloadCertificate(int $id)
    {
        $certificate = Certificate::findOrFail($id);

        $this->authorize('download', $certificate);

        if (!Storage::exists($certificate->file_path)) {
            return ApiResponse::notFound('File not found');
        }

        return Storage::download($certificate->file_path);
    }

    public function deleteCertificate(int $id, CertificateService $service)
    {
        $certificate = Certificate::findOrFail($id);

        $this->authorize('delete', $certificate);

        $service->delete($certificate);

        return ApiResponse::success(
            null,
            'Certificate deleted successfully'
        );
    }
}
