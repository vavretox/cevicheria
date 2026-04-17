<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashSessionController extends Controller
{
    public function cashierIndex()
    {
        $currentSession = $this->getCurrentSession();
        $selectedDate = request('date', now()->toDateString());

        $sessions = $this->cashierSessionsQuery(Auth::id())
            ->take(20)
            ->get();

        return view('cashier.cash-sessions', compact('currentSession', 'sessions', 'selectedDate'));
    }

    public function cashierPrint()
    {
        $currentSession = $this->getCurrentSession();
        $sessions = $this->cashierSessionsQuery(Auth::id())->get();

        return view('cashier.cash-sessions-print', compact('currentSession', 'sessions'));
    }

    public function cashierThermalPrint(Request $request)
    {
        $selectedDate = $this->resolveSelectedDate($request);

        $session = $this->cashSessionReportQuery()
            ->with('cashier')
            ->withCount(['orders as completed_orders_count' => fn ($query) => $query->where('status', 'completed')])
            ->where('user_id', Auth::id())
            ->when($selectedDate, fn ($query) => $query->whereDate('opened_at', $selectedDate->toDateString()))
            ->latest('opened_at')
            ->first();

        return view('cash-sessions.thermal-print', [
            'session' => $session,
            'title' => $selectedDate
                ? 'Arqueo del ' . $selectedDate->format('d/m/Y')
                : 'Arqueo de caja',
            'selectedDate' => $selectedDate,
            'returnUrl' => route('cashier.cash-sessions', $request->only(['date'])),
        ]);
    }

    public function open(Request $request)
    {
        $request->validate([
            'opening_amount' => 'required|numeric|min:0',
            'opening_note' => 'nullable|string',
        ]);

        if ($this->getCurrentSession()) {
            return redirect()->route('cashier.cash-sessions')
                ->with('error', 'Ya tienes una caja abierta.');
        }

        CashSession::create([
            'user_id' => Auth::id(),
            'status' => 'open',
            'opening_amount' => $request->opening_amount,
            'opening_note' => $request->opening_note,
            'opened_at' => now(),
        ]);

        return redirect()->route('cashier.cash-sessions')
            ->with('success', 'Caja abierta correctamente.');
    }

    public function close(Request $request, $id)
    {
        $request->validate([
            'counted_amount' => 'required|numeric|min:0',
            'closing_note' => 'nullable|string',
        ]);

        $session = CashSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->findOrFail($id);

        DB::transaction(function () use ($session, $request) {
            $paymentSums = $this->paymentSumsForSession($session);

            $expected = (float) $session->opening_amount + $paymentSums['cash'];
            $counted = (float) $request->counted_amount;

            $session->update([
                'status' => 'closed',
                'closed_at' => now(),
                'expected_amount' => $expected,
                'counted_amount' => $counted,
                'difference_amount' => $counted - $expected,
                'closing_note' => $request->closing_note,
            ]);
        });

        return redirect()->route('cashier.cash-sessions')
            ->with('success', 'Caja cerrada correctamente.');
    }

    public function adminIndex(Request $request)
    {
        $baseQuery = CashSession::query()
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('opened_at', $request->date))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status));

        $sessions = $this->cashSessionReportQuery()
            ->with('cashier')
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('opened_at', $request->date))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest('opened_at')
            ->paginate(20);

        $cashiers = \App\Models\User::where('role', 'cajero')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $summary = [
            'open_count' => (clone $baseQuery)->where('status', 'open')->count(),
            'closed_count' => (clone $baseQuery)->where('status', 'closed')->count(),
            'cash_sales_total' => $this->cashSessionReportQuery((clone $baseQuery))->get()->sum('cash_sales_total'),
            'qr_sales_total' => $this->cashSessionReportQuery((clone $baseQuery))->get()->sum('qr_sales_total'),
            'sales_total' => $this->cashSessionReportQuery((clone $baseQuery))->get()->sum('sales_total'),
        ];

        return view('admin.cash-sessions.index', compact('sessions', 'cashiers', 'summary'));
    }

    public function adminPrint(Request $request)
    {
        $sessions = $this->cashSessionReportQuery()
            ->with('cashier')
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('opened_at', $request->date))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest('opened_at')
            ->get();

        $summary = [
            'open_count' => $sessions->where('status', 'open')->count(),
            'closed_count' => $sessions->where('status', 'closed')->count(),
            'cash_sales_total' => $sessions->sum(fn ($session) => (float) $session->cash_sales_total),
            'qr_sales_total' => $sessions->sum(fn ($session) => (float) $session->qr_sales_total),
            'sales_total' => $sessions->sum(fn ($session) => (float) $session->sales_total),
        ];

        return view('admin.cash-sessions.print', compact('sessions', 'summary'));
    }

    public function adminThermalPrint(Request $request)
    {
        $selectedDate = $this->resolveSelectedDate($request);

        $session = $this->cashSessionReportQuery()
            ->with('cashier')
            ->withCount(['orders as completed_orders_count' => fn ($query) => $query->where('status', 'completed')])
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($selectedDate, fn ($query) => $query->whereDate('opened_at', $selectedDate->toDateString()))
            ->latest('opened_at')
            ->first();

        return view('cash-sessions.thermal-print', [
            'session' => $session,
            'title' => $selectedDate
                ? 'Arqueo del ' . $selectedDate->format('d/m/Y')
                : 'Arqueo de caja',
            'selectedDate' => $selectedDate,
            'returnUrl' => route('admin.cash-sessions', $request->only(['user_id', 'status', 'date'])),
        ]);
    }

    public static function currentOpenSessionForAuthenticatedUser(): ?CashSession
    {
        return CashSession::where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();
    }

    private function getCurrentSession(): ?CashSession
    {
        return $this->cashSessionReportQuery()
            ->where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();
    }

    private function cashierSessionsQuery(int $userId)
    {
        return $this->cashSessionReportQuery()
            ->with('cashier')
            ->where('user_id', $userId)
            ->latest('opened_at');
    }

    private function latestOpenSessionQuery()
    {
        return $this->cashSessionReportQuery()
            ->with('cashier')
            ->withCount(['orders as completed_orders_count' => fn ($query) => $query->where('status', 'completed')])
            ->where('status', 'open')
            ->latest('opened_at');
    }

    private function resolveSelectedDate(Request $request): ?Carbon
    {
        if (!$request->filled('date')) {
            return null;
        }

        $request->validate([
            'date' => 'nullable|date',
        ]);

        return Carbon::parse($request->date)->startOfDay();
    }

    private function cashSessionReportQuery($query = null)
    {
        $query ??= CashSession::query();

        return $query->addSelect([
            'orders_sum_total' => $this->completedOrdersSum('total'),
            'orders_sum_cash_paid_amount' => $this->completedOrdersSum(
                "CASE
                    WHEN payment_method = 'cash' THEN COALESCE(cash_paid_amount, total)
                    WHEN payment_method = 'mixed' THEN COALESCE(cash_paid_amount, 0)
                    ELSE 0
                END"
            ),
            'orders_sum_qr_paid_amount' => $this->completedOrdersSum(
                "CASE
                    WHEN payment_method = 'qr' THEN COALESCE(qr_paid_amount, total)
                    WHEN payment_method = 'mixed' THEN COALESCE(qr_paid_amount, 0)
                    ELSE 0
                END"
            ),
        ]);
    }

    private function completedOrdersSum(string $expression)
    {
        return Order::query()
            ->selectRaw("COALESCE(SUM({$expression}), 0)")
            ->whereColumn('cash_session_id', 'cash_sessions.id')
            ->where('status', 'completed');
    }

    private function paymentSumsForSession(CashSession $session): array
    {
        $reportSession = $this->cashSessionReportQuery(
            CashSession::query()->whereKey($session->getKey())
        )->first();

        return [
            'cash' => (float) ($reportSession?->cash_sales_total ?? 0),
            'qr' => (float) ($reportSession?->qr_sales_total ?? 0),
            'total' => (float) ($reportSession?->sales_total ?? 0),
        ];
    }
}
