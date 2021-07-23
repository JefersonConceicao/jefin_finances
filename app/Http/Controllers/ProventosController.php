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

        $data = $proventos->getProventos($request->all());
        return view('proventos.index')
            ->with('dataProventos', $data);
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
    public function show($id)
    {
        //
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
