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
        return collect(config('chapung.emails', []))
            ->map(fn (string $address, string $key): array => [
                'label' => (string) config('chapung.email_labels.'.$key, ucfirst($key)),
                'address' => $this->validAddress($address),
            ])
            ->filter(fn (array $mailbox): bool => filled($mailbox['address']))
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function adminEmails(): array
    {
        return collect(config('chapung.admin_emails', []))
            ->map(fn (string $email): string => mb_strtolower($this->validAddress($email)))
            ->filter()
            ->unique()
            ->values()
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
            ?: Arr::get($departments, 'admin.address')
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
