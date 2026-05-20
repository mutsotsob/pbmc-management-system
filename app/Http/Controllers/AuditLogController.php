<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['created_at', 'event', 'user_name', 'auditable_type'];
        $sort = in_array($request->query('sort'), $allowedSorts, true)
            ? $request->query('sort')
            : 'created_at';
        $dir = $request->query('dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $logs = AuditLog::query()
            ->when($request->filled('event'), fn ($q) => $q->event($request->query('event')))
            ->when($request->filled('user'),  fn ($q) => $q->where('user_name', $request->query('user')))
            ->when($request->filled('model'), fn ($q) => $q->where('auditable_type', 'like', '%' . $request->query('model') . '%'))
            ->orderBy($sort, $dir)
            ->paginate(25)
            ->withQueryString();

        $events = AuditLog::distinct()->pluck('event')->sort()->values();
        $users  = User::orderBy('name')->pluck('name', 'name');

        return view('audit-logs.index', compact('logs', 'sort', 'dir', 'events', 'users'));
    }
}
