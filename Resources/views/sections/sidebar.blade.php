{{-- Titan Zero — Worksuite Custom Module Sidebar (canonical dropdown) --}}
@php
    use Illuminate\Support\Facades\Route;

    // Build submenu items in the same pattern as Worksuite core (no :link on x-menu-item)
    $items = [
        ['route' => 'titan.zero.index',      'label' => __('Dashboard')],
        ['route' => 'titan.zero.chat',       'label' => __('Chat & Standards')],
        ['route' => 'titan.zero.coaches',    'label' => __('Coaches')],
        ['route' => 'titan.zero.business',   'label' => __('Business Coach')],
        ['route' => 'titan.zero.foreman',    'label' => __('Foreman Coach')],
        ['route' => 'titan.zero.compliance', 'label' => __('Standards & Compliance')],
        ['route' => 'titan.zero.wizards',    'label' => __('Wizards')],
        ['route' => 'titan.zero.generators', 'label' => __('Generators')],
        ['route' => 'titan.zero.templates',  'label' => __('Templates')],
        ['route' => 'titan.zero.help',       'label' => __('Help')],
    ];

    // Only show links that actually exist
    $visible = [];
    foreach ($items as $it) {
        if (Route::has($it['route'])) $visible[] = $it;
    }
@endphp

@if(!empty($visible))
    <x-menu-item icon="sparkles" :text="__('Titan Zero')">
        <x-slot name="iconPath">
            {{-- simple "sparkles" icon --}}
            <path d="M8 0.5l.7 2.6 2.6.7-2.6.7L8 7.1l-.7-2.6-2.6-.7 2.6-.7L8 .5z" />
            <path d="M13 6l.55 2.05L15.6 8.6l-2.05.55L13 11.2l-.55-2.05L10.4 8.6l2.05-.55L13 6z" />
            <path d="M3 9l.55 2.05L5.6 11.6l-2.05.55L3 14.2l-.55-2.05L.4 11.6l2.05-.55L3 9z" />
        </x-slot>

        <div class="accordionItemContent">
            @foreach($visible as $it)
                <x-sub-menu-item :link="route($it['route'])" :text="$it['label']" />
            @endforeach
        </div>
    </x-menu-item>
@endif
