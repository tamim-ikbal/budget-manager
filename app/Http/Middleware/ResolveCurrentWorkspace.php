<?php

namespace App\Http\Middleware;

use App\Enums\WorkspaceMemberRole;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class ResolveCurrentWorkspace
{
    public const WORKSPACE_UID_HEADER = 'X-Workspace-Uid';

    public const CURRENT_WORKSPACE_ATTRIBUTE = 'currentWorkspace';

    public const CURRENT_WORKSPACE_MEMBER_ATTRIBUTE = 'currentWorkspaceMember';

    public const AVAILABLE_WORKSPACE_MEMBERS_ATTRIBUTE = 'availableWorkspaceMembers';

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $workspaceMemberships = WorkspaceMember::query()
            ->where('user_id', $user->id)
            ->whereIn('role', [WorkspaceMemberRole::Owner->value, WorkspaceMemberRole::Member->value])
            ->whereHas('workspace')
            ->with('workspace:id,uid,name')
            ->orderBy('id')
            ->get();

        $currentWorkspaceMembership = $this->resolveCurrentWorkspaceMembership(
            $workspaceMemberships,
            $request->header(self::WORKSPACE_UID_HEADER),
        );

        if (! $currentWorkspaceMembership instanceof WorkspaceMember) {
            abort(403);
        }

        $currentWorkspace = $currentWorkspaceMembership->workspace;

        if (! $currentWorkspace instanceof Workspace) {
            abort(403);
        }

        $request->attributes->set(self::AVAILABLE_WORKSPACE_MEMBERS_ATTRIBUTE, $workspaceMemberships);
        $request->attributes->set(self::CURRENT_WORKSPACE_MEMBER_ATTRIBUTE, $currentWorkspaceMembership);
        $request->attributes->set(self::CURRENT_WORKSPACE_ATTRIBUTE, $currentWorkspace);

        return $next($request);
    }

    /**
     * @param  Collection<int, WorkspaceMember>  $workspaceMemberships
     */
    protected function resolveCurrentWorkspaceMembership(
        Collection $workspaceMemberships,
        mixed $requestedWorkspaceUid,
    ): ?WorkspaceMember {
        if (is_string($requestedWorkspaceUid) && $requestedWorkspaceUid !== '') {
            $requestedMembership = $workspaceMemberships->first(
                fn (WorkspaceMember $workspaceMember): bool => $workspaceMember->workspace?->uid === $requestedWorkspaceUid,
            );

            if ($requestedMembership instanceof WorkspaceMember) {
                return $requestedMembership;
            }
        }

        return $workspaceMemberships->first();
    }
}
