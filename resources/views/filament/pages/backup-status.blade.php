<x-filament-panels::page>
    @php
        $files = $this->getBackupFiles();
    @endphp

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.backup.files') }}</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format(count($files)) }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.backup.latest') }}</p>
                <p class="mt-2 text-lg font-semibold text-gray-950 dark:text-white">{{ $this->getLatestBackupLabel() }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase text-gray-500">{{ __('admin.backup.total_size') }}</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $this->getTotalBackupSize() }}</p>
            </div>
        </div>

        <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ __('admin.backup.private_storage') }}</h2>
            <p class="mt-2 break-all text-sm text-gray-600 dark:text-gray-300">{{ $this->getBackupDirectoryPath() }}</p>
            <p class="mt-2 text-sm text-gray-500">{{ __('admin.backup.metadata_only') }}</p>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ __('admin.backup.history') }}</h2>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800">
                        <tr>
                            <th class="py-3 pr-4">{{ __('admin.backup.file') }}</th>
                            <th class="py-3 pr-4">{{ __('admin.backup.stored_path') }}</th>
                            <th class="py-3 pr-4 text-right">{{ __('admin.backup.size') }}</th>
                            <th class="py-3 pr-4">{{ __('admin.backup.modified') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($files as $file)
                            <tr>
                                <td class="py-3 pr-4 font-medium text-gray-950 dark:text-white">{{ $file['name'] }}</td>
                                <td class="py-3 pr-4 text-gray-600 dark:text-gray-300">{{ $file['path'] }}</td>
                                <td class="py-3 pr-4 text-right text-gray-950 dark:text-white">{{ $file['size'] }}</td>
                                <td class="py-3 pr-4 text-gray-600 dark:text-gray-300">{{ $file['modified_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-gray-500">{{ __('admin.backup.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
