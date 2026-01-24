<?php

namespace Modules\Enquiries\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use App\Services\OTPService;
use Modules\Enquiries\Models\DirectEnquiry;
use Modules\Enquiries\Mail\AdminDirectEnquiryMail;
use Modules\Enquiries\Mail\UserDirectEnquiryConfirmation;
use App\Notifications\AdminDirectEnquiryNotification;
use App\Models\User;

class DirectEnquiryController extends Controller
{
    public function regenerateCaptcha()
    {
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        session(['captcha_answer' => $num1 + $num2]);
        return response()->json(compact('num1','num2'));
    }

   public function sendOtp(Request $request, OTPService $otpService)
    {
        $request->validate(['identifier' => 'required|string']);

        $identifier = $request->identifier;

        // Create guest user if not logged in
        $user = Auth::user() ?? User::firstOrCreate(
            filter_var($identifier, FILTER_VALIDATE_EMAIL) ? ['email' => $identifier] : ['phone' => $identifier],
            ['status' => 'pending_verification']
        );

        // Rate limit check
        $recentOtp = DB::table('user_otps')
            ->where('identifier', $identifier)
            ->where('purpose', 'direct_enquiry')
            ->latest()
            ->first();

        if ($recentOtp && now()->diffInSeconds($recentOtp->created_at) < 500) {
            return response()->json(['message' => 'Please wait before requesting another OTP'], 429);
        }

        $otpService->generate($user->id, $identifier, 'direct_enquiry');

        return response()->json(['success' => true, 'message' => 'OTP sent successfully']);
    }


    public function verifyOtp(Request $request, OTPService $otpService)
    {
        $request->validate(['identifier'=>'required|string','otp'=>'required|digits:4']);
        $user = Auth::user() ?? User::where('email',$request->identifier)->orWhere('phone',$request->identifier)->first();
        if(!$user) return response()->json(['success'=>false],404);

        $verified = $otpService->verify($user->id,$request->identifier,$request->otp,'direct_enquiry');
        if(!$verified) return response()->json(['success'=>false,'message'=>'Invalid or expired OTP'],422);

        DirectEnquiry::where(filter_var($request->identifier, FILTER_VALIDATE_EMAIL) ? 'email':'phone',$request->identifier)
            ->update([filter_var($request->identifier, FILTER_VALIDATE_EMAIL)?'is_email_verified':'is_phone_verified'=>true]);

        return response()->json(['success'=>true]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name'=>'required|min:3',
            'email'=>'required|email',
            'phone'=>'required|digits:10',
            'hoarding_type'=>'required|array|min:1',
            'remarks'=>'required|min:5',
            'captcha'=>'required|numeric',
            'location_city'     => 'string|max:255',
            
        ]);

        if($validator->fails()) return response()->json(['errors'=>$validator->errors()],422);

        if((int)$request->captcha !== (int)session('captcha_answer')) {
            return response()->json(['errors'=>['captcha'=>['Invalid captcha']]],422);
        }

        // Check OTP
        $emailVerified = DB::table('user_otps')->where('identifier',$request->email)->where('purpose','direct_enquiry')->whereNotNull('verified_at')->exists();
        $phoneVerified = DB::table('user_otps')->where('identifier',$request->phone)->where('purpose','direct_enquiry')->whereNotNull('verified_at')->exists();
        if(!$emailVerified || !$phoneVerified) return response()->json(['errors'=>['otp'=>['Verify email and phone first']]],422);

        $data = $validator->validated();
        $data['hoarding_type'] = implode(',',$data['hoarding_type']);
        unset($data['captcha']);

        $enquiry = DirectEnquiry::create([
            ...$data,
            'is_email_verified'=>true,
            'is_phone_verified'=>true
        ]);

        Mail::to($enquiry->email)->queue(new UserDirectEnquiryConfirmation($enquiry));
        Mail::to('admin@oohapp.com')->queue(new AdminDirectEnquiryMail($enquiry));
        Notification::send(User::where('active_role','admin')->get(), new AdminDirectEnquiryNotification($enquiry));
        session()->forget('captcha_answer');

        return response()->json(['success'=>true]);
    }
}
