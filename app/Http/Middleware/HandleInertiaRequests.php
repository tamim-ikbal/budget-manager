<?php

namespace App\Http\Middleware;

use App\Enums\WorkspaceMemberRole;
use App\Models\WorkspaceMember;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $workspaceMemberships = $this->workspaceMemberships($request);
        $currentWorkspaceMembership = $this->resolveCurrentWorkspaceMembership($request, $workspaceMemberships);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'workspaces' => $workspaceMemberships
                    ->map(fn (WorkspaceMember $workspaceMember): array => [
                        'uid' => $workspaceMember->workspace->uid,
                        'name' => $workspaceMember->workspace->name,
                        'role' => $workspaceMember->role->value,
                    ])
                    ->values()
                    ->all(),
                'currentWorkspace' => $currentWorkspaceMembership instanceof WorkspaceMember
                    ? [
                        'uid' => $currentWorkspaceMembership->workspace->uid,
                        'name' => $currentWorkspaceMembership->workspace->name,
                        'role' => $currentWorkspaceMembership->role->value,
                    ]
                    : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return Collection<int, WorkspaceMember>
     */
    protected function workspaceMemberships(Request $request): Collection
    {
        $workspaceMemberships = $request->attributes->get(ResolveCurrentWorkspace::AVAILABLE_WORKSPACE_MEMBERS_ATTRIBUTE);

        if ($workspaceMemberships instanceof Collection) {
            return $workspaceMemberships;
        }

        $user = $request->user();

        if (! $user) {
            return collect();
        }

        return WorkspaceMember::query()
            ->where('user_id', $user->id)
            ->whereIn('role', [WorkspaceMemberRole::Owner->value, WorkspaceMemberRole::Member->value])
            ->whereHas('workspace')
            ->with('workspace:id,uid,name')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  Collection<int, WorkspaceMember>  $workspaceMemberships
     */
    protected function resolveCurrentWorkspaceMembership(
        Request $request,
        Collection $workspaceMemberships,
    ): ?WorkspaceMember {
        $attributeWorkspaceMember = $request->attributes->get(ResolveCurrentWorkspace::CURRENT_WORKSPACE_MEMBER_ATTRIBUTE);

        if ($attributeWorkspaceMember instanceof WorkspaceMember) {
            return $attributeWorkspaceMember;
        }

        $requestedWorkspaceUid = $request->header(ResolveCurrentWorkspace::WORKSPACE_UID_HEADER);

        if (is_string($requestedWorkspaceUid) && $requestedWorkspaceUid !== '') {
            $requestedWorkspaceMembership = $workspaceMemberships->first(
                fn (WorkspaceMember $workspaceMember): bool => $workspaceMember->workspace?->uid === $requestedWorkspaceUid,
            );

            if ($requestedWorkspaceMembership instanceof WorkspaceMember) {
                return $requestedWorkspaceMembership;
            }
        }

        return $workspaceMemberships->first();
    }
}
