import type { WorkspaceSummary } from '@/types';

export const CURRENT_WORKSPACE_STORAGE_KEY = 'current_workspace_uid';

export function getStoredCurrentWorkspaceUid(): string | null {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem(CURRENT_WORKSPACE_STORAGE_KEY);
}

export function setStoredCurrentWorkspaceUid(workspaceUid: string): void {
    if (typeof window === 'undefined') {
        return;
    }

    localStorage.setItem(CURRENT_WORKSPACE_STORAGE_KEY, workspaceUid);
}

export function initializeCurrentWorkspace(
    workspaces: WorkspaceSummary[],
    currentWorkspace: WorkspaceSummary | null,
): string | null {
    const workspaceUids = new Set(workspaces.map((workspace) => workspace.uid));
    const storedWorkspaceUid = getStoredCurrentWorkspaceUid();

    if (storedWorkspaceUid && workspaceUids.has(storedWorkspaceUid)) {
        return storedWorkspaceUid;
    }

    const fallbackWorkspaceUid =
        currentWorkspace?.uid ?? workspaces[0]?.uid ?? null;

    if (fallbackWorkspaceUid) {
        setStoredCurrentWorkspaceUid(fallbackWorkspaceUid);
    }

    return fallbackWorkspaceUid;
}
