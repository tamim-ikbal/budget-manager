<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Workspace\UpdateWorkspaceSettingsAction;
use App\DTOs\Workspace\UpdateWorkspaceSettingsData;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveCurrentWorkspace;
use App\Http\Requests\Settings\UpdateWorkspaceSettingsRequest;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceSettingsController extends Controller
{
    public function edit(Request $request): Response
    {
        $workspace = $request->attributes->get(ResolveCurrentWorkspace::CURRENT_WORKSPACE_ATTRIBUTE);
        $workspaceMember = $request->attributes->get(ResolveCurrentWorkspace::CURRENT_WORKSPACE_MEMBER_ATTRIBUTE);

        if (! $workspace instanceof Workspace || ! $workspaceMember instanceof WorkspaceMember) {
            abort(403);
        }

        return Inertia::render('settings/Workspace', [
            'workspace' => [
                'uid' => $workspace->uid,
                'name' => $workspace->name,
                'role' => $workspaceMember->role->value,
            ],
            'status' => $request->session()->get('status'),
        ]);
    }

    public function update(
        UpdateWorkspaceSettingsRequest $request,
        UpdateWorkspaceSettingsAction $updateWorkspaceSettings,
    ): RedirectResponse {
        $workspace = $request->attributes->get(ResolveCurrentWorkspace::CURRENT_WORKSPACE_ATTRIBUTE);

        if (! $workspace instanceof Workspace) {
            abort(403);
        }

        $validated = $request->validated();

        $updateWorkspaceSettings($workspace, new UpdateWorkspaceSettingsData(
            name: $validated['name'],
        ));

        return to_route('workspace-settings.edit')->with('status', 'Workspace name updated successfully.');
    }
}
