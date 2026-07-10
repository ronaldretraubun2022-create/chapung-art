<?php

namespace App\Services;

use Illuminate\Support\Arr;

class MailboxService
{
    /**
     * @return array<string, array{label: string, address: string}>
     */
    public function departments(): array
    {
        return collect(config('mail.departments', []))
            ->map(fn (array $mailbox, string $key): array => [
                'label' => (string) ($mailbox['label'] ?? ucfirst($key)),
                'address' => $this->validAddress((string) ($mailbox['address'] ?? '')),
            ])
            ->filter(fn (array $mailbox): bool => filled($mailbox['address']))
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->departments());
    }

    public function addressFor(string $department): string
    {
        $departments = $this->departments();

        return Arr::get($departments, $department.'.address')
            ?: Arr::get($departments, 'contact.address')
            ?: Arr::get($departments, 'info.address')
            ?: (string) config('mail.from.address');
    }

    public function labelFor(string $department): string
    {
        return Arr::get($this->departments(), $department.'.label', ucfirst($department));
    }

    private function validAddress(string $address): string
    {
        $address = trim($address);

        return filter_var($address, FILTER_VALIDATE_EMAIL) ? $address : '';
    }
}
