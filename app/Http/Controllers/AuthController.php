<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use \Illuminate\Support\Facades\Mail;
use \Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\support\Str;
use App\Models\User;

class AuthController extends Controller
{


    public function sellerSignUp(Request $request){

        $request->validate([

            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'store_category' => 'required|string',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'email' =>'required|email',
            'password' => 'required|min:8|confirmed'

        ]);

        DB::beginTransaction();

        try {

           $user = User::where('email', $request->email)->first();

            if ($user){

                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Account already exist'], 400);

            }else{


                $otp = Str::random(6);
                $verification_expiry = now()->addHours(24);

                User::create([

                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'role' => "seller",
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'store_category' => $request->store_category,
                    'password' => bcrypt($request->password),
                    "store_status" => true,
                    "status" => true,
                    "verification_code" =>  $otp,
                    "verification_expiry" => $verification_expiry

                ]);

                DB::commit();

                   //Send verification email here
                Mail::send('emails.verification', ['name' => $request->first_name . '' . $request->last_name,'otp' => $otp,'email'=>$request->email], function($message) use($request){
                    $message->to($request->email);
                    $message->subject('Verification Mail');
                });


                return response()->json([
                    'status' => 'success',
                    'message' => 'Account created successfully. A verification code has been sent to ' .$request->email . ' the code expires in 24 hours',
                    'data' => [

                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'role' => "seller",
                        'email' => $request->email,
                        'phone' => $request->phone,

                    ]], 200);

            }

        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not register user. ' . $e->getMessage()], 500);
        }
    }

    public function buyerSignUp(Request $request){

        $request->validate([

            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' =>'required|email',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'password' => 'required|min:8|confirmed'

        ]);

        DB::beginTransaction();

        try {

           $user = User::where('email', $request->email)->first();

            if ($user){

                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Account already exist'], 400);

            }else{


                $otp = Str::random(6);
                $verification_expiry = now()->addHours(24);

                User::create([

                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'role' => "buyer",
                    'email' => $request->email,
                    'phone' => $request->phone,
                    "status" => true,
                    'password' => bcrypt($request->password),
                    "verification_code" =>  $otp,
                    "verification_expiry" => $verification_expiry

                ]);

                DB::commit();

                   //Send verification email here
                Mail::send('emails.verification', ['name' => $request->first_name . '' . $request->last_name,'otp' => $otp,'email'=>$request->email], function($message) use($request){
                    $message->to($request->email);
                    $message->subject('Verification Mail');
                });


                return response()->json([
                    'status' => 'success',
                    'message' => 'Account created successfully. A verification code has been sent to ' .$request->email . ' the code expires in 24 hours',
                    'data' => [

                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'role' => "seller",
                        'email' => $request->email,
                        'phone' => $request->phone,

                    ]], 200);

            }

        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not register user. ' . $e->getMessage()], 500);
        }
    }

    public function verifyEmail(Request $request){

        $request->validate(['verification_code' => 'required|string']);

        DB::beginTransaction();

        try {

            $user = User::where('verification_code', $request->verification_code)->first();

            if(!$user){

                return response()->json(['status' => 'error', 'message' => 'wrong verifcation code'], 400);
            }

            if(now()->isBefore(Carbon::parse($user->verification_expiry))){

                $user->email_verified_at = now();
                $user->verification_code = null;
                $user->verification_expiry = null;
                $user->save();

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Email Successfully Verified',
                    'data' => ['user_id'=>$user->id]
                ],200);

            }else{

                return response()->json(['status' => 'error', 'message' => 'verification code expired.click on resend', 'data' => ['user_email'=>$user->email]], 400);
            }


        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function resendVerificationCode(Request $request){


        $data = $request->validate([

            'email' => 'required|exists:users,email|email',

        ]);

        DB::beginTransaction();

        try {

            $user = User::where('email', $data['email'])->first();

            if($user){

                if ($user->email_verified_at !== null) {

                    return response()->json(['status' => 'error', 'message' => 'Your email has been verified already'], 400);
                }

                $verification_code = Str::random(6);
                $verification_expiry = now()->addHours(24);
                $user->update(["verification_code" =>  $verification_code,"verification_expiry" =>  $verification_expiry,]);
                DB::commit();

                //Send verification email here
                Mail::send('emails.verification', ['name' => $request->full_name,'otp' => $verification_code,'email'=>$request->email], function($message) use($request){
                    $message->to($request->email);
                    $message->subject('Verification Mail');
                });

                return response()->json(['status' => 'success', 'message' => 'A verification code has been sent to ' .
                $user->email . '. Check your spam if you can\'t find it. The link expires in 24 hours'], 200);

            }else{

                return response()->json(['status' => 'error', 'message' => 'This Email is not registered with us'], 403);
            }

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'An error occurred .' . $e->getMessage()], 500);
        }
    }

    public function signIn(Request $request){


        $data = $request->validate([

           'email' => 'required|exists:users,email|email',
            'password' =>'required|string'
        ]);

        try {

            $user = User::where('email',$data['email'])->first();


            if (!$user){
                return response()->json(['status' => 'error', 'message' => 'This email is not registered'], 400);
            }

            if (!Hash::check($data['password'], $user->password)) {
                return response()->json(['status' => 'error', 'message' => 'Wrong Password'], 400);
            }

            if ($user->status === false) {

                return response()->json(['status' => 'error', 'message' => 'Account Suspended contact admin'], 400);
            }

            $token = $user->createToken('token')->plainTextToken;
            return response()->json(['status' => 'success', 'message' => 'Signin successful', 'token' => $token,'data' => $user], 200);

        } catch (\Exception $e) {

            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later. ' . $e->getMessage()], 500);

        }

    }

    public function adminSignIn(Request $request){


        $data = $request->validate([

            'email' => 'required|exists:users,email|email',
            'password' =>'required|string'
        ]);

        try {

            $user = User::whereEmail($data['email'])->first();

            if (!$user){

                return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 400);
            }

            if (!Hash::check($data['password'], $user->password)) {

                return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 400);
            }

            if ($user->role == "admin" or $user->role == "super_admin"){

                $token = $user->createToken('token')->plainTextToken;

                return response()->json(['status' => 'success', 'message' => 'Signin successfull', 'token' => $token,'data' => $user], 200);

            }else{

                return response()->json(['status' => 'error', 'message' => 'Unauthorized access'], 400);

            }

            $token = $user->createToken('token')->plainTextToken;
            return response()->json(['status' => 'success', 'message' => 'Signin successfull', 'token' => $token,'data' => $user], 200);

        } catch (\Exception $e) {

            return response()->json(['status' => 'error', 'message' => 'An error occurred. Please try again later. ' . $e->getMessage()], 500);

        }

    }

    public function forgotPassword(Request $request){

        try {

            $data = $request->validate([

                'email' => 'required|exists:users,email|email'
            ]);

            DB::beginTransaction();

            $user = User::whereEmail($data['email'])->first();

            if($user)
            {
                $user_check = DB::table('password_reset_tokens')->where('email', $data['email'])->first();

                if($user_check){

                    $otp = Str::random(6);
                    DB::table('password_reset_tokens')->where(['email' =>  $data['email']])->update([
                        'token' => $otp,
                        'created_at' => Carbon::now(),
                        'expired_at' => now()->addMinutes(30)
                      ]);
                      DB::commit();

                }else{

                    $otp = Str::random(6);
                    DB::table('password_reset_tokens')->insert([
                        'email' => $request->email,
                        'token' => $otp,
                        'created_at' => Carbon::now(),
                        'expired_at' => now()->addMinutes(30)
                      ]);
                      DB::commit();
                }


                Mail::send('emails.resetpassword', ['otp' => $otp,'email'=>$request->email], function($message) use($request){
                    $message->to($request->email);
                    $message->subject('Password Reset OTP');
                });

                return response()->json(['status' => 'success', 'message' => 'An OTP has been sent to your Email address'], 200);

            }else{

                return response()->json(['status' => 'error', 'message' => 'This Email address is not registered with us '], 404);
            }


        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'An error occurred .' . $e->getMessage()], 500);
        }
    }

    public function resetPasswordWithOTP(Request $request){

        try {

            $request->validate([
                'otp' => 'required',
                'password' => 'required|min:8|confirmed'
            ]);

            $user_check = DB::table('password_reset_tokens')->where([['token', $request->otp]])->first();

            if(!$user_check){
                return response()->json(['status' => 'error', 'message' => 'Invalid OTP'], 404);
            }else{

                if(now()->isBefore(Carbon::parse($user_check->expired_at))){

                    $user = User::where('email', $user_check->email)->first();
                    $user = $user->update(['password' => bcrypt($request->password)]);
                    DB::table('password_reset_tokens')->where('email',$user_check->email)->delete();
                    return response()->json(['status' => 'success', 'message' => 'Password reset successfully'], 200);

                }else{

                    return response()->json(['status' => 'error', 'message' => 'OTP expired'], 400);
                }
            }


        }catch(\Exception $e){

            return response()->json(['status' => 'error', 'message' => 'An error occurred .' . $e->getMessage()], 500);
        }
    }



}
