<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Event;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q'));

        $users = User::query()
            ->with(['roles:id,name', 'assignedEvents:id,title'])
            ->when($q, function ($builder) use ($q) {
                $builder->where(function ($b) use ($q) {
                    $b->where('username', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%");
                });
            })
            ->orderBy('username')
            ->paginate(20)
            ->withQueryString();

        return view('pages.users.index', [
            'users'  => $users,
            'roles'  => Role::query()->orderBy('name')->pluck('name'),
            'events' => Event::query()->orderBy('title')->get(['id', 'title']),
            'q'      => $q,
        ]);
    }

    public function store(StoreUserRequest $request, UserService $service): RedirectResponse
    {
        $service->create($request->validated());

        return back()->with('success', 'User created successfully.');
    }

    public function update(UpdateUserRequest $request, User $user, UserService $service): RedirectResponse
    {
        $service->update($user, $request->validated());

        return back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user, UserService $service): RedirectResponse
    {
        $service->delete($user);

        return back()->with('success', 'User deleted.');
    }
}
