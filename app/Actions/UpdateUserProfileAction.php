<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Arr;

class UpdateUserProfileAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): User
    {
        $user->loadMissing(['writer', 'contributor', 'reader']);

        $userData = Arr::only($data, ['name', 'email', 'country', 'language', 'locale']);

        if (isset($userData['email'])) {
            $userData['email'] = strtolower((string) $userData['email']);
        }

        if ($userData !== []) {
            $user->fill($userData)->save();
        }

        if ($user->writer) {
            $this->updateWriterProfile($user, $data);
        } elseif ($user->contributor) {
            $this->updateContributorProfile($user, $data);
        }

        return $user->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function updateWriterProfile(User $user, array $data): void
    {
        $writerData = Arr::only($data, [
            'display_name',
            'bio',
            'profile_photo',
            'portfolio_link',
            'experience_level',
            'languages',
            'editorial_specialties',
            'location',
            'social_links',
            'media_affiliation',
        ]);

        if ($writerData !== []) {
            $user->writer->fill($writerData)->save();
        }

        if (array_key_exists('categories', $data)) {
            $user->writer->categories()->sync($data['categories']);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function updateContributorProfile(User $user, array $data): void
    {
        $contributorData = Arr::only($data, [
            'bio',
            'profile_photo',
            'portfolio_link',
        ]);

        if ($contributorData !== []) {
            $user->contributor->fill($contributorData)->save();
        }

        if (array_key_exists('categories', $data)) {
            $user->contributor->categories()->sync($data['categories']);
        }
    }
}
