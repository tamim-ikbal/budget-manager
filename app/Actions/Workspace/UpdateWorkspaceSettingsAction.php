<?php

namespace App\Actions\Workspace;

use App\DTOs\Workspace\UpdateWorkspaceSettingsData;
use App\Models\Workspace;

class UpdateWorkspaceSettingsAction
{
    public function __invoke(Workspace $workspace, UpdateWorkspaceSettingsData $data): Workspace
    {
        $workspace->update([
            'name' => $data->name,
        ]);

        return $workspace->refresh();
    }
}
