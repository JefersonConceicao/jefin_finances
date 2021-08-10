<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//MODELS
use App\Models\TiposDespesa;

class TiposDespesasController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tiposDespesa = new TiposDespesa;

        $data = $tiposDespesa->getTiposDespesas($request->all());
        return response()->json($data);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $tipoDespesa = new TiposDespesa;

        $data = $tipoDespesa->saveTiposDespesa($request->all());
        return response()->json($data);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $tipoDespesa = new TiposDespesa;

        $data = $tipoDespesa->updateTiposDespesa($id, $request->all());
        return response()->json($data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $tipoDespesa = new TiposDesepsa;

        $data = $tipoDespesa->deleteTipoDespesa($id);
        return response()->json($data);
    }
}
