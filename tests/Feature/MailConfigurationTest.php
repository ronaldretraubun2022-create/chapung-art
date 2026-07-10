<?php

use App\Mail\ContactInquiryReceived;
use Illuminate\Support\Facades\Mail;

function contactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Maria Contact',
        'email' => 'maria@example.com',
        'department' => 'contact',
        'subject' => 'Artwork Inquiry',
        'message' => 'Saya ingin bertanya tentang koleksi Chapung Art.',
    ], $overrides);
}

test('mail department configuration is available for production mailboxes', function () {
    $departments = config('mail.departments');

    expect(array_keys($departments))->toBe([
        'admin',
        'info',
        'gallery',
        'news',
        'media',
        'support',
        'finance',
        'contact',
    ]);

    foreach ($departments as $key => $mailbox) {
        expect($mailbox['address'])->toBe("{$key}@chapungart.com")
            ->and($mailbox['label'])->not->toBeEmpty();
    }
});

test('contact page renders department mailbox options', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Contact Form')
        ->assertSee('Admin')
        ->assertSee('Info')
        ->assertSee('Gallery')
        ->assertSee('News')
        ->assertSee('Media')
        ->assertSee('Support')
        ->assertSee('Finance')
        ->assertSee('Contact');
});

test('contact form sends mail to selected department mailbox', function (string $department, string $address) {
    Mail::fake();

    $this->post(route('contact.send'), contactPayload([
        'department' => $department,
        'subject' => ucfirst($department).' request',
    ]))->assertRedirect();

    Mail::assertSent(ContactInquiryReceived::class, function (ContactInquiryReceived $mail) use ($department, $address): bool {
        return $mail->hasTo($address)
            && $mail->payload['department'] === $department
            && $mail->payload['email'] === 'maria@example.com';
    });
})->with([
    ['admin', 'admin@chapungart.com'],
    ['info', 'info@chapungart.com'],
    ['gallery', 'gallery@chapungart.com'],
    ['news', 'news@chapungart.com'],
    ['media', 'media@chapungart.com'],
    ['support', 'support@chapungart.com'],
    ['finance', 'finance@chapungart.com'],
    ['contact', 'contact@chapungart.com'],
]);

test('contact form rejects unknown mailbox and does not send mail', function () {
    Mail::fake();

    $this->from(route('contact'))
        ->post(route('contact.send'), contactPayload(['department' => 'unknown']))
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors('department');

    Mail::assertNothingSent();
});

test('env example documents every production mailbox address', function () {
    $env = parse_ini_file(base_path('.env.example'), false, INI_SCANNER_RAW) ?: [];

    expect($env['MAIL_ADMIN_ADDRESS'] ?? null)->toBe('admin@chapungart.com')
        ->and($env['MAIL_INFO_ADDRESS'] ?? null)->toBe('info@chapungart.com')
        ->and($env['MAIL_GALLERY_ADDRESS'] ?? null)->toBe('gallery@chapungart.com')
        ->and($env['MAIL_NEWS_ADDRESS'] ?? null)->toBe('news@chapungart.com')
        ->and($env['MAIL_MEDIA_ADDRESS'] ?? null)->toBe('media@chapungart.com')
        ->and($env['MAIL_SUPPORT_ADDRESS'] ?? null)->toBe('support@chapungart.com')
        ->and($env['MAIL_FINANCE_ADDRESS'] ?? null)->toBe('finance@chapungart.com')
        ->and($env['MAIL_CONTACT_ADDRESS'] ?? null)->toBe('contact@chapungart.com');
});
