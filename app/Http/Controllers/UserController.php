<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the users with their roles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = max(1, min(100, (int) $request()->query('per_page', 10)));
        $query = User::with('roles');

        if($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        }elseif($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $allowedSorts = ['id', 'name', 'email', 'created_at', 'updated_at'];
        $sortBy = $request->query('sort_by', 'id');
        if(! in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }
        $sortDir = $request->query('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDir);

        $users = $query->paginate($perPage)->appends($request->query());

        return response()->json($users);
    }
}
