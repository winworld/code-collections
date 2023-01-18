<?php

namespace App\Http\Controllers\Admin;

use Gate;
use App\Models\Nrc;
use App\Models\Role;
use App\Models\User;
use App\Models\Business;
use App\Models\UserMeta;
use App\Models\Speciality;
use App\Helpers\SiteHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\MassDestroyUserRequest;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user_ids = $this->getRelatedUsers();

        if ($request->ajax()) {

            $selected_role = $request->route('role');
            $query = User::with(['role', 'roles', 'specialities', 'businesses'])
                ->select(sprintf('%s.*', (new User())->table))
                ->when(!Auth::user()->isSuperAdmin, function ($q) {
                    // don't allow super admin users to be edited by lower roles
                    return $q->where('id', '!=', config('constants.role_id.super_admin'));
                })
                ->when($selected_role, function($q) use ($selected_role) {
                    $const_roles = config('constants.role_id');
                    // don't allow super admin users to be edited by lower roles
                    return $q->where('role_id', $const_roles[$selected_role]);
                });


            if($user_ids) {
               $query->whereIn('id', $user_ids);
            }

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'user_show';
                $editGate = 'user_edit';
                $deleteGate = 'user_delete';
                $crudRoutePart = 'users';

                return view('partials.datatablesActions', compact(
                'viewGate',
                'editGate',
                'deleteGate',
                'crudRoutePart',
                'row'
            ));
            });

            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : '';
            });
            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : '';
            });
            $table->editColumn('phone', function ($row) {
                return $row->phone ? $row->phone : '';
            });
            $table->addColumn('role_title', function ($row) {
                return $row->role ? $row->role->title : '';
            });

            $table->editColumn('is_active', function ($row) {
                return $row->is_active ? User::IS_ACTIVE_SELECT[$row->is_active] : '';
            });

            $table->rawColumns(['actions', 'placeholder', 'role']);

            return $table->make(true);
        }

        $roles        = Role::get();
        $specialities = Speciality::get();
        $businesses   = Business::get();

        return view('admin.users.index', compact('roles', 'specialities', 'businesses'));
    }

    public function create()
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = $this->getLimitedRoles();

        $specialities = Speciality::pluck('title', 'id');

        $businesses = Business::pluck('name', 'id');

        $nrc_types = [
            "N" => "(နိုင်)", "EH" => "(ဧည့်)", "PYU" => "(ပြု)", "THA" => "(သာ)", "THI" => "(သီ)"
        ];

        $nrcs = Nrc::orderBy('state_number_en')->get([
            'id',
            'state_number_en',
            'state_number_mm',
            'long_district',
            'short_district_mm',
        ]);

        return view('admin.users.create', compact('businesses', 'roles', 'specialities',  'nrcs', 'nrc_types'));
    }

    public function store(StoreUserRequest $request)
    {       
        $user = User::create($request->all());

        $user->phone_verified_at = now();
        $user->token = Str::random(40);
        $user->save();

         // update meta values
        $this->saveMeta($request->meta, $user);
        $user->specialities()->sync($request->input('specialities', []));
        $user->roles()->sync($request->input('role_id', []));
        $user->businesses()->sync($request->input('businesses', []));

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        abort_if(!$this->isUserInSameBusiness($user), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if(!$this->canManageByRole($user), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = $this->getLimitedRoles();

        $specialities = Speciality::pluck('title', 'id');

        $businesses = Business::pluck('name', 'id');

        $user->load('role', 'roles', 'specialities', 'businesses', 'userUserMeta');

        $nrc_types = [
            "N" => "(နိုင်)", "EH" => "(ဧည့်)", "PYU" => "(ပြု)", "THA" => "(သာ)", "THI" => "(သီ)"
        ];

        $nrcs = Nrc::orderBy('state_number_en')->get([
            'id',
            'state_number_en',
            'state_number_mm',
            'long_district',
            'short_district_mm',
        ]);

        return view('admin.users.edit', compact('businesses', 'roles', 'specialities', 'user', 'nrcs', 'nrc_types'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->all());

        if (!empty($request['password'])) {
            $user->password = Hash::make($request['password']);
            $user->save();
        }
         // update meta values
        $this->saveMeta($request->meta, $user);
        $user->specialities()->sync($request->input('specialities', []));
        $user->businesses()->sync($request->input('businesses', []));
        $user->roles()->sync($request->input('role_id', []));
        return redirect()->route('admin.users.index');
    }

    public function show(User $user)
    {
        abort_if(!$this->isUserInSameBusiness($user), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_if(!$this->canManageByRole($user), Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->load('role', 'roles', 'specialities', 'businesses', 'patientBookings', 'userUserMeta');

        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // delete meta
        $user->userUserMeta()->delete();
        // detach specialities
        $user->specialities()->detach();

        $user->delete();

        return back();
    }

    public function massDestroy(MassDestroyUserRequest $request)
    {
        $users = User::whereIn('id', request('ids'))->get();
        foreach($users as $user) {
            // delete meta
            $user->userUserMeta()->delete();
            // detach specialities
            $user->specialities()->detach();

            $user->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
    // this will check if the user has HIGHER role to manage the users
    public function canManageByRole(User $user, $role_id = '')
    {
        if(Auth::user()->isSuperAdmin) return true;

        $role_id = !empty($role_id) ? $role_id : Auth::user()->role->id;
        $can_manage_role_key = array_search($role_id, config('constants.role_id'));
        $can_edit = true;
        if($can_manage_role_key) {
            $can_manage_roles = config('constants.can_manage_roles');
            if(Arr::exists($can_manage_roles, $can_manage_role_key)) {
                $can_edit = in_array($user->role->id, $can_manage_roles[$can_manage_role_key]) ? true : false;
            }
        }

        return $can_edit;
    }

    // make sure the user can see the list of user from the same business/clinic
    public function isUserInSameBusiness(User $user)
    {
        if(Auth::user()->isAdmin || Auth::user()->isSuperAdmin) return true;

        if( $user->role->id == config('constants.role_id.clinic')) {
            $auth_user_business_ids = Auth::user()->businesses->pluck('id')->toArray();
            $user_business_ids = $user->businesses->pluck('id')->toArray();

            $found = array_intersect($auth_user_business_ids, $user_business_ids);

            if( count($found) > 0 ) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    public function getRelatedUsers()
    {
        if(Auth::user()->isAdmin || Auth::user()->isSuperAdmin) return null;

        $user_ids = [];
        $role_id = Auth::user()->role->id;

        // they should be seeing only the staff within the same clinic
        if(Auth::user()->isClinic) {
            $business_ids = Auth::user()->businesses->pluck('id')->toArray();
            $businesses = Business::whereIn('id', $business_ids)->get();

            foreach($businesses as $b) {
                $user_ids = array_merge($user_ids, $b->businessUsers->pluck('id')->toArray());
            }
        }

        // get more users who are allowed to be editable by roles.
        $can_manage_role_key = array_search($role_id, config('constants.role_id'));

        if($can_manage_role_key) {
            $can_manage_roles = config('constants.can_manage_roles');
            $allow_role_ids = array_intersect(config('constants.role_id'), $can_manage_roles[$can_manage_role_key]);
            if(Auth::user()->isClinic) {
                // we don't want other staff from another clinic
                $allow_role_ids = Arr::except($allow_role_ids, [$can_manage_role_key]);
            }
        }

        $allow_roles = Arr::flatten($allow_role_ids);

        $more_user = User::with('roles')->whereIn('role_id', $allow_roles)->pluck('id')->toArray();

        $user_ids = array_merge($user_ids, $more_user);

        return $user_ids;
    }

    // show the list of roles which are below or same as the logged in user's role.
    public function getLimitedRoles()
    {
        switch (Auth::user()->roles[0]['id']) {
            case config('constants.role_id.super_admin'):
                $q = Role::all();
                break;
            case config('constants.role_id.admin'):
                $q = Role::whereNotIn(
                    'id',
                    [
                        config('constants.role_id.super_admin'),
                    ]);
                break;
            case config('constants.role_id.clinic'):
                $q = Role::whereNotIn(
                    'id',
                    [
                        config('constants.role_id.super_admin'),
                        config('constants.role_id.admin'),
                        config('constants.role_id.business'),
                    ]);
                break;
            default:
                break;
        }

        return $q->pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');
    }

    public function saveMeta($meta, $user)
    {
        if (!$meta) {
            return;
        }

        foreach ($meta as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    if($k == 'dob') {
                        $v = SiteHelper::setFormattedDate($v);
                    }                    
                    UserMeta::updateOrCreate([
                        'user_id' => $user->id,
                        'meta_key' => $k,
                        'meta_value' => $v,
                    ], [
                        'user_id' => $user->id,
                        'meta_key' => $k,
                        'meta_value' => $v,
                    ]);
                }
            } else if (!is_null($val) && $val) {
                if($key == 'dob') {
                    $val = SiteHelper::setFormattedDate($val);
                } 
                UserMeta::updateOrCreate([
                    'user_id' => $user->id,
                    'meta_key' => $key,
                ], [
                    // 'user_id' => $user->id,
                    // 'meta_key' => $key,
                    'meta_value' => $val,
                ]);
            }

        }
    }
}
