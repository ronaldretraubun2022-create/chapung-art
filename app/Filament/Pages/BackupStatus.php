<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use UnitEnum;

class BackupStatus extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Backup Status';

    protected static ?string $title = 'Backup Status';

    protected static ?string $slug = 'backup-status';

    protected string $view = 'filament.pages.backup-status';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_any backup') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runBackup')
                ->label('Run Backup')
                ->icon('heroicon-o-play')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Run full backup')
                ->modalDescription('This will create a database and upload backup in private storage. It may take a while on cPanel hosting.')
                ->action('runBackup'),
        ];
    }

    public function runBackup(): void
    {
        try {
            $exitCode = Artisan::call('backup:run', [
                '--disable-notifications' => true,
            ]);

            if ($exitCode !== 0) {
                Notification::make()
                    ->title('Backup failed')
                    ->body(Str::limit(Artisan::output(), 240))
                    ->danger()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Backup created')
                ->body('Backup file tersimpan di private storage, bukan public folder.')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Backup failed')
                ->body(Str::limit($exception->getMessage(), 240))
                ->danger()
                ->send();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getBackupFiles(): array
    {
        $disk = Storage::disk('local');
        $directory = $this->backupDirectory();

        return collect($disk->allFiles($directory))
            ->filter(fn (string $file): bool => str_ends_with($file, '.zip'))
            ->map(fn (string $file): array => [
                'name' => basename($file),
                'path' => $file,
                'size' => $this->formatBytes($disk->size($file)),
                'modified_at' => Carbon::createFromTimestamp($disk->lastModified($file))->format('d M Y H:i'),
            ])
            ->sortByDesc('modified_at')
            ->values()
            ->all();
    }

    public function getBackupDirectoryPath(): string
    {
        return storage_path('app/private').DIRECTORY_SEPARATOR.$this->backupDirectory();
    }

    public function getLatestBackupLabel(): string
    {
        $latest = $this->getBackupFiles()[0] ?? null;

        return $latest ? $latest['modified_at'] : 'Belum ada backup';
    }

    public function getTotalBackupSize(): string
    {
        $disk = Storage::disk('local');
        $directory = $this->backupDirectory();

        $bytes = collect($disk->allFiles($directory))
            ->filter(fn (string $file): bool => str_ends_with($file, '.zip'))
            ->sum(fn (string $file): int => $disk->size($file));

        return $this->formatBytes($bytes);
    }

    private function backupDirectory(): string
    {
        return trim((string) config('backup.backup.name', config('app.name', 'chapung-art')), '/');
    }

    private function formatBytes(int|float $bytes): string
    {
        if ($bytes < 1024) {
            return number_format($bytes).' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $size = $bytes / 1024;

        foreach ($units as $unit) {
            if ($size < 1024) {
                return number_format($size, 2).' '.$unit;
            }

            $size /= 1024;
        }

        return number_format($size, 2).' PB';
    }
}
