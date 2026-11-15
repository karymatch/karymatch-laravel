<?php

namespace App\Policies;

use App\Models\JobPost;
use App\Models\User;

class JobPostPolicy
{
    private function owns(User $user, JobPost $post): bool
    {
        return $user->role === 'perusahaan'
            && $user->company
            && $post->company_id === $user->company->id;
    }

    /** Edit hanya untuk iklan milik sendiri yang masa tayangnya belum habis. */
    public function update(User $user, JobPost $post): bool
    {
        return $this->owns($user, $post) && ! $post->isExpired();
    }

    /** Up ulang dan duplikat boleh untuk iklan milik sendiri, termasuk yang sudah berakhir. */
    public function renew(User $user, JobPost $post): bool
    {
        return $this->owns($user, $post);
    }

    public function duplicate(User $user, JobPost $post): bool
    {
        return $this->owns($user, $post);
    }
}
