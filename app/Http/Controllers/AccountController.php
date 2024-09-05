<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AccountController extends Controller
{
    //user Registration Page
    public function registration()
    {
        return view('front.account.registration');
    }



    //Submit User
    public function processRegistration(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required'
        ]);

        if($validator->passes())
        {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success', 'You have registerd Successfully.');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

    }

    //user Login Page
    public function login()
    {
        return view('front.account.login');
    }

    //Authenticate User

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->passes())
        {
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password]))
            {
                return redirect()->route('account.profile');
            }
            else
            {
                return redirect()->route('account.login')->with('error', 'Either Email/Password is incorrect.');
            }
        }
        else{
            return redirect()->route('account.login')
            ->withErrors($validator)
            ->withInput($request->only('email'));
        }
    }

    public function profile()
    {
        $user = Auth::user();
        return view('front.account.profile', [
            'user' => $user
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function updateProfile(Request $request)
    {
        $id = Auth::user()->id;
        $validator = Validator::make($request->all(),[
            'name' => 'required|min:3|max:20',
            'email' => 'required|email|unique:users,email,'.$id.',id',

        ]);

        if($validator->passes())
        {
            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->designation = $request->designation;
            $user->mobile = $request->mobile;

            $user->save();

            session()->flash('success', 'Profile updated Successfully!');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        }
        else
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function updateProfilePic(Request $request)
    {
        // Fetch the authenticated user's ID
        $id = Auth::user()->id;

        // Validate the incoming request with stricter rules
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Accept only certain types and limit size to 2MB
        ]);

        // Check if the validation passes
        if ($validator->fails()) {
            // Return a JSON response if validation fails
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        try {
            // Process the image
            $image = $request->file('image');
            $ext = $image->getClientOriginalExtension();
            $imageName = $id . '-' . time() . '.' . $ext;

            // Attempt to move the uploaded file to the desired directory
            $image->move(public_path('profile_pic/'), $imageName);

            //Create a small thumbnail
            $sourcePath = public_path('profile_pic/'.$imageName);
            $manager = new ImageManager(Driver::class);
            $image = $manager->read($sourcePath);

            $image->cover(150,150);
            $image->toPng()->save(public_path('profile_pic/thumb/'.$imageName));

            // Update the user's profile picture in the database
            User::where('id', $id)->update(['image' => $imageName]);

            session()->flash('success', 'Profile picture updated successfully.');

            // Return success response
            return response()->json([
                'status' => true,
                'message' => 'Profile picture updated successfully!',
                'image' => asset('profile_pic/' . $imageName), // Optional: return the image path
            ]);
        } catch (\Exception $e) {
            // Handle any errors during the file move or database update
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the profile picture.',
                'error' => $e->getMessage()
            ]);
        }
    }


}
