<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ConfirmRegistration;
use App\Notifications\InviteUsers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    //

    public function store(Request $request) {
        foreach($request->toArray() as $row) {       
             User::create($row);
        }

        return [
            'status'=>'ok',
            'message'=>'Successfully created users'
        ];
    }

    public function sendInvites(Request $request) {

        $users = User::all()->where('user_role', 'user')->where('user_status', 'inactive');
        
        if(!Gate::allows('admin-activity')) {
            abort(403);
        }
        
        Notification::send($users, new InviteUsers());

        return [
            'status'=>'ok',
            'message'=>'Successfully sent invites to users'
        ];
    }

    public function update(Request $request) {

        $user = User::find($request->query()['id']);

        if($user) {
            // $path = $request->file('avatar')->getRealPath();
            // $avatar = file_get_contents($path);
            // $base64 = base64_encode($avatar);

            $validation = Validator::make($request->all(), [
                'user_name'=>'min:4,max:20',
                'password'=>'confirmed'
            ]);


            if(!$validation->fails()) {
                $user->update([
                    'user_name'=>$request->user_name,
                    'password'=>Hash::make($request->password),
                    'user_status'=>'pending',
                    'user_pin'=> static::generateRandomString(6)
                ]);
    
                $user->notify(new ConfirmRegistration($user->user_pin, $user->name, $user->id));
                return [
                    'status'=>'ok',
                    'message'=>'Successfully updated initial basic info. (An email was sent along with your PIN to continue)'
                ];
            } else {
                return $validation->errors();
            }
        }

        else abort(404);
    }

    public function updateProfile(Request $request) {
        $user = User::find($request->query()['id']);

        if($user) {
            $base64 = '';
            $validator = [
                'user_name'=>'min:4|max:20',
                'old_password'=>'current_password:api',
                'newpassword'=>'confirmed',
            ];
            if($request->file('avatar')!==null) {
                $path = $request->file('avatar')->getRealPath();
                $avatar = file_get_contents($path);
                $base64 = base64_encode($avatar);
                $validator['avatar'] = 'dimensions:width=256,height=256';
            }
            
           // dd($validator);
            $validation = Validator::make($request->all(), $validator);


            if(!$validation->fails()) {
                $user->update([
                    'name'=>$request->name,
                    'user_name'=>$request->user_name,
                    'password'=>Hash::make($request->newpassword),
                    'avatar'=>$request->file('avatar')!==null ? $base64 : $user->avatar,
                ]);
                
                return [
                    'status'=>'ok',
                    'message'=>'Profile Successfully Updated'
                ];
               // $user->notify(new InviteUsers);
            } else {
                return $validation->errors();
            }
        }

        else abort(404);
    }


    public function login(Request $request) {
        $user = User::where('user_name', $request->user_name)->where('user_status', 'active')->first();

        if($user) {
            if(Hash::check($request->password, $user->password)) {
                $user->update([
                 'api_token'=> Str::random(60)
                ]);
     
                return [
                    'status'=>'Access Granted',
                    'api_token'=>$user->api_token
                ];
             } else abort(403, 'Invalid Password');
        }

        else abort(403, 'Invalid Credentials');

    }

    public function confirmRegistration(Request $request) {
        $user = User::where('user_pin', $request->user_pin)->where('id', $request->query()['id'])->first();
        


        if($user) {
            $user->update([
                'user_status'=>'active',
                'registered_at'=>Carbon::now()
            ]);

            return [
                'status'=>'ok',
                'message'=>'You are now successfully registered. Thank you'
            ];
        } else {
            abort(404, 'User not found.');
        }

    }

    static function generateRandomString(int $n=0) {
        $al = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $len = !$n ? random_int(7, 12) : $n; // Chose length randomly in 7 to 12

        $ddd = array_map(function($a) use ($al){
            $key = random_int(0, 9);
            return $al[$key];
        }, array_fill(0,$len,0));
        return implode('', $ddd);
    }
}
