<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Laratrust\Models\Role;
use App\Traits\GlobalTraits;
use Illuminate\Http\Request;
use Laratrust\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RoleAndPermissionController extends Controller
{

    use GlobalTraits;

    public function Roles()
    {
        $roles = Role::with('permissions')->get();

        $rolesWithPermissions = [];

        foreach ($roles as $role) {
            $permissions = $role->permissions->pluck('name');

            $rolesWithPermissions[] = [
                'id' => $role->id,
                'name' => $role->name,
                'avatar' => $role->avatar,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'permissions' => $permissions,
            ];
        }

        return $this->SendResponse($rolesWithPermissions, "success all roles", 200);
    }

    public function Role($id)
    {
        $role = Role::find($id);
        if (!$role) {
            return $this->SendResponse(null, "error the rols not faund", 401);
        }
        $permissions = $role->permissions;
        $roleName = $role;

        return $this->SendResponse($roleName, "success this role", 200);
    }


    public function Permissons()
    {
        $Permissons = Permission::all();
        return $this->SendResponse($Permissons, "success this Permissons", 200);
    }


    public function Permissons_role($id)
    {
        return $id;
    }


    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => 'required|string|between:2,100',
            'display_name' => 'required|string|max:100',
            'description' => 'string',
            'avatar' => 'max:2048',
        ]);
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->errors(), 401);
        }
        $folderPath = public_path('avatars');
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }
        $role = new Role;
        if ($request->hasFile('avatar')) {
            $avatar = $request->file("avatar");
            $imageName = time() . '_' . $avatar->getClientOriginalName();
            $avatar->move(public_path("avatars/"), $imageName);
            $role->avatar = $imageName;
        }
        $role->name = $request->name;
        $role->display_name = $request->display_name;
        $role->description = $request->description;
        $role->save();
        $inputString = $request->type;
        $group = json_decode($inputString);
        $permissions = [];
        foreach ($group as $item) {
            $permission = Permission::where('id', $item)->first();
            if ($permission) {
                $permissions[] = $permission;
            }
        }
        $role->givePermissions($permissions);
        return $this->SendResponse([
            'role' => $role,
            'permissions' => $role->permissions->pluck('name')
        ], "success create new role", 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "name" => 'required|string|between:2,100',
            'display_name' => 'required|string|max:100',
            'description' => 'string',
            'avatar' => 'max:2048',
            //'permissions' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->errors(), 401);
        }

        $folderPath = public_path('avatars');
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }
        // Optionally, store the file path or avatar information in the database
        // $group = Permission::where('name', 'users-create')->get();
        $role = Role::find($id);
        if ($request->hasFile('avatar')) {


            $filePath = public_path('avatars/' . $role->avatar);

            if ($role->avatar && file_exists($filePath)) {
                unlink($filePath);
                //echo 'delete image file';
            }
            $avatar = $request->file("avatar");
            $imageName = time() . '_' . $avatar->getClientOriginalName();
            $avatar->move(public_path("avatars/"), $imageName);
            $role->avatar = $imageName;
        }
        $role->name = $request->name;
        $role->display_name = $request->display_name;
        $role->description = $request->description;
        $role->update();
        //---------------------------------------------------------
        if ($request->type) {
            $role->permissions()->sync([]);
        }
        $inputString = $request->type;
        $group = json_decode($inputString);
        $permissions = [];
        if ($group) {
            foreach ($group as $item) {
                $permission = Permission::where('id', $item)->first();
                if ($permission) {
                    $permissions[] = $permission;
                }
            }
        }
        $role->givePermissions($permissions);
        $permissions = $role->permissions;
        $roleName = $role;
        return $this->SendResponse(['role' => $roleName, 'permissions' => $permissions], "success update tha roles", 200);
        //---------------------------------------------------------
    }

    public function delete($id)
    {
        $Role = Role::find($id);

        $filePath = public_path('avatars/' . $Role->avatar);
        if ($Role->avatar && file_exists($filePath)) {
            unlink($filePath);
            //echo 'delete image file';
        }
        $Role->delete();
        if ($Role) {
            return $this->SendResponse($Role, "success delete tha roles", 200);
        }
    }


    public function givePerToRole(Request $request)
    {
        $role_id = $request->role_id;
        $adminRole = Role::find($role_id);

        if ($request->permissions) {
            // Delete all permissions old for the role
            $adminRole->permissions()->sync([]);
        }
        $inputString = $request->permissions;
        $group = json_decode($inputString);
        $permissions = [];
        if ($group) {
            foreach ($group as $item) {
                $permission = Permission::where('id', $item)->first();
                if ($permission) {
                    $permissions[] = $permission;
                }
            }
        }
        $adminRole->syncPermissions($permissions);

        return $this->SendResponse($permissions, "success async permissions with role", 200);
    }


    public function RemoveAllPerForRole(Request $request)
    {

        $role_id = $request->role_id;
        $role = Role::find($role_id); // test admin

        if ($role) {
            $role->permissions()->sync([]);
            return $this->SendResponse(null, "success remove permissions from role", 200);
        } else {
            return $this->SendResponse(null, "Error remove  permissions from role", 401);
        }
    }


    public function RemoveRoleFromUser(Request $request)
    {
        $user = User::find($request->user_id);
        $role = Role::find($request->role_id);
        $remove =  $user->removeRole($role);

        if ($remove) {
            return $this->SendResponse($request->user_id, "success remove  roles from user", 200);
        }
    }

    public function giveRoleToUser(Request $request)
    {
        $role_id = $request->role_id;
        $TypeRole = Role::find($role_id);
        $inputString = $request->users;
        $group = json_decode($inputString);
        $users = [];
        if ($group) {
            foreach ($group as $item) {
                $user = User::where('id', $item)->first();
                if ($user) {
                    $user->removeRole($TypeRole);
                    $user->addRole($TypeRole);
                    $users[] = $user;
                }
            }
        }
        return $this->SendResponse($group, "success async users with role", 200);
    }

    public function RemoveAllUsersFormRole(Request $request)
    {
        $role_id = $request->role_id;
        $TypeRole = Role::find($role_id);
        $user_ids = User::all()->pluck("id");
        if ($user_ids) {
            foreach ($user_ids as $item) {
                $user = User::where('id', $item)->first();
                if ($user) {
                    $user->removeRole($TypeRole);
                }
            }
        }

        return $this->SendResponse(null, "success remove permissions from role", 200);
    }
}
