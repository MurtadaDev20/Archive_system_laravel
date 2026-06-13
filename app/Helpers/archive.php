<?php

if (! function_exists('archive_status_label')) {
    function archive_status_label(int $statusId): string
    {
        static $cache = [];
        if (! isset($cache[$statusId])) {
            $status = \App\Models\Status::find($statusId);
            $cache[$statusId] = $status?->label() ?? '';
        }

        return $cache[$statusId];
    }
}

if (! function_exists('archive_role_label')) {
    function archive_role_label(string $roleName): string
    {
        return match ($roleName) {
            'Super Admin' => __('archive.role_super_admin'),
            'Admin' => __('archive.role_admin'),
            'Department Manager', 'Manager' => __('archive.role_manager'),
            'Employee' => __('archive.role_employee'),
            'Viewer', 'Editor' => __('archive.role_viewer'),
            default => $roleName,
        };
    }
}

if (! function_exists('archive_transfer_status_label')) {
    function archive_transfer_status_label(string $status): string
    {
        return match ($status) {
            'sent' => __('archive.transfer_status_sent'),
            'received' => __('archive.transfer_status_received'),
            'accepted' => __('archive.transfer_status_accepted'),
            'rejected' => __('archive.transfer_status_rejected'),
            default => $status,
        };
    }
}

if (! function_exists('archive_audit_description')) {
    function archive_audit_description(object $log): string
    {
        $key = 'archive.audit_'.str_replace('.', '_', $log->action);
        $params = is_array($log->metadata ?? null) ? $log->metadata : [];

        $translated = __($key, $params);

        return $translated !== $key ? $translated : (string) $log->description;
    }
}
