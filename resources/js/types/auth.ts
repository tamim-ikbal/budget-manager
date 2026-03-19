export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type WorkspaceRole = 'owner' | 'member';

export type WorkspaceSummary = {
    uid: string;
    name: string;
    role: WorkspaceRole;
};

export type Auth = {
    user: User;
    workspaces: WorkspaceSummary[];
    currentWorkspace: WorkspaceSummary | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
