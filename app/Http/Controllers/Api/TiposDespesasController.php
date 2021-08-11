<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
        $tiposDespesa = new TiposDespesa;

        $data = $tiposDespesa->deleteTipoDespesa($id);
        return response()->json($data);
    }
}
