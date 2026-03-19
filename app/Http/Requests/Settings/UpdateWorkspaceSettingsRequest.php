<?php

namespace App\Http\Requests\Settings;

use App\Enums\WorkspaceMemberRole;
use App\Http\Middleware\ResolveCurrentWorkspace;
use App\Models\WorkspaceMember;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkspaceSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $workspaceMember = $this->attributes->get(ResolveCurrentWorkspace::CURRENT_WORKSPACE_MEMBER_ATTRIBUTE);

        return $workspaceMember instanceof WorkspaceMember
            && $workspaceMember->role === WorkspaceMemberRole::Owner;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
