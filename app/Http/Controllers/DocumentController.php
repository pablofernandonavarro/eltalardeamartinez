<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    /**
     * Download the regulation document.
     */
    public function downloadRegulation(): BinaryFileResponse
    {
        // Use the signed regulation document
        $path = 'documents/reglamento_talar_firmado.pdf';

        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'El documento no está disponible.');
        }

        $fullPath = Storage::disk('public')->path($path);

        return response()->download($fullPath, 'Reglamento_El_Talar_de_Martinez.pdf');
    }
}
