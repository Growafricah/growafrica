<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Import Log facade
use \Illuminate\Support\Facades\Mail;
use \Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\User;


class UserController extends Controller
{


    public function index(){

        try{
             $user = User::find(auth()->user()->id);

            return response()->json(['success' => 'success', 'message' => 'User profile retrieved successfully', 'data' => ['users' => $user] ], 200);

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not get profile' . $e->getMessage()], 500);

        }

    }

    public function updateProfile(Request $request){

        $data = $request->validate([

            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'gender' => 'required|string',
            'email' =>'required|email',
            'address' => 'required|string',
            'country' => 'required|string',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',

        ]);

        DB::beginTransaction();

        try {

            $user = User::where('id',auth()->user()->id)->first();

            if ($user){
                 // Update user
                $user->update($data);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile updated successfully',
                    'data' => [
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'gender' => $request->gender,
                        'phone' => $request->phone,
                        'country' => $request->country,
                        'address' => $request->address
                    ]], 200);
            }else{
                // Rollback if any step fails
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Unauthorized action'], 403);
            }

        } catch (\Exception $e) {
            // Rollback in case of exception
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not update user. ' . $e->getMessage()], 500);
        }
    }

    public function uploadProfilePic(Request $request){

        $request->validate([

            'pic' => 'required|mimes:jpeg,png,jpg|max:5048',
        ]);

        DB::beginTransaction();

        try{

            $user = User::where('id',auth()->user()->id)->first();

            $picture_path = $request->file('pic')->store('images', 'public');

            $pic_url = env('WEB_URL') . '/storage/' . $picture_path;


            if($user){

                $update = $user->update([ "pic" => $pic_url]);
                DB::commit();
                if($update){

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Image uploaded successfully',
                        'data' => ['pic' => $pic_url]], 200);
                }else{
                    DB::rollback();
                    return response()->json(['status' => 'error', 'message' => 'Unauthorized action'], 403);
                }

            }else{
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'User does not exist'], 404);

            }

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'An error occurred .' . $e->getMessage()], 500);
        }

    }

    public function changePassword(Request $request){

        try{

            $data = $request->validate([
                'current_password' => 'required|string',
                'new_password' =>'required|min:8|max:24|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%@&()+=-_]).*$/|confirmed'
            ]);

            $user = User::where('id',auth()->user()->id)->first();
            if($user){

                if (!Hash::check($data['current_password'], $user->password)) {

                    return response()->json(['success' => 'success', 'message' => 'Current password does not match '], 400);

                }else{

                    $user->update(["password" => bcrypt($data['new_password'])]);
                    return response()->json(['success' => 'success', 'message' => 'Password changed successfully'], 200);
                }

            }else{

                return response()->json(['success' => 'error', 'message' => 'User does not exist'], 404);
            }

        }catch (\Exception $e) {

            return response()->json(['success' => 'error', 'message' => 'An error occurred. Please try again later. ' . $e->getMessage()], 500);

        }

    }

    public function openStore(){


        DB::beginTransaction();

        try{

            $user = User::where('id',auth()->user()->id)->first();

            if($user){

                $update_user = $user->update([ "store_status" => true]);
                DB::commit();
                if($update_user){

                    return response()->json(['status' => 'success','message' => 'Store opened successfully'], 200);
                }

            }else{
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'User does not exist'], 400);

            }

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'An error occurred .' . $e->getMessage()], 500);
        }

    }

    public function closeStore(){


        DB::beginTransaction();

        try{

            $user = User::where('id',auth()->user()->id)->first();

            if($user){

                $update_user = $user->update([ "store_status" => false]);
                DB::commit();
                if($update_user){

                    return response()->json(['status' => 'success','message' => 'Store closed successfully and you wont appear in searches'], 200);
                }

            }else{
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'User does not exist'], 400);

            }

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'An error occurred .' . $e->getMessage()], 500);
        }

    }

    public function updateBankAccountDetails(Request $request){

        $data = $request->validate([

            'bank_name' => 'required|string',
            'account_name' => 'required|string',
            'account_number' => 'required|string',
        ]);

        DB::beginTransaction();

        try {

            $user = User::where('id',auth()->user()->id)->first();

            if ($user){
                 // Update user
                $user->update($data);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Bank Account details  updated successfully',
                    'data' => [
                        'bank_name' => $request->bank_name,
                        'account_name' => $request->account_name,
                        'account_number' => $request->account_number,
                    ]], 200);
            }else{
                // Rollback if any step fails
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Unauthorized action'], 403);
            }

        } catch (\Exception $e) {
            // Rollback in case of exception
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not update account. ' . $e->getMessage()], 500);
        }
    }

    public function applyKyc(Request $request){

        $request->validate([

            'gender' => 'required|string',
            'business_name' => 'required|string',
            'business_city' => 'required|string',
            'business_state' =>'required|string',
            'address' =>'required|string',
            'id_doc' => 'nullable|file|mimes:jpeg,png,jpg|max:12048',


        ]);

        DB::beginTransaction();

        try {

            $user = User::where('id',auth()->user()->id)->first();

            if ($user){

                $doc_path = $user->id_doc;

                if ($request->hasFile('id_doc')) {

                    $path = $request->file('id_doc')->store('KYCDocs', 'public');
                    $doc_path = env('WEB_URL') . '/storage/' .  $path;
                }

                $user->update([

                    'id_doc' => $doc_path,
                    'gender' => $request->gender,
                    'business_name' => $request->business_name,
                    'business_city' => $request->business_city,
                    'business_state' => $request->business_state,
                    'address' => $request->address,
                    'kyc_status' => "applied",
                ]);

                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => '  KYC updated and application received'], 200);
            }else{

                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Unauthorized action'], 403);
            }

        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not receive application. ' . $e->getMessage()], 500);
        }
    }


    public function fetchPendingKyc(){

        try{
             $user = User::where('role','seller')->where('kyc_status',"pending")->get();

            return response()->json(['success' => 'success', 'message' => 'Pending sellers kyc retrieved successfully', 'data' => ['users' => $user] ], 200);

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not get profile' . $e->getMessage()], 500);

        }

    }


    public function fetchAppliedKyc(){

        try{
             $user = User::where('role','seller')->where('kyc_status',"applied")->get();

            return response()->json(['success' => 'success', 'message' => 'kyc applications retrieved successfully', 'data' => ['users' => $user] ], 200);

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not get profile' . $e->getMessage()], 500);

        }

    }


    public function fetchApprovedKyc(){

        try{
             $user = User::where('role','seller')->where('kyc_status',"approved")->get();

            return response()->json(['success' => 'success', 'message' => 'Approved sellers kyc retrieved successfully', 'data' => ['users' => $user] ], 200);

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not get profile' . $e->getMessage()], 500);

        }

    }


    public function fetchRejectedKyc(){

        try{
             $user = User::where('role','seller')->where('kyc_status',"rejected")->get();

            return response()->json(['success' => 'success', 'message' => 'Rejected sellers kyc retrieved successfully', 'data' => ['users' => $user] ], 200);

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not get profile' . $e->getMessage()], 500);

        }

    }


    public function updateSellerKyc(Request $request,$user_id){

        $request->validate([

            'status' => 'required|string',
            'reason' => 'nullable|string',

        ]);

        DB::beginTransaction();

        try {

            $user = User::where('id',$user_id)->first();

            if ($user){




                if($request->status == "rejected"){

                    $message = " Your KYC was not approved ";

                    $response_message = " KYC application rejected and user notified";

                    $user->update(['kyc_status' => "rejected"]);
                    DB::commit();

                }else{

                    $message = " Your KYC has been approved, you can now proceed to add products.";

                    $response_message = " KYC application approved and user notified";

                    $user->update(['kyc_status' => $request->status]);
                    DB::commit();
                }

                try {
                    Mail::send('emails.kyc', ['name' => $user->first_name . $user->last_name, 'text' => $message, 'reason' => $request->reason], function ($message) use ($user) {
                        $message->to($user->email);
                        $message->subject('KYC');
                    });
                } catch (\Exception $e) {

                    Log::error('Failed to send KYC email: ' . $e->getMessage());
                }

                return response()->json([
                    'status' => 'success',
                    'message' => $response_message], 200);
            }else{

                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Unauthorized action'], 403);
            }

        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not receive application. ' . $e->getMessage()], 500);
        }
    }



    // public function fetchUserNotifications(){

    //     try{

    //          $notifications = DB::table('notifications')->where('user_id',auth()->user()->id)->where('read_at', Null)->get();

    //         if($notifications){

    //             return response()->json(['success' => 'success', 'message' => 'User notifications', 'data' => ['notifications' =>  $notifications] ], 200);

    //         }else{

    //             return response()->json(['success' => 'error', 'message' => 'No notification avaiable'], 400);
    //         }

    //     }catch(\Exception $e){

    //         return response()->json(['success' => 'error', 'message' => $e->getMessage()], 500);

    //     }

    // }

    // public function readNotification($notification_id){


    //     DB::beginTransaction();

    //     try {

    //         $notification = DB::table('notifications')->where('id',$notification_id)->first();

    //         if ($notification){
    //              // Update user
    //             DB::table('notifications')->where('id',$notification_id)->update(['read_at' => now()]);

    //             DB::commit();
    //             return response()->json([
    //                 'status' => 'success',
    //                 'message' => 'read successfully'], 200);
    //         }else{
    //             // Rollback if any step fails
    //             DB::rollback();
    //             return response()->json(['status' => 'error', 'message' => 'Unauthorized action'], 403);
    //         }

    //     } catch (\Exception $e) {
    //         // Rollback in case of exception
    //         DB::rollback();
    //         return response()->json(['status' => 'error', 'message' =>  $e->getMessage()], 500);
    //     }
    // }


    public function createAdmin(Request $request){

        $request->validate([

            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' =>'required|email',
            'password' => 'required|min:8'

        ]);

        DB::beginTransaction();

        try {

            $user = User::whereEmail($request->email)->first();

            if ($user){

                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Account already exist'], 400);

            }else{

                User::create([

                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'role' => "admin",
                    'email' => $request->email,
                    'password' =>bcrypt($request->password),
                    "email_verified_at" => now(),

                ]);

                DB::commit();
                return response()->json(['status' => 'success','message' => 'Account created successfully'], 200);

            }

        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Could not register user. ' . $e->getMessage()], 500);
        }
    }

    public function deleteAdmin($user_id)
    {

        DB::beginTransaction();

        try {

            $user = User::where('id',$user_id)->first();

            if ($user) {

                $user->delete();
                DB::commit();
                return response()->json(['status' => 'success','message' => 'Admin deleted successfully'], 200);
            } else {
                DB::rollback();
                return response()->json(['status' => 'error','message' => 'Admin could not found '],400);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error','message' => 'An error occurred: ' . $e->getMessage()], 500);
        }

    }


    public function fetchAllAdmins(){

        try{
             $admins = User::where('role', 'admin')->orWhere('role', 'super_admin')->get();

            if($admins){

                return response()->json(['success' => 'success', 'message' => 'Admin retrieved successfully', 'data' => ['admins' => $admins] ], 200);
            }

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not fetch admins' . $e->getMessage()], 500);

        }

    }

    public function fetchAllBuyers(){

        try{
             $buyers = User::where('role', 'buyer')->get();

            if($buyers){

                return response()->json(['success' => 'success', 'message' => 'Buyers retrieved successfully', 'data' => ['buyers' => $buyers] ], 200);
            }

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not fetch buyers' . $e->getMessage()], 500);

        }

    }

    public function fetchAllSellers(){

        try{
             $sellers = User::where('role', 'seller')->get();

            if($sellers){

                return response()->json(['success' => 'success', 'message' => 'Sellers retrieved successfully', 'data' => ['sellers' => $sellers] ], 200);
            }

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not fetch sellers' . $e->getMessage()], 500);

        }

    }

    public function fetchUser($user_id){

        try{
             $user = User::where('id',$user_id)->first();

            if($user){

                return response()->json(['success' => 'success', 'message' => 'User profile retrieved successfully', 'data' => ['user' => $user] ], 200);

            }else{

                return response()->json(['success' => 'error', 'message' => 'User does not exist'], 400);
            }

        }catch(\Exception $e){

            return response()->json(['success' => 'error', 'message' => 'Could not get profile' . $e->getMessage()], 500);

        }

    }

    // public function updateUserVerificationStatus(Request $request){

    //     $request->validate([

    //         'verification_status' => 'required|boolean',
    //         'user_id' => 'required|string',
    //         'reason' => 'nullable|string',
    //     ]);

    //     DB::beginTransaction();

    //     try{

    //         $user = User::where('id',$request->user_id)->first();

    //         if($user){

    //             $update_user = $user->update([ "verification_status" => $request->verification_status]);
    //             DB::commit();
    //             if($update_user){

    //                 if( $request->verification_status === false){

    //                     $message = "Your KYC was not appoved";
    //                     Mail::send('emails.kyc', ['name' => $user->full_name,'text'=>$message,'reason'=>$request->reason], function($message) use($user){
    //                         $message->to($user->email);
    //                         $message->subject('KYC');
    //                     });
    //                 }else{
    //                     $message = "Your KYC has been appoved";

    //                     Mail::send('emails.kyc', ['name' => $user->full_name,'text'=> $message,'reason'=>$request->reason], function($message) use($user){
    //                         $message->to($user->email);
    //                         $message->subject('KYC');
    //                     });
    //                 }


    //                 return response()->json([
    //                     'status' => 'success',
    //                     'message' => 'User verification status set successfully',
    //                     'data' => ['verification_status' => $request->verification_status]], 200);

    //             }

    //         }else{
    //             DB::rollback();
    //             return response()->json(['status' => 'error', 'message' => 'User does not exist'], 400);

    //         }

    //     }catch(\Exception $e){
    //         DB::rollback();
    //         return response()->json(['status' => 'error', 'message' => 'An error occurred .' . $e->getMessage()], 500);
    //     }

    // }

    public function deactivate($user_id){


        DB::beginTransaction();

        try{

            $user = User::where('id',$user_id)->first();

            if($user){

                $update_user = $user->update([ "status" => false]);
                DB::commit();
                if($update_user){

                    $message = "Your account has been suspended";

                    Mail::send('emails.accounts', ['name' => $user->full_name,'text'=>$message], function($message) use($user){
                        $message->to($user->email);
                        $message->subject('Account Status');
                    });

                    return response()->json(['status' => 'success','message' => 'User account deactivated successfully'], 200);

                }

            }else{
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'User does not exist'], 400);

            }

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'An error occurred .' . $e->getMessage()], 500);
        }

    }

    public function activate($user_id){


        DB::beginTransaction();

        try{

            $user = User::where('id',$user_id)->first();

            if($user){

                $update_user = $user->update([ "status" => true]);
                DB::commit();
                if($update_user){

                    $message = "Your account has been activated";

                    Mail::send('emails.accounts', ['name' => $user->full_name,'text'=>$message], function($message) use($user){
                        $message->to($user->email);
                        $message->subject('Account Status');
                    });

                    return response()->json(['status' => 'success','message' => 'User account reactivated successfully'], 200);

                }

            }else{
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'User does not exist'], 400);

            }

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'An error occurred .' . $e->getMessage()], 500);
        }

    }

    public function sendMail(Request $request){

        $request->validate([
            'type' => 'required|in:all,seller,buyer',
            'subject' => 'required|string',
            'message' => 'required|string'
        ]);

        try {
            $users = User::where('role', '!=', 'admin')->where('role', '!=', 'super_admin');

            if ($request->type !== 'all') {
                $users->where('role', $request->type);
            }

            $userEmails = $users->pluck('email');

            if ($request->type === 'all') {
                $allUsers = User::whereIn('role', ['seller', 'buyer'])->pluck('email');
                $userEmails = $userEmails->merge($allUsers);
            }

            $subject = $request->subject;

            Mail::send('emails.mail', ['text' => $request->message], function($message) use ($userEmails, $subject) {
                $message->bcc($userEmails->toArray());
                $message->subject($subject);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Message delivered successfully'
            ], 200);

        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }


}
