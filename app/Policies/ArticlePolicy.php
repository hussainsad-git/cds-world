<?php

namespace App\Policies;

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\User;

/** Shield permission keys (Action:Model). Writers are limited to their own drafts/rejected articles. */
class ArticlePolicy
{
    private function manages(User $user, Article $article): bool
    {
        return $user->can('manage_all_articles') || $article->author_id === $user->id;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Article');
    }

    public function view(User $user, Article $article): bool
    {
        return $user->can('View:Article') && $this->manages($user, $article);
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Article');
    }

    public function update(User $user, Article $article): bool
    {
        if (! $user->can('Update:Article') || ! $this->manages($user, $article)) {
            return false;
        }
        if ($user->can('manage_all_articles')) {
            return true;
        }

        // Writers: only while the article is still theirs to edit.
        return in_array($article->status, [ContentStatus::Draft, ContentStatus::Rejected], true);
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->can('Delete:Article') && $this->manages($user, $article);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('DeleteAny:Article');
    }

    public function restore(User $user, Article $article): bool
    {
        return $user->can('Restore:Article');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('RestoreAny:Article');
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return $user->can('ForceDelete:Article');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('ForceDeleteAny:Article');
    }

    public function replicate(User $user, Article $article): bool
    {
        return $user->can('Replicate:Article');
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder:Article');
    }
}
