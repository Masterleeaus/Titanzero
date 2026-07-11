<x-filament-widgets::widget>
    <x-filament::section
        :collapsible="true"
        :collapsed="! $expanded"
        icon="heroicon-o-cpu-chip"
    >
        <x-slot name="heading">
            🤖 Ask Titan Zero
        </x-slot>

        <div class="space-y-3">
            {{-- Context indicator --}}
            @if (!empty($context))
                <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                    @foreach ($context as $key => $value)
                        <span class="rounded bg-gray-100 px-2 py-0.5 dark:bg-gray-700">
                            {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}
                        </span>
                    @endforeach
                </div>
            @endif

            {{-- Output mode switcher --}}
            <div class="flex gap-2 text-sm">
                <span class="font-medium text-gray-700 dark:text-gray-300">Mode:</span>
                <span class="rounded-full bg-primary-100 px-2 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                    {{ ucfirst($outputMode) }}
                </span>
            </div>

            {{-- Chat placeholder --}}
            <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-center text-sm text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500">
                <p class="mb-1 font-medium">Titan Zero AI</p>
                <p>The embedded AI panel is ready. Connect your API key in AI Settings to activate chat.</p>
            </div>

            {{-- Input area --}}
            <div class="flex items-center gap-2">
                <input
                    type="text"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500"
                    placeholder="Ask Titan Zero anything about this record…"
                    disabled
                />
                <x-filament::button
                    size="sm"
                    disabled
                    color="primary"
                >
                    Ask
                </x-filament::button>
            </div>

            <p class="text-xs text-gray-400 dark:text-gray-600">
                All queries are tenant-scoped and logged in the AI Audit Log.
            </p>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
