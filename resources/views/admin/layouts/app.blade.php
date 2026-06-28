<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name', 'Laravel') . ' Dashboard')</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @php
            $hasViteAssets = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'));
        @endphp

        @if ($hasViteAssets)
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <script>
                tailwind.config = {
                    theme: {
                        extend: {
                            fontFamily: {
                                sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                            },
                        },
                    },
                };
            </script>

            <style>
                :root {
                    --dashboard-bg: #f4f7fb;
                    --dashboard-panel: #ffffff;
                    --dashboard-border: #dbe5f0;
                    --dashboard-primary: #173f87;
                    --dashboard-primary-dark: #12356f;
                }

                body {
                    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                }

                .dashboard-panel {
                    background: var(--dashboard-panel);
                    border: 1px solid var(--dashboard-border);
                    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
                }

                .submenu {
                    max-height: 0;
                    transition: max-height 0.3s ease, opacity 0.25s ease, transform 0.25s ease;
                    opacity: 0;
                    transform: translateY(-8px);
                }

                .submenu:not(.is-ready) {
                    transition: none;
                }

                .submenu.is-open {
                    opacity: 1;
                    transform: translateY(0);
                }

                .sidebar-trigger[aria-expanded='true'] .submenu-arrow {
                    transform: rotate(180deg);
                }

                .sidebar-trigger.is-active,
                .sidebar-trigger[aria-expanded='true'] {
                    background: rgba(255, 255, 255, 0.08);
                    color: #ffffff;
                }

                .admin-page-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 12px;
                    margin-bottom: 16px;
                }

                .admin-page-title {
                    margin: 0;
                    font-size: 16px;
                    font-weight: 600;
                    color: #1f2937;
                }

                .admin-page-copy {
                    margin-top: 4px;
                    font-size: 14px;
                    color: #6b7280;
                }

                .admin-chip {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 9999px;
                    padding: 6px 12px;
                    font-size: 12px;
                    font-weight: 600;
                    background: #f3f4f6;
                    color: #475569;
                }

                .admin-btn {
                    min-height: 42px;
                    padding: 10px 16px;
                    border-radius: 14px;
                    text-decoration: none;
                    font-size: 14px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    border: 1px solid transparent;
                    cursor: pointer;
                }

                .admin-btn-primary {
                    background: #4a91e2;
                    border-color: #4a91e2;
                    color: #fff;
                }

                .admin-btn-primary:hover {
                    background: #3d83d4;
                    border-color: #3d83d4;
                }

                .admin-btn-secondary {
                    background: #fff;
                    color: #4b5563;
                    border-color: #d1d5db;
                }

                .admin-btn-secondary:hover {
                    background: #f8fafc;
                }

                .admin-form-card,
                .admin-filter-card {
                    background: #fff;
                    border: 1px solid #e5e7eb;
                    border-radius: 0;
                    box-shadow: none;
                }

                .admin-form-card {
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
                }

                .admin-index-panel {
                    background: #fff;
                    border: 1px solid #e5e7eb;
                    border-radius: 0;
                    box-shadow: none;
                }

                .admin-filter-grid {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 16px;
                }

                .admin-filter-field-wide {
                    grid-column: span 2;
                }

                .admin-filter-actions {
                    display: flex;
                    align-items: flex-end;
                    gap: 12px;
                }

                .admin-field label {
                    display: block;
                    margin-bottom: 8px;
                    font-size: 14px;
                    font-weight: 600;
                    color: #1f2937;
                }

                .admin-input,
                .admin-select,
                .admin-textarea {
                    width: 100%;
                    border: 1px solid #d1d5db;
                    background: #fff;
                    color: #374151;
                    border-radius: 4px;
                    min-height: 44px;
                    padding: 10px 14px;
                    font-size: 14px;
                }

                .admin-textarea {
                    min-height: 132px;
                    resize: vertical;
                }

                .admin-input-group {
                    display: grid;
                    grid-template-columns: minmax(0, 1fr) 64px;
                }

                .admin-input-group > .admin-input,
                .admin-input-group > .admin-select {
                    border-radius: 4px 0 0 4px;
                    border-right: none;
                }

                .admin-input-addon {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    border: 1px solid #d1d5db;
                    border-left: none;
                    background: #f8fafc;
                    color: #4b5563;
                    font-size: 13px;
                    min-height: 44px;
                    border-radius: 0 4px 4px 0;
                }

                .admin-preview-box {
                    width: 112px;
                    height: 112px;
                    border: 1px solid #d1d5db;
                    background: #f8fafc;
                    border-radius: 8px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    overflow: hidden;
                    color: #64748b;
                    font-size: 12px;
                }

                .admin-preview-box img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }

                .admin-file-meta {
                    margin-top: 8px;
                    font-size: 12px;
                    color: #64748b;
                }

                .admin-checkbox {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-size: 14px;
                    font-weight: 600;
                    color: #1f2937;
                }

                .admin-form-grid {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 18px 22px;
                }

                .admin-form-actions {
                    display: flex;
                    gap: 12px;
                    margin-top: 20px;
                }

                .admin-section-title {
                    margin: 0;
                    font-size: 24px;
                    line-height: 1.2;
                    font-weight: 700;
                    color: #1e293b;
                }

                .admin-section-copy {
                    margin-top: 10px;
                    color: #64748b;
                    font-size: 14px;
                }

                .admin-divider {
                    border-top: 1px solid #e5e7eb;
                    margin: 28px 0;
                }

                .admin-table-wrap {
                    overflow-x: auto;
                    border-radius: 0;
                    border: none;
                }

                .admin-table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .admin-table th {
                    background: #4a90e2;
                    color: #fff;
                    text-align: left;
                    padding: 16px;
                    font-size: 14px;
                    font-weight: 600;
                    white-space: nowrap;
                }

                .admin-table td {
                    padding: 16px;
                    border-top: 1px solid #eee;
                    font-size: 14px;
                    color: #333;
                    vertical-align: top;
                    background: #fff;
                }

                .admin-table tr:hover td {
                    background: #fafcff;
                }

                .admin-empty {
                    text-align: center;
                    color: #64748b;
                    padding: 36px 16px;
                }

                .admin-thumb,
                .admin-thumb-empty {
                    width: 52px;
                    height: 52px;
                    border-radius: 6px;
                    border: 1px solid #e5e7eb;
                }

                .admin-thumb {
                    object-fit: cover;
                }

                .admin-thumb-empty {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #f8fafc;
                    color: #64748b;
                    font-size: 11px;
                }

                .admin-status-badge {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    padding: 6px 12px;
                    border-radius: 999px;
                    font-size: 12px;
                    font-weight: 600;
                    text-transform: capitalize;
                    white-space: nowrap;
                }

                .admin-status-badge-active,
                .admin-status-badge-success,
                .admin-status-badge-paid,
                .admin-status-badge-approved,
                .admin-status-badge-published,
                .admin-status-badge-confirmed {
                    background: #e7f7ee;
                    color: #1f9254;
                }

                .admin-status-badge-pending,
                .admin-status-badge-initiated,
                .admin-status-badge-draft,
                .admin-status-badge-unpaid {
                    background: #fff4dd;
                    color: #d48a00;
                }

                .admin-status-badge-inactive,
                .admin-status-badge-failed,
                .admin-status-badge-cancelled,
                .admin-status-badge-rejected,
                .admin-status-badge-archived,
                .admin-status-badge-banned,
                .admin-status-badge-expired {
                    background: #fdeaea;
                    color: #d64545;
                }

                .admin-action-list {
                    position: relative;
                    display: inline-block;
                }

                .admin-action-trigger {
                    width: 34px;
                    min-width: 34px;
                    height: 34px;
                    padding: 0;
                    border: 1px solid #d5dbe3;
                    border-radius: 6px;
                    background: #fff;
                    color: #334155;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 13px;
                    cursor: pointer;
                    list-style: none;
                }

                .admin-action-trigger::-webkit-details-marker {
                    display: none;
                }

                .admin-action-list[open] .admin-action-trigger {
                    border-color: #4a90e2;
                    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.12);
                }

                .admin-action-menu {
                    position: absolute;
                    right: 0;
                    top: calc(100% + 8px);
                    min-width: 170px;
                    background: #fff;
                    border: 1px solid #d5dbe3;
                    border-radius: 8px;
                    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
                    padding: 8px;
                    z-index: 20;
                }

                .admin-action-link {
                    width: 100%;
                    padding: 9px 11px;
                    font-size: 13px;
                    border-radius: 6px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    text-decoration: none;
                    background: transparent;
                    color: #334155;
                }

                .admin-action-link:hover {
                    background: #f8fafc;
                }

                .admin-inline-actions {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    flex-wrap: wrap;
                }

                .admin-icon-action {
                    width: 40px;
                    height: 40px;
                    border-radius: 6px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    text-decoration: none;
                    border: 1px solid transparent;
                    color: #fff;
                    cursor: pointer;
                }

                .admin-icon-action svg {
                    width: 18px;
                    height: 18px;
                }

                .admin-icon-action-view {
                    background: #3b82f6;
                }

                .admin-icon-action-edit {
                    background: #4a91e2;
                }

                .admin-icon-action-open {
                    background: #06b6d4;
                }

                .admin-icon-action-delete {
                    background: #ef4444;
                }

                .admin-detail-grid {
                    display: grid;
                    grid-template-columns: 340px minmax(0, 1fr);
                    gap: 20px;
                }

                .admin-meta-table {
                    width: 100%;
                    border-collapse: collapse;
                }

                .admin-meta-table th,
                .admin-meta-table td {
                    border: 1px solid #e5e7eb;
                    padding: 12px 14px;
                    font-size: 14px;
                    color: #334155;
                    vertical-align: top;
                }

                .admin-meta-table th {
                    width: 220px;
                    background: #f8fafc;
                    font-weight: 600;
                }

                .sidebar-trigger.is-active,
                .sidebar-trigger[aria-expanded='true'] {
                    background: #f4f8fd;
                    color: var(--dashboard-primary);
                }

                .admin-page-header {
                    padding-bottom: 12px;
                    border-bottom: 1px solid #e6edf6;
                }

                .admin-page-title {
                    font-size: 15px;
                    color: #1d355d;
                }

                .admin-page-copy {
                    font-size: 12px;
                    color: #7a8ca8;
                }

                .admin-chip {
                    padding: 5px 11px;
                    font-size: 11px;
                    background: #eef3fa;
                    color: #5d708d;
                }

                .admin-btn {
                    min-height: 40px;
                    padding: 9px 16px;
                    border-radius: 10px;
                    font-size: 13px;
                    font-weight: 600;
                    white-space: nowrap;
                }

                .admin-btn-primary {
                    background: var(--dashboard-primary);
                    border-color: var(--dashboard-primary);
                }

                .admin-btn-primary:hover {
                    background: var(--dashboard-primary-dark);
                    border-color: var(--dashboard-primary-dark);
                }

                .admin-btn-secondary {
                    color: #52657f;
                    border-color: #d6e0eb;
                }

                .admin-btn-secondary:hover {
                    background: #f6f9fd;
                }

                .admin-form-card {
                    border: 1px solid #dbe5f0;
                    border-radius: 16px;
                    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
                }

                .admin-filter-card,
                .admin-index-panel {
                    border: 1px solid #dbe5f0;
                    border-radius: 18px;
                    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
                }

                .admin-filter-grid {
                    gap: 14px;
                }

                .admin-field label {
                    margin-bottom: 6px;
                    font-size: 12px;
                    color: #33496b;
                }

                .admin-input,
                .admin-select,
                .admin-textarea {
                    border: 1px solid #d6e0eb;
                    border-radius: 10px;
                    min-height: 42px;
                    padding: 9px 13px;
                    font-size: 13px;
                }

                .admin-input-group {
                    grid-template-columns: minmax(0, 1fr) 54px;
                }

                .admin-input-group > .admin-input,
                .admin-input-group > .admin-select {
                    border-radius: 10px 0 0 10px;
                }

                .admin-input-addon {
                    border: 1px solid #d6e0eb;
                    background: #fbfdff;
                    color: #5e6f89;
                    font-size: 11px;
                    min-height: 42px;
                    border-radius: 0 10px 10px 0;
                    font-weight: 700;
                }

                .admin-form-grid {
                    gap: 16px 20px;
                }

                .admin-form-actions {
                    margin-top: 18px;
                }

                .admin-section-title {
                    font-size: 22px;
                }

                .admin-section-copy {
                    margin-top: 8px;
                    color: #7385a2;
                    font-size: 13px;
                }

                .admin-divider {
                    margin: 24px 0;
                }

                .admin-index-panel-table {
                    padding: 0;
                    overflow: hidden;
                }

                .admin-index-panel-table > .admin-page-header {
                    margin-bottom: 0;
                    padding: 22px 24px 16px;
                }

                .admin-index-panel-table > .admin-table-wrap {
                    margin-top: 0;
                }

                .admin-index-panel-table > .mt-5 {
                    margin-top: 0;
                    padding: 20px 24px 22px;
                }

                .admin-table th {
                    background: #4b8fe2;
                    padding: 16px 24px;
                    font-size: 12px;
                }

                .admin-table td {
                    padding: 24px;
                    border-top: 1px solid #edf2f7;
                    font-size: 13px;
                    color: #2e3b52;
                    vertical-align: middle;
                }

                .admin-empty {
                    padding: 36px 24px;
                }

                .admin-stat-card {
                    position: relative;
                    display: block;
                    text-decoration: none;
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                }

                .admin-stat-card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
                }

                .admin-dashboard-section {
                    overflow: hidden;
                    border-radius: 18px;
                    border: 1px solid #dbe5f0;
                    background: #fff;
                    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
                }

                .admin-dashboard-section-head {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 14px;
                    padding: 22px 24px 18px;
                    border-bottom: 1px solid #edf2f7;
                }

                .admin-dashboard-section-title {
                    margin: 0;
                    font-size: 15px;
                    font-weight: 700;
                    color: #1d355d;
                }

                .admin-dashboard-section-copy {
                    margin-top: 4px;
                    font-size: 12px;
                    color: #7385a2;
                }

                .admin-dashboard-list {
                    display: flex;
                    flex-direction: column;
                }

                .admin-dashboard-row {
                    display: grid;
                    grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr) auto auto;
                    gap: 18px;
                    align-items: center;
                    padding: 22px 24px;
                    border-top: 1px solid #edf2f7;
                    text-decoration: none;
                    background: #fff;
                    transition: background 0.2s ease;
                }

                .admin-dashboard-row:first-child {
                    border-top: none;
                }

                .admin-dashboard-row:hover {
                    background: #fbfdff;
                }

                .admin-dashboard-key {
                    font-size: 14px;
                    font-weight: 700;
                    color: #17233b;
                }

                .admin-dashboard-subkey {
                    margin-top: 6px;
                    font-size: 11px;
                    color: #6d7f9d;
                    line-height: 1.5;
                }

                .admin-dashboard-meta {
                    font-size: 14px;
                    font-weight: 700;
                    color: #17233b;
                }

                .admin-dashboard-meta-copy {
                    margin-top: 6px;
                    font-size: 11px;
                    color: #6d7f9d;
                    line-height: 1.5;
                }

                .admin-dashboard-value {
                    font-size: 16px;
                    font-weight: 700;
                    color: #17233b;
                    text-align: right;
                    white-space: nowrap;
                }

                .admin-dashboard-linkbtn {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 138px;
                    min-height: 52px;
                    padding: 0 20px;
                    border-radius: 16px;
                    background: #edf4ff;
                    color: #173f87;
                    font-size: 13px;
                    font-weight: 700;
                    text-decoration: none;
                    white-space: nowrap;
                }

                .admin-dashboard-chip {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 120px;
                    min-height: 52px;
                    padding: 0 18px;
                    border-radius: 999px;
                    font-size: 13px;
                    font-weight: 700;
                    white-space: nowrap;
                }

                .admin-dashboard-chip-warning {
                    background: #fff4dd;
                    color: #cf5a00;
                }

                .admin-dashboard-chip-success {
                    background: #dff9fb;
                    color: #127c7f;
                }

                .admin-thumb,
                .admin-thumb-empty {
                    width: 58px;
                    height: 58px;
                    border-radius: 10px;
                    border: 1px solid #dbe5f0;
                }

                .admin-status-badge {
                    padding: 6px 14px;
                    font-size: 11px;
                }

                .admin-action-trigger {
                    width: 40px;
                    min-width: 40px;
                    height: 40px;
                    border: 1px solid #dbe5f0;
                    border-radius: 12px;
                    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
                }

                .admin-action-trigger svg {
                    width: 18px;
                    height: 18px;
                }

                .admin-action-list[open] .admin-action-trigger {
                    border-color: #c8d8ec;
                    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
                }

                .admin-action-menu {
                    border: 1px solid #dbe5f0;
                    border-radius: 16px;
                    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.1);
                    padding: 8px;
                    min-width: 158px;
                }

                .admin-action-link {
                    padding: 9px 11px;
                    border-radius: 10px;
                    font-size: 12px;
                }

                .admin-action-link svg {
                    width: 18px;
                    height: 18px;
                    flex: 0 0 auto;
                }

                .admin-action-link:hover {
                    background: #f5f8fd;
                }

                .admin-action-link-danger {
                    color: #ef4444;
                }

                .admin-inline-actions {
                    gap: 8px;
                }

                .admin-icon-action {
                    width: 42px;
                    height: 42px;
                    border-radius: 12px;
                    border: 1px solid #dbe5f0;
                    color: #334155;
                    background: #fff;
                    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
                }

                .admin-icon-action-view {
                    color: #334155;
                }

                .admin-icon-action-edit {
                    color: #1a7ba7;
                }

                .admin-icon-action-open {
                    color: #3459d1;
                }

                .admin-icon-action-delete {
                    color: #ef4444;
                }

                .admin-pagination-wrap {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 16px;
                    padding-top: 20px;
                    border-top: 1px solid #edf2f7;
                    flex-wrap: wrap;
                }

                .admin-pagination-meta {
                    font-size: 13px;
                    font-weight: 600;
                    color: #44546f;
                }

                .admin-pagination {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    flex-wrap: wrap;
                }

                .admin-page-btn {
                    min-width: 38px;
                    height: 38px;
                    padding: 0 11px;
                    border-radius: 12px;
                    background: #f4f7fc;
                    color: #2f3b52;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    text-decoration: none;
                    font-size: 12px;
                    font-weight: 700;
                    border: 1px solid transparent;
                }

                .admin-page-btn svg {
                    width: 15px;
                    height: 15px;
                }

                .admin-page-btn.is-active {
                    background: #4950d4;
                    color: #fff;
                    box-shadow: 0 10px 18px rgba(73, 80, 212, 0.22);
                }

                .admin-page-btn.is-muted,
                .admin-page-btn.is-disabled {
                    color: #9aa7ba;
                }

                @media (max-width: 900px) {
                    .admin-detail-grid {
                        grid-template-columns: 1fr;
                    }
                }

                @media (max-width: 768px) {
                    .admin-page-header {
                        flex-direction: column;
                        align-items: flex-start;
                    }

                    .admin-filter-grid,
                    .admin-form-grid {
                        grid-template-columns: 1fr;
                    }

                    .admin-filter-field-wide {
                        grid-column: auto;
                    }

                    .admin-filter-actions {
                        align-items: stretch;
                        flex-direction: column;
                    }

                    .admin-dashboard-row {
                        grid-template-columns: 1fr;
                        gap: 12px;
                    }

                    .admin-dashboard-value {
                        text-align: left;
                    }

                    .admin-dashboard-linkbtn,
                    .admin-dashboard-chip {
                        min-height: 46px;
                        width: 100%;
                    }
                }

                @media (min-width: 1024px) {
                    .dashboard-sidebar {
                        height: 100vh;
                    }
                }
            </style>
        @endif
    </head>
    <body class="dashboard-shell min-h-screen bg-[var(--dashboard-bg)] text-slate-900 antialiased">
        <div class="relative min-h-screen overflow-x-hidden">
            <div class="relative flex min-h-screen">
                @include('admin.components.sidebar')

                <div class="flex min-h-screen min-w-0 flex-1 flex-col lg:pl-[234px]">
                    @include('admin.components.header')

                    <main class="flex-1 space-y-4 px-4 pb-8 pt-[4rem] sm:px-6 lg:px-8">
                        @if (session('success'))
                            <div class="dashboard-panel rounded-[16px] border border-emerald-200 bg-emerald-50 px-4 py-3 text-[0.82rem] text-emerald-700">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="dashboard-panel rounded-[16px] border border-rose-200 bg-rose-50 px-4 py-3 text-[0.82rem] text-rose-700">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="dashboard-panel rounded-[16px] border border-amber-200 bg-amber-50 px-4 py-3 text-[0.82rem] text-amber-800">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        @yield('content')
                    </main>
                </div>
            </div>
        </div>

        @unless ($hasViteAssets)
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const sidebar = document.querySelector('[data-sidebar]');
                    const backdrop = document.querySelector('[data-sidebar-backdrop]');
                    const openButton = document.querySelector('[data-sidebar-toggle]');
                    const closeButton = document.querySelector('[data-sidebar-close]');
                    const submenuButtons = document.querySelectorAll('[data-submenu-toggle]');

                    const setSidebarState = (open) => {
                        if (!sidebar || !backdrop) {
                            return;
                        }

                        sidebar.classList.toggle('translate-x-0', open);
                        sidebar.classList.toggle('-translate-x-full', !open);
                        backdrop.classList.toggle('hidden', !open);
                        document.body.classList.toggle('overflow-hidden', open && window.innerWidth < 1024);
                    };

                    openButton?.addEventListener('click', () => setSidebarState(true));
                    closeButton?.addEventListener('click', () => setSidebarState(false));
                    backdrop?.addEventListener('click', () => setSidebarState(false));

                    submenuButtons.forEach((button) => {
                        const submenu = button.parentElement?.querySelector('[data-submenu]');

                        if (!submenu) {
                            return;
                        }

                        const isOpen = button.getAttribute('aria-expanded') === 'true';
                        button.classList.toggle('is-active', isOpen);
                        submenu.classList.toggle('is-open', isOpen);
                        submenu.style.maxHeight = isOpen ? `${submenu.scrollHeight}px` : '0px';

                        button.addEventListener('click', () => {
                            const isCurrentlyOpen = button.getAttribute('aria-expanded') === 'true';

                            submenuButtons.forEach((otherButton) => {
                                if (otherButton === button) {
                                    return;
                                }

                                const otherSubmenu = otherButton.parentElement?.querySelector('[data-submenu]');

                                if (!otherSubmenu) {
                                    return;
                                }

                                otherButton.setAttribute('aria-expanded', 'false');
                                otherButton.classList.remove('is-active');
                                otherSubmenu.classList.remove('is-open');
                                otherSubmenu.style.maxHeight = '0px';
                            });

                            button.setAttribute('aria-expanded', String(!isCurrentlyOpen));
                            button.classList.toggle('is-active', !isCurrentlyOpen);
                            submenu.classList.toggle('is-open', !isCurrentlyOpen);
                            submenu.style.maxHeight = isCurrentlyOpen ? '0px' : `${submenu.scrollHeight}px`;
                        });
                    });

                    requestAnimationFrame(() => {
                        submenuButtons.forEach((button) => {
                            const submenu = button.parentElement?.querySelector('[data-submenu]');

                            submenu?.classList.add('is-ready');
                        });
                    });

                    const actionMenus = document.querySelectorAll('.admin-action-list');

                    document.addEventListener('click', (event) => {
                        actionMenus.forEach((menu) => {
                            if (!menu.contains(event.target)) {
                                menu.removeAttribute('open');
                            }
                        });
                    });

                    const fileInputs = document.querySelectorAll('[data-file-input]');

                    fileInputs.forEach((input) => {
                        const targetName = input.getAttribute('data-file-name');
                        const targetPreview = input.getAttribute('data-file-preview');
                        const filenameNode = targetName ? document.querySelector(targetName) : null;
                        const previewNode = targetPreview ? document.querySelector(targetPreview) : null;
                        const previewImage = previewNode?.querySelector('img');
                        const previewText = previewNode?.querySelector('[data-preview-text]');

                        input.addEventListener('change', () => {
                            const [file] = input.files ?? [];

                            if (filenameNode) {
                                filenameNode.textContent = file ? file.name : 'No file selected';
                            }

                            if (!previewNode) {
                                return;
                            }

                            if (!file || !file.type.startsWith('image/')) {
                                if (previewImage) {
                                    previewImage.removeAttribute('src');
                                    previewImage.classList.add('hidden');
                                }

                                previewText?.classList.remove('hidden');
                                return;
                            }

                            const reader = new FileReader();
                            reader.onload = ({ target }) => {
                                if (previewImage) {
                                    previewImage.src = String(target?.result ?? '');
                                    previewImage.classList.remove('hidden');
                                }

                                previewText?.classList.add('hidden');
                            };

                            reader.readAsDataURL(file);
                        });
                    });

                    const socialIconSelects = document.querySelectorAll('[data-social-icon-select]');

                    socialIconSelects.forEach((select) => {
                        const preview = document.querySelector('[data-social-icon-preview]');

                        if (!preview) {
                            return;
                        }

                        const updatePreview = () => {
                            preview.innerHTML = `<i class="${select.value}"></i>`;
                        };

                        select.addEventListener('change', updatePreview);
                        updatePreview();
                    });
                });
            </script>
        @endunless
    </body>
</html>
