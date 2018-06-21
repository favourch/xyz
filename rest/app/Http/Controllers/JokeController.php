<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Joke;
use App\Transformers\JokeTransformer;
use Tymon\JWTAuth\Facades\JWTAuth;

class JokeController extends Controller
{
    public function index()
    {
        return fractal(Joke::with('user')->get(), new JokeTransformer)->respond(200);
    }
    
    
    public function store(Request $request)
    {
        $user = JWTAuth::toUser(JWTAuth::getToken());
        
        $joke = $user->jokes()->create([
            'title'=>$request->title,
            'joke' =>$request->joke
                
        ]);
        return fractal($joke, new JokeTransformer)->respond(200);
    }
    
    
    
    public function show($id){
         
        try{
            $joke = Joke::findOrFail($id);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e){
            return response()->json(['error'=>'Joke Not Found'], 404);
        }
        
        return fractal($joke, new JokeTransformer)->respond(200);
    }
    
    
    
    
    
    public function update(Request $request, $id)
    {
        try{
            $joke = Joke::findOrFail($id);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e){
            return response()->json(['error'=>'Joke Not Found'], 404);
        }
        
        $joke->title = $request->title;
        $joke->joke  = $request->joke;
        $joke->save();
        
        return fractal($joke, new JokeTransformer)->respond(200);
    }
    
    
    
    public function destroy($id)
    {
        
    }
}
