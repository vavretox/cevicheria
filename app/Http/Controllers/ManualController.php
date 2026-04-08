<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ManualController extends Controller
{
    private const MANUAL_VERSION = 'v1.0';

    public function admin(): Response
    {
        return $this->downloadPdf(
            'manuals.admin',
            'manual_admin_cevicheria_pos.pdf'
        );
    }

    public function cashier(): Response
    {
        return $this->downloadPdf(
            'manuals.cashier',
            'manual_cajero_cevicheria_pos.pdf'
        );
    }

    public function waiter(): Response
    {
        return $this->downloadPdf(
            'manuals.waiter',
            'manual_mesero_cevicheria_pos.pdf'
        );
    }

    private function downloadPdf(string $view, string $filename): Response
    {
        $pdf = Pdf::loadView($view, $this->buildManualPayload())
            ->setPaper('a4');

        return $pdf->download($filename);
    }

    private function buildManualPayload(): array
    {
        return [
            'generatedAt' => now()->format('d/m/Y H:i'),
            'trainingDate' => now()->format('d/m/Y'),
            'manualVersion' => self::MANUAL_VERSION,
            'appName' => config('app.name', 'Cevicheria POS'),
            'logoDataUri' => $this->getLogoDataUri(),
        ];
    }

    private function getLogoDataUri(): ?string
    {
        $logoPath = public_path('images/logo-los-pepes.jpeg');
        if (!is_file($logoPath) || !is_readable($logoPath)) {
            return null;
        }

        $binary = @file_get_contents($logoPath);
        if ($binary === false) {
            return null;
        }

        $mimeType = mime_content_type($logoPath) ?: 'image/jpeg';

        return 'data:' . $mimeType . ';base64,' . base64_encode($binary);
    }
}
