<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
//REQUEST
use App\Http\Requests\RegisterRequest;
//MODEL
use App\Models\User;

class UsersController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = new User;
        $data = $user->getUsers($request->all());
        return view('users.index')->with('dataUsers', $data);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * @param   App\Http\Requests\RegisterRequest;  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RegisterRequest $request)
    {
        $user = new User;

        $data = $user->signUpUser($request->all());
        return response()->json($data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = new User;
        
        $data = $user->find($id);
        return view('users.edit')->with('user', $data);
    }

    /**
     * @param  App\Http\Requests\RegisterRequest;
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RegisterRequest $request, $id)
    {
        $user = new User;

        $data = $user->updateUser($id, $request->all());
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

    public function profile(){
        $user = Auth::user();

        return view('users.profile')
            ->with('user', $user);
    }   

    public function profileUpdate(RegisterRequest $request){
        $user = Auth::user();

        $data = $user->updateProfile($request->all(), $user);
        return response()->json($data);
    }

    public function changePassword(RegisterRequest $request){
        $user = Auth::user();

        $data = $user->updatePassword($request->all(), $user);
        return response()->json($data);
    }
}
