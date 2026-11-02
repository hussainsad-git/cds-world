<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Role → permission matrix. Idempotent: creates any missing permission, then syncs each role.
 * Permission names follow Filament Shield ("Action:Model") plus the custom keys in RolesSeeder.
 * super_admin needs no entries (Shield grants it everything through a Gate::before).
 * The Super Administrator can change any of this later in Roles & Permissions.
 */
class RolePermissionSeeder extends Seeder
{
    private const ACTIONS = ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny', 'Restore', 'RestoreAny', 'ForceDelete', 'ForceDeleteAny', 'Replicate', 'Reorder'];

    private const READ = ['ViewAny', 'View'];

    private const EDIT = ['ViewAny', 'View', 'Create', 'Update'];

    private function crud(string $model, array $actions = self::ACTIONS): array
    {
        return array_map(fn ($a) => "$a:$model", $actions);
    }

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $content = ['Page', 'Category', 'Event', 'MediaCoverage', 'MediaOutlet', 'Video', 'GalleryAlbum', 'TeamMember', 'NavigationMenu', 'Popup', 'Redirect'];
        $engagement = ['NewsletterSubscriber', 'NewsletterCampaign', 'ContactMessage'];

        $matrix = [
            'administrator' => array_merge(
                $this->crud('Article'),
                ...array_map(fn ($m) => $this->crud($m), array_merge($content, $engagement)),
                $this->crud('User', ['ViewAny', 'View', 'Create', 'Update']),
                ['manage_all_articles', 'publish_article', 'approve_article', 'feature_article', 'schedule_article', 'manage_seo', 'manage_redirects', 'manage_theme', 'manage_settings', 'view_activity_log'],
            ),
            'editor' => array_merge(
                $this->crud('Article', ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'Restore']),
                $this->crud('Category', self::EDIT),
                $this->crud('Page', self::READ),
                $this->crud('Event', self::READ),
                ['manage_all_articles', 'publish_article', 'approve_article', 'feature_article', 'schedule_article'],
            ),
            'writer' => array_merge(
                $this->crud('Article', ['ViewAny', 'View', 'Create', 'Update', 'Delete']),
                $this->crud('Category', self::READ),
            ),
            'seo_manager' => array_merge(
                $this->crud('Article', self::READ), // viewing only; SEO edits happen from the SEO dashboard links
                array_merge(...array_map(fn ($m) => $this->crud($m, ['ViewAny', 'View', 'Update']), ['Page', 'Category', 'Event', 'MediaCoverage', 'Video', 'GalleryAlbum', 'TeamMember'])),
                $this->crud('Redirect'),
                ['manage_all_articles', 'manage_seo', 'manage_redirects'],
            ),
            'event_manager' => array_merge(
                $this->crud('Event', ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'Restore', 'Replicate']),
                $this->crud('Category', self::EDIT),
                $this->crud('TeamMember', self::READ),
            ),
            'media_manager' => array_merge(
                $this->crud('MediaCoverage'),
                $this->crud('MediaOutlet'),
                $this->crud('Video'),
                $this->crud('GalleryAlbum'),
                $this->crud('Category', self::EDIT),
                $this->crud('Event', self::READ),
            ),
        ];

        foreach ($matrix as $roleName => $permissions) {
            $permissions = array_values(array_unique($permissions));
            foreach ($permissions as $name) {
                Permission::findOrCreate($name, 'web');
            }
            Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
