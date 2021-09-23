<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
//MODELS
use App\Models\Dividas;
//REQUESTS
use App\Http\Requests\DividasRequest;

class DividasController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $dividas = new Dividas;

        $data = $dividas->getDividas($request->all());
        $sumDividas = $dividas->getTotalValorDivida();

        return view('dividas.index')
            ->with('dataDividas', $data)
            ->with('countDividas', $sumDividas);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('dividas.create');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DividasRequest $request)
    {
        $dividas = new Dividas;

        $data = $dividas->saveDivida($request->all());
        return response()->json($data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   
        $dividas = new Dividas;
        
        $data = $dividas->getDividasByID($id);
        return view('dividas.view')->with('divida', $data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $dividas = new Dividas;

        $data = $dividas->deleteDivida($id);
        return response()->json($data);
    }

    public function declarePayment($id){

        $dividas = new Dividas;

        $data = $dividas->payDebt($id);
        return response()->json($data);
    }
}
