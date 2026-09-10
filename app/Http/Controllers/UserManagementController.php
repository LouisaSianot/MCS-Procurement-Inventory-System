<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    private const ROLES = ["Administrator", "Purchasing Officer", "Inventory Officer", "EndUser"];
    public function index(Request $request)
    {
        $search = trim((string) $request->query("search"));
        $users = User::with(["branch", "roles"])->when($search, fn($q) => $q->where("name", "like", "%{$search}%")->orWhere("email", "like", "%{$search}%"))->orderBy("name")->paginate(12)->withQueryString();
        return view("admin.users.index", compact("users", "search"));
    }
    public function create()
    {
        return $this->form(new User(["is_active" => true]));
    }
    public function edit(User $user)
    {
        return $this->form($user);
    }
    public function store(Request $request)
    {
        $data = $this->data($request, true);
        $user = User::create($data);
        $user->syncRoles([$request->string("role")->toString()]);
        return redirect()->route("admin.users.index")->with("success", "User created successfully.");
    }
    public function update(Request $request, User $user)
    {
        if ($user->is(auth()->user()) && ! $request->boolean("is_active")) return back()->with("error", "You cannot deactivate your own account.");
        $data = $this->data($request, false, $user);
        $user->update($data);
        $user->syncRoles([$request->string("role")->toString()]);
        return redirect()->route("admin.users.index")->with("success", "User updated successfully.");
    }
    public function destroy(User $user)
    {
        if ($user->is(auth()->user())) return back()->with("error", "You cannot delete your own account.");
        $user->delete();
        return redirect()->route("admin.users.index")->with("success", "User deleted.");
    }
    public function roles()
    {
        return view("admin.roles.index", ["roles" => Role::whereIn("name", self::ROLES)->with("permissions")->get(), "permissions" => Permission::orderBy("name")->get()]);
    }
    public function updateRole(Request $request, Role $role)
    {
        abort_unless(in_array($role->name, self::ROLES, true), 404);
        $role->syncPermissions($request->validate(["permissions" => ["array"], "permissions.*" => ["exists:permissions,name"]])["permissions"] ?? []);
        return redirect()->route("admin.roles.index")->with("success", "Permissions updated for {$role->name}.");
    }
    private function form(User $user)
    {
        return view("admin.users.form", ["user" => $user->load("roles"), "branches" => Branch::orderBy("name")->get(), "roles" => self::ROLES]);
    }
    private function data(Request $r, bool $creating, ?User $user = null): array
    {
        $rules = ["name" => ["required", "string", "max:255"], "email" => ["required", "email", "max:255", Rule::unique((new User)->getTable())->ignore($user?->id)], "branch_id" => ["nullable", Rule::exists((new Branch)->getTable(), "id")], "is_active" => ["required", "boolean"], "role" => ["required", Rule::in(self::ROLES)], "password" => $creating ? ["required", "confirmed", "min:8"] : ["nullable", "confirmed", "min:8"]];
        $data = $r->validate($rules);
        $data["is_active"] = $r->boolean("is_active");
        if (! $data["password"]) unset($data["password"]);
        else $data["password"] = Hash::make($data["password"]);
        unset($data["role"]);
        return $data;
    }
}
