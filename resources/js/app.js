
import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

import '../css/app.css';

window.Alpine = Alpine;

/*
|--------------------------------------------------------------------------
| Lucide Icons
|--------------------------------------------------------------------------
|
| Render Lucide icons used throughout the application.
|
*/
function initIcons() {
    createIcons({
        icons,
        attrs: {
            'stroke-width': 2,
        },
    });
}

/*
|--------------------------------------------------------------------------
| Sidebar
|--------------------------------------------------------------------------
|
| Handles:
| - Mobile sidebar drawer
| - Sidebar backdrop
| - Desktop sidebar collapse
| - Remembering collapsed state
| - Escape key
|
*/
function initSidebar() {
    const backdrop = document.getElementById('sidebar-backdrop');
    const mobileToggle = document.getElementById('mobile-sidebar-toggle');
    const closeBtn = document.getElementById('sidebar-close');
    const sidebar = document.getElementById('sidebar');
    const collapseBtn = document.getElementById('sidebar-collapse');

    if (!sidebar) {
        return;
    }

    const openMobile = () => {
        sidebar.classList.remove('-translate-x-full');
        backdrop?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeMobile = () => {
        sidebar.classList.add('-translate-x-full');
        backdrop?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    mobileToggle?.addEventListener('click', openMobile);
    closeBtn?.addEventListener('click', closeMobile);
    backdrop?.addEventListener('click', closeMobile);

    /*
    |--------------------------------------------------------------------------
    | Desktop sidebar collapse
    |--------------------------------------------------------------------------
    */

    collapseBtn?.addEventListener('click', () => {
        const isCollapsed = sidebar.classList.toggle('lg:w-20');

        sidebar.classList.toggle('lg:w-64', !isCollapsed);
        sidebar.classList.toggle('collapsed', isCollapsed);

        document.body.classList.toggle(
            'lg:pl-20',
            isCollapsed
        );

        document.body.classList.toggle(
            'lg:pl-64',
            !isCollapsed
        );

        localStorage.setItem(
            'sidebar-collapsed',
            isCollapsed ? '1' : '0'
        );

        document
            .querySelectorAll('.collapsible-label')
            .forEach((element) => {
                element.classList.toggle(
                    'hidden',
                    isCollapsed
                );
            });
    });

    /*
    |--------------------------------------------------------------------------
    | Restore saved sidebar state
    |--------------------------------------------------------------------------
    */

    if (
        localStorage.getItem('sidebar-collapsed') === '1'
    ) {
        collapseBtn?.click();
    }

    /*
    |--------------------------------------------------------------------------
    | Escape key closes mobile sidebar
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMobile();
        }
    });
}

/*
|--------------------------------------------------------------------------
| Inventory Table Filters
|--------------------------------------------------------------------------
|
| Supports:
| - Search
| - Category
| - Stock status
|
*/
function initInventoryFilters() {
    const search = document.getElementById('inventory-search');
    const category = document.getElementById(
        'inventory-category-filter'
    );
    const status = document.getElementById(
        'inventory-status-filter'
    );

    const rows = document.querySelectorAll(
        '[data-inventory-row]'
    );

    if (!rows.length) {
        return;
    }

    const apply = () => {
        const query = (
            search?.value || ''
        ).toLowerCase().trim();

        const selectedCategory =
            category?.value || '';

        const selectedStatus =
            status?.value || '';

        rows.forEach((row) => {
            const text = (
                row.textContent || ''
            ).toLowerCase();

            const rowCategory =
                row.dataset.category || '';

            const rowStatus =
                row.dataset.status || '';

            const matchesQuery =
                !query || text.includes(query);

            const matchesCategory =
                !selectedCategory ||
                rowCategory === selectedCategory;

            const matchesStatus =
                !selectedStatus ||
                rowStatus === selectedStatus;

            row.classList.toggle(
                'hidden',
                !(
                    matchesQuery &&
                    matchesCategory &&
                    matchesStatus
                )
            );
        });
    };

    search?.addEventListener('input', apply);
    category?.addEventListener('change', apply);
    status?.addEventListener('change', apply);
}

/*
|--------------------------------------------------------------------------
| Flash Messages
|--------------------------------------------------------------------------
|
| Automatically removes temporary Laravel flash messages.
|
*/
function initFlashDismiss() {
    document
        .querySelectorAll('[data-auto-dismiss]')
        .forEach((element) => {
            setTimeout(() => {
                element.style.transition =
                    'opacity 0.4s ease';

                element.style.opacity = '0';

                setTimeout(() => {
                    element.remove();
                }, 400);
            }, 4500);
        });
}

/*
|--------------------------------------------------------------------------
| Application Initialization
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {
    initIcons();
    initSidebar();
    initInventoryFilters();
    initFlashDismiss();

    Alpine.start();
});

/*
|--------------------------------------------------------------------------
| Livewire Navigation
|--------------------------------------------------------------------------
|
| Re-render Lucide icons when Livewire navigation injects
| new DOM elements.
|
*/

document.addEventListener(
    'livewire:navigated',
    () => {
        initIcons();
    }
);
