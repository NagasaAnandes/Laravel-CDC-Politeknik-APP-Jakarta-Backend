<?php

namespace App\Domain\CareerProfile\Services;

use App\Models\Experience;
use Illuminate\Support\Facades\DB;

class ExperienceService
{
    public function create(array $data): Experience
    {
        return DB::transaction(function () use ($data) {

            if (!empty($data['is_current'])) {
                Experience::where('user_id', $data['user_id'])
                    ->update(['is_current' => false]);
            }

            return Experience::create($data);
        });
    }

    public function update(Experience $experience, array $data): Experience
    {
        return DB::transaction(function () use ($experience, $data) {

            if (!empty($data['is_current'])) {
                Experience::where('user_id', $experience->user_id)
                    ->where('id', '!=', $experience->id)
                    ->update(['is_current' => false]);
            }

            $experience->update($data);

            return $experience;
        });
    }
}
