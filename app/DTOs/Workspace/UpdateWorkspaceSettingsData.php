<?php

namespace App\DTOs\Workspace;

class UpdateWorkspaceSettingsData
{
    public function __construct(
        public string $name,
    ) {}
}
