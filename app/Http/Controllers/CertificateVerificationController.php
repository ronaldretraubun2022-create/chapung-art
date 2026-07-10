<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Contracts\View\View;

class CertificateVerificationController extends Controller
{
    public function __invoke(string $certificateNumber): View
    {
        $certificate = Certificate::with(['artwork.artist', 'artist'])
            ->where('certificate_number', $certificateNumber)
            ->first();

        return view('certificates.verify', [
            'certificate' => $certificate,
            'certificateNumber' => $certificateNumber,
        ]);
    }
}
