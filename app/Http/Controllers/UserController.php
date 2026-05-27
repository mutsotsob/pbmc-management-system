<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Notifications\UserCreatedNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
    public function index(Request $request)
    {
        [$query, $q, $sort, $dir] = $this->usersQuery($request);

        $users = $query
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'q', 'sort', 'dir'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $newUser = User::create([
            ...$validated,
            'password'    => Hash::make($validated['password']),
            'user_status' => true,
        ]);

        $newUser->notify(new UserCreatedNotification($validated['password']));

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot disable your own account.');
        }

        $user->update(['user_status' => !$user->user_status]);

        return back()->with('success', 'User status updated.');
    }

    public function bulkEnable(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        User::whereIn('id', $data['ids'])->update(['user_status' => true]);

        return back()->with('success', 'Selected users enabled successfully.');
    }

    public function bulkDisable(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        $activeAdminsAfter = User::where('user_type', 'admin')
            ->where('user_status', true)
            ->whereNotIn('id', $data['ids'])
            ->count();

        if ($activeAdminsAfter === 0) {
            return back()->with('error', 'Cannot disable all active administrators. At least one must remain active.');
        }

        User::whereIn('id', $data['ids'])->update(['user_status' => false]);

        return back()->with('success', 'Selected users disabled successfully.');
    }

    public function exportExcel(Request $request)
    {
        [$query] = $this->usersQuery($request);
        $users = $query->get();

        return $this->downloadExcel($users, 'users_report_all_' . now()->format('Y_m_d_His') . '.xls');
    }

    public function exportSelectedExcel(Request $request)
    {
        $users = $this->resolveSelectedUsers($request);

        if ($users->isEmpty()) {
            return back()->with('error', 'Please select at least one user to export.');
        }

        return $this->downloadExcel($users, 'users_report_selected_' . now()->format('Y_m_d_His') . '.xls');
    }

    public function exportCsv(Request $request)
    {
        [$query] = $this->usersQuery($request);
        $users = $query->get();

        return $this->downloadCsv($users, 'users_report_all_' . now()->format('Y_m_d_His') . '.csv');
    }

    public function exportSelectedCsv(Request $request)
    {
        $users = $this->resolveSelectedUsers($request);

        if ($users->isEmpty()) {
            return back()->with('error', 'Please select at least one user to export.');
        }

        return $this->downloadCsv($users, 'users_report_selected_' . now()->format('Y_m_d_His') . '.csv');
    }

    public function exportPdf(Request $request): View
    {
        [$query] = $this->usersQuery($request);

        return $this->renderPdf($query->get(), 'System Users Report');
    }

    public function exportSelectedPdf(Request $request)
    {
        $users = $this->resolveSelectedUsers($request);

        if ($users->isEmpty()) {
            return back()->with('error', 'Please select at least one user to export.');
        }

        return $this->renderPdf($users, 'Selected System Users Report');
    }

    private function usersQuery(Request $request): array
    {
        $q    = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'name');
        $dir  = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowedSorts = ['name', 'email', 'department', 'job_title', 'user_type', 'user_status', 'created_at'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $query = User::query()
            ->when($q, fn ($query) => $query->where(fn ($qq) =>
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('department', 'like', "%{$q}%")
                    ->orWhere('job_title', 'like', "%{$q}%")
            ))
            ->orderBy($sort, $dir);

        return [$query, $q, $sort, $dir];
    }

    private function resolveSelectedUsers(Request $request)
    {
        $data = $request->validate([
            'selected_user_ids'   => ['required', 'array', 'min:1'],
            'selected_user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        return User::query()
            ->whereIn('id', $data['selected_user_ids'])
            ->orderBy('name')
            ->get();
    }

    private function downloadExcel($users, string $filename)
    {
        $content = view('users.exports.excel', compact('users'))->render();

        return Response::make($content, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function renderPdf($users, string $title): View
    {
        return view('users.exports.pdf', [
            'users'       => $users,
            'title'       => $title,
            'generatedAt' => now(),
            'generatedBy' => Auth::user(),
        ]);
    }

    private function downloadCsv($users, string $filename)
    {
        return Response::streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');

            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $this->reportHeaders());

            foreach ($users as $user) {
                fputcsv($handle, $this->reportRow($user));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function reportHeaders(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Department',
            'Job Title',
            'Phone Number',
            'User Type',
            'Status',
            'Email Verified At',
            'Created At',
            'Updated At',
        ];
    }

    private function reportRow(User $user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->department,
            $user->job_title,
            $user->phone_number,
            ucfirst((string) $user->user_type),
            $user->user_status ? 'Active' : 'Disabled',
            $user->email_verified_at?->format('Y-m-d H:i:s'),
            $user->created_at?->format('Y-m-d H:i:s'),
            $user->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
