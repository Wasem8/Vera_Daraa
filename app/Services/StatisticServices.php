<?php

namespace App\Services;

use App\Http\Responses\Response;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;

class StatisticServices
{

    public function numberClients($request): array
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate   = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $newClients = User::role('client')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return [
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'new_clients' => $newClients,
        ];
    }

    public function mostPopularServices()
    {
        return Service::withCount('bookings')
            ->orderBy('booking_count','desc')
            ->take(10)
            ->get();
    }

    public function financialReports(string $reportType = 'monthly', string $date = null): array
    {
        $date = $date ?? now()->format('Y-m');

        if ($reportType === 'monthly') {
            $startDate = Carbon::parse($date)->startOfMonth();
            $endDate   = Carbon::parse($date)->endOfMonth();
            $title     = "Monthly report: " . $startDate->format('F Y');
        } else {
            $startDate = Carbon::parse($date)->startOfYear();
            $endDate   = Carbon::parse($date)->endOfYear();
            $title     = "Yearly report: " . $startDate->year;
        }

        $invoices = Invoice::query()
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->orderBy('invoice_date')
            ->get();

        $summary = [
            'total_invoices'   => $invoices->count(),
            'total_amount'     => $invoices->sum('total_amount'),
            'paid_amount'      => $invoices->sum('paid_amount'),
            'remaining_amount' => $invoices->sum('remaining_amount'),
        ];

        return [
            'title'   => $title,
            'summary' => $summary,
            'data'    => $invoices,
        ];
    }
}
