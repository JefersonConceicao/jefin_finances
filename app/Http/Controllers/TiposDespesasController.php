<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//REQUEST 
use App\Http\Requests\TipoDespesasRequest;
//MODEL
use App\Models\TiposDespesa;

class TiposDespesasController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tiposDespesa = new TiposDespesa;

        $data = $tiposDespesa->getTiposDespesas();

        return view('tipo_despesa.index')
            ->with('dataTiposDespesa', $data);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tipo_despesa.create');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TipoDespesasRequest $request)
    {
        $tiposDespesa = new TiposDespesa;

        $data = $tiposDespesa->saveTiposDespesa($request->all());
        return response()->json($data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tiposDespesa = new TiposDespesa;

        $data = $tiposDespesa->find($id);

        return view('tipo_despesa.edit')
            ->with('tipoDespesa', $data);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $tiposDespesa = new TiposDespesa;

        $data = $tiposDespesa->updateTiposDespesa($id, $request->all());
        return response()->json($data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $tiposDespesa = new TiposDespesa;

        $data = $tiposDespesa->deleteTipoDespesa($id);
        return response()->json($data);
    }
}