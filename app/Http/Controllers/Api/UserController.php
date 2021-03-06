<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Auth;

class UserController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = new User;
        
        $data = $user->getUsersApi($request->all());
        return response()->json($data);
    }

    public function store(Request $request){
        $user = new User;

        $data = $user->signUpUser($request->all());
        return response()->json($data);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = new User;

        $data = $user->updateUserAPI($id, $request->all());
        return response()->json($data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $user = new User;
        
        $data = $user->deleteUser($id);
        return response()->json($data);
    }

    public function refreshToken(){
        $token = auth('api')->refresh();
    
        return response()->json([
            'token' => $token,
            'dataUser' => Auth::user()
        ]);
    }
}
