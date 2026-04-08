<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashSessionController extends Controller
{
    public function cashierIndex()
    {
        $currentSession = $this->getCurrentSession();

        $sessions = $this->cashierSessionsQuery(Auth::id())
            ->take(20)
            ->get();

        return view('cashier.cash-sessions', compact('currentSession', 'sessions'));
    }

    public function cashierPrint()
    {
        $currentSession = $this->getCurrentSession();
        $sessions = $this->cashierSessionsQuery(Auth::id())->get();

        return view('cashier.cash-sessions-print', compact('currentSession', 'sessions'));
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
            $session->loadSum(['orders' => fn ($query) => $query->where('status', 'completed')], 'total');

            $expected = (float) $session->opening_amount + (float) $session->sales_total;
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
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status));

        $sessions = CashSession::query()
            ->with('cashier')
            ->withSum(['orders' => fn ($query) => $query->where('status', 'completed')], 'total')
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
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
            'sales_total' => (clone $baseQuery)->withSum(['orders' => fn ($query) => $query->where('status', 'completed')], 'total')->get()->sum('sales_total'),
        ];

        return view('admin.cash-sessions.index', compact('sessions', 'cashiers', 'summary'));
    }

    public function adminPrint(Request $request)
    {
        $sessions = CashSession::query()
            ->with('cashier')
            ->withSum(['orders' => fn ($query) => $query->where('status', 'completed')], 'total')
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->user_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest('opened_at')
            ->get();

        $summary = [
            'open_count' => $sessions->where('status', 'open')->count(),
            'closed_count' => $sessions->where('status', 'closed')->count(),
            'sales_total' => $sessions->sum(fn ($session) => (float) $session->sales_total),
        ];

        return view('admin.cash-sessions.print', compact('sessions', 'summary'));
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
        return CashSession::query()
            ->withSum(['orders' => fn ($query) => $query->where('status', 'completed')], 'total')
            ->where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();
    }

    private function cashierSessionsQuery(int $userId)
    {
        return CashSession::query()
            ->with('cashier')
            ->withSum(['orders' => fn ($query) => $query->where('status', 'completed')], 'total')
            ->where('user_id', $userId)
            ->latest('opened_at');
    }
}
