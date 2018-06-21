<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Tymon\JWTAuth\Facades\JWTAuth;


use App\User;

use \Tymon\JWTAuth\Exceptions\JWTException;

class ApiAuthController extends Controller
{
    public function authenticate(Request $request) 
    {
        
        $credentials = $request->only('email', 'password');
        
        try{
            $token = JWTAuth::attempt($credentials);
            
            if(!$token){
                return response()->json(['error'=>'invalid credentials'], 401); 
            }
            
        } catch (JWTException $e){
            return response()->json(['error'=>'Somethig went wrong'], 500); 
        }
        
        return response()->json(['token'=>$token], 200); 
    }
    
    
    
    public function register(Request $request)
    {
        $name = request()->name;
        $email = request()->email;
        $password = request()->password;
        
        $user = User::create([
                    'name' => $name,
                    'email'=>$email,
                    'password' => bcrypt($password) 
                ]);
                
        $token = JWTAuth::fromUser($user);

        return response()->json(['token'=>$token], 200);
    }
}
