<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->ensureAdmin();

        $filters = $this->normalizeFilters($request);
        $format = $request->query('format', 'csv') === 'pdf' ? 'pdf' : 'csv';

        return $format === 'pdf'
            ? $this->downloadPdf($filters)
            : $this->downloadCsv($filters);
    }

    protected function ensureAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()?->is_admin) {
            abort(403);
        }
    }

    protected function normalizeFilters(Request $request): array
    {
        $preset = $request->query('range', 'this_month');
        $now = now();
        $from = null;
        $to = null;

        if ($preset === 'custom') {
            $from = $request->filled('date_from') ? Carbon::parse($request->query('date_from'))->startOfDay() : null;
            $to = $request->filled('date_to') ? Carbon::parse($request->query('date_to'))->endOfDay() : null;
        } elseif ($preset === 'this_year') {
            $from = $now->copy()->startOfYear();
            $to = $now->copy()->endOfYear();
        } elseif ($preset === 'last_30') {
            $from = $now->copy()->subDays(30)->startOfDay();
            $to = $now->copy()->endOfDay();
        } elseif ($preset === 'all_time') {
            $from = null;
            $to = null;
        } else {
            $preset = 'this_month';
            $from = $now->copy()->startOfMonth();
            $to = $now->copy()->endOfMonth();
        }

        return [
            'range' => $preset,
            'date_from' => $from,
            'date_to' => $to,
            'status' => $request->query('status', 'all'),
            'payment_method' => $request->query('payment_method', 'all'),
            'search' => trim((string) $request->query('search', '')),
            'sort' => $request->query('sort', 'date'),
            'direction' => $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc',
        ];
    }

    protected function transactionQuery(array $filters): Builder
    {
        $query = Booking::query()->with(['room', 'user']);

        $query->when($filters['date_from'], function (Builder $query, Carbon $from) {
            $query->where(function (Builder $query) use ($from) {
                $query->where('paid_at', '>=', $from)
                    ->orWhere(function (Builder $query) use ($from) {
                        $query->whereNull('paid_at')->where('updated_at', '>=', $from);
                    });
            });
        });

        $query->when($filters['date_to'], function (Builder $query, Carbon $to) {
            $query->where(function (Builder $query) use ($to) {
                $query->where('paid_at', '<=', $to)
                    ->orWhere(function (Builder $query) use ($to) {
                        $query->whereNull('paid_at')->where('updated_at', '<=', $to);
                    });
            });
        });

        $query->when($filters['status'] !== 'all', function (Builder $query) use ($filters) {
            if ($filters['status'] === 'confirmed') {
                $query->where('payment_status', 'paid');
            } elseif ($filters['status'] === 'pending') {
                $query->whereIn('payment_status', ['pending', 'for_verification']);
            } else {
                $query->where('payment_status', $filters['status']);
            }
        });

        $query->when($filters['payment_method'] !== 'all', fn (Builder $query) => $query->where('payment_method', $filters['payment_method']));

        $query->when($filters['search'] !== '', function (Builder $query) use ($filters) {
            $search = $filters['search'];
            $query->where(function (Builder $query) use ($search) {
                $query->where('id', $search)
                    ->orWhere('payment_reference', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('room', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"));
            });
        });

        match ($filters['sort']) {
            'amount' => $query->orderBy('total', $filters['direction']),
            'status' => $query->orderBy('payment_status', $filters['direction']),
            default => $query->orderByRaw('COALESCE(paid_at, updated_at, created_at) ' . $filters['direction']),
        };

        return $query->orderBy('id', $filters['direction']);
    }

    protected function downloadCsv(array $filters): StreamedResponse
    {
        $filename = $this->filename('csv', $filters);

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Villa Estella Payment Transactions']);
            fputcsv($handle, ['Exported at', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, ['Filters', $this->filterSummary($filters)]);
            fputcsv($handle, []);
            fputcsv($handle, ['Transaction ID', 'Booking ID', 'Guest', 'Room', 'Amount', 'Status', 'Payment Method', 'Date/Time']);

            $this->transactionQuery($filters)->chunk(500, function ($bookings) use ($handle) {
                foreach ($bookings as $booking) {
                    fputcsv($handle, $this->row($booking));
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function downloadPdf(array $filters)
    {
        $rows = [];
        $this->transactionQuery($filters)->chunk(500, function ($bookings) use (&$rows) {
            foreach ($bookings as $booking) {
                $rows[] = implode(' | ', $this->row($booking));
            }
        });

        $lines = array_merge([
            'Villa Estella Payment Transactions',
            'Exported at: ' . now()->format('Y-m-d H:i:s'),
            'Filters: ' . $this->filterSummary($filters),
            '',
            'Transaction ID | Booking ID | Guest | Room | Amount | Status | Method | Date/Time',
        ], $rows ?: ['No transactions found.']);

        return response($this->makeSimplePdf($lines), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->filename('pdf', $filters) . '"',
        ]);
    }

    protected function row(Booking $booking): array
    {
        return [
            $booking->transaction_id,
            '#' . $booking->id,
            $booking->contact_name ?: $booking->user?->name ?: 'Guest',
            $booking->room?->name ?: 'Room',
            number_format((float) $booking->total, 2, '.', ''),
            ucfirst($booking->report_payment_status),
            $booking->payment_method ?: 'n/a',
            optional($booking->transaction_date)->format('Y-m-d H:i:s') ?: '',
        ];
    }

    protected function filterSummary(array $filters): string
    {
        return collect([
            'range=' . $filters['range'],
            'from=' . ($filters['date_from']?->toDateString() ?? 'all'),
            'to=' . ($filters['date_to']?->toDateString() ?? 'all'),
            'status=' . $filters['status'],
            'method=' . $filters['payment_method'],
            'search=' . ($filters['search'] ?: 'none'),
        ])->implode('; ');
    }

    protected function filename(string $extension, array $filters): string
    {
        $range = $filters['range'] === 'custom'
            ? (($filters['date_from']?->format('Ymd') ?? 'start') . '_' . ($filters['date_to']?->format('Ymd') ?? 'end'))
            : $filters['range'];

        return 'transactions_' . now()->format('Y-m-d') . '_' . $range . '.' . $extension;
    }

    protected function makeSimplePdf(array $lines): string
    {
        $pages = array_chunk($lines, 42);
        $objects = [];
        $pagesKids = [];
        $objectNumber = 1;

        foreach ($pages as $pageLines) {
            $contentNumber = $objectNumber++;
            $pageNumber = $objectNumber++;
            $pagesKids[] = $pageNumber . ' 0 R';
            $content = "BT\n/F1 9 Tf\n50 792 Td\n";

            foreach ($pageLines as $line) {
                $content .= '(' . $this->pdfText($line) . ") Tj\n0 -16 Td\n";
            }

            $content .= "ET";
            $objects[$contentNumber] = "<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream";
            $objects[$pageNumber] = "<< /Type /Page /Parent 0 0 R /MediaBox [0 0 612 842] /Resources << /Font << /F1 FONT_OBJECT 0 R >> >> /Contents {$contentNumber} 0 R >>";
        }

        $pagesNumber = $objectNumber++;
        $catalogNumber = $objectNumber++;
        $fontNumber = $objectNumber++;
        $objects[$pagesNumber] = "<< /Type /Pages /Kids [" . implode(' ', $pagesKids) . "] /Count " . count($pagesKids) . " >>";
        $objects[$catalogNumber] = "<< /Type /Catalog /Pages {$pagesNumber} 0 R >>";
        $objects[$fontNumber] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

        foreach ($objects as $number => $body) {
            $objects[$number] = str_replace(
                ['/Parent 0 0 R', 'FONT_OBJECT'],
                ["/Parent {$pagesNumber} 0 R", (string) $fontNumber],
                $body
            );
        }

        ksort($objects);
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$body}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (max(array_keys($objects)) + 1) . "\n0000000000 65535 f \n";

        for ($i = 1; $i <= max(array_keys($objects)); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }

        return $pdf . "trailer\n<< /Size " . (max(array_keys($objects)) + 1) . " /Root {$catalogNumber} 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    protected function pdfText(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], substr($text, 0, 120));
    }
}
