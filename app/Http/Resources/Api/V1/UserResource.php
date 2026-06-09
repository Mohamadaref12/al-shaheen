<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\Profiles\AdminProfileResource;
use App\Http\Resources\Api\V1\Profiles\ContributorProfileResource;
use App\Http\Resources\Api\V1\Profiles\EditorProfileResource;
use App\Http\Resources\Api\V1\Profiles\ReaderProfileResource;
use App\Http\Resources\Api\V1\Profiles\WriterProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static function authRelations(): array
    {
        return [
            'reader',
            'contributor.categories:id,name,slug',
            'writer.categories:id,name,slug',
            'editor',
            'admin',
        ];
    }

    public function toArray(Request $request): array
    {
        $role = $this->resolveRole();

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'role'        => $role,
            'locale'      => $this->locale,
            'country'     => $this->country,
            'language'    => $this->language,
            'is_active'   => (bool) $this->is_active,
            'is_verified' => (bool) $this->is_verified,
            'profile'     => $this->profileResource($role),
        ];
    }

    private function resolveRole(): string
    {
        if ($this->writer) {
            return 'writer';
        }

        if ($this->contributor) {
            return 'contributor';
        }

        if ($this->editor) {
            return 'editor';
        }

        if ($this->admin) {
            return 'admin';
        }

        return 'reader';
    }

    private function profileResource(string $role): JsonResource|null
    {
        return match ($role) {
            'writer'      => $this->writer ? new WriterProfileResource($this->writer) : null,
            'contributor' => $this->contributor ? new ContributorProfileResource($this->contributor) : null,
            'reader'      => $this->reader ? new ReaderProfileResource($this->reader) : null,
            'editor'      => $this->editor ? new EditorProfileResource($this->editor) : null,
            'admin'       => $this->admin ? new AdminProfileResource($this->admin) : null,
            default       => null,
        };
    }

    public static function makeLoaded(User $user): array
    {
        $user->loadMissing(self::authRelations());

        return (new self($user))->resolve();
    }
}
