<?php

namespace App\Http\Controllers;

use App\Mail\ContactInquiryReceived;
use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Photography;
use App\Services\MailboxService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class PublicPageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'artistCount' => Artist::active()->count(),
            'artworkCount' => Artwork::count(),
            'photographyCount' => Photography::count(),
        ]);
    }

    public function contact(MailboxService $mailboxes): View
    {
        return view('pages.contact', [
            'mailboxes' => $mailboxes->departments(),
        ]);
    }

    public function sendContact(Request $request, MailboxService $mailboxes): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'department' => ['required', 'string', Rule::in($mailboxes->keys())],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        Mail::to($mailboxes->addressFor($payload['department']))
            ->send(new ContactInquiryReceived($payload, $mailboxes->labelFor($payload['department'])));

        return back()->with('toast', [
            'message' => 'Pesan Anda sudah dikirim ke tim Chapung Art.',
        ]);
    }
}
