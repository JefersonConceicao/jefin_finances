<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//REQUEST
use App\Http\Requests\ProventosRequest;
//MODEL
use App\Models\Proventos;
use Auth;

class ProventosController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $proventos = new Proventos;
        $user = Auth::user(); 

        $data = $proventos->getProventos($request->all(), $user)->get();

        return view('proventos.index')
            ->with('dataProventos', $data)
            ->with('optionsMeses', $this->optionsMeses);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('proventos.create');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProventosRequest $request)
    {
        $proventos = new Proventos;
        $user = Auth::user();

        $data = $proventos->saveProvento($request->all(), $user);
        return response()->json($data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $proventos = new Proventos;

        $data = $proventos->find($id);
        return view('proventos.edit')   
            ->with('provento', $data);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProventosRequest $request, $id)
    {
        $proventos = new Proventos;

        $data = $proventos->updateProvento($id, $request->all());
        return response()->json($data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $proventos = new Proventos;

        $data = $proventos->deleteProvento($id);
        return response()->json($data);
    }

    public function copyProventos(Request $request){
        $proventos = new Proventos; 

        $data = $proventos->copyLastMonth($request->all());
        return response()->json($data);
    }
}
