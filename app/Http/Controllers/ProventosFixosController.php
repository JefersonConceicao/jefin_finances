<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

//REQUEST
use App\Http\Requests\ProventosFixosRequest;

//MODELS
use App\Models\ProventoFixo;

class ProventosFixosController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $proventoFixo = new ProventoFixo;

        $proventoFixo->completarJanela($user);

        return view('proventos_fixos.index')
            ->with('dataProventosFixos', $proventoFixo->getProventosFixos($user, $request->boolean('mostrar_ocultas'))->get());
    }

    public function create()
    {
        return view('proventos_fixos.create');
    }

    public function store(ProventosFixosRequest $request)
    {
        $data = (new ProventoFixo)->saveProventoFixo($request->all(), Auth::user());
        return response()->json($data);
    }

    public function edit($id)
    {
        return view('proventos_fixos.edit')
            ->with('proventoFixo', (new ProventoFixo)->doUsuario($id, Auth::user()));
    }

    public function update(ProventosFixosRequest $request, $id)
    {
        $data = (new ProventoFixo)->updateProventoFixo($id, $request->all(), Auth::user());
        return response()->json($data);
    }

    public function delete($id)
    {
        $data = (new ProventoFixo)->deleteProventoFixo($id, Auth::user());
        return response()->json($data);
    }

    public function reajustar(ProventosFixosRequest $request, $id)
    {
        $data = (new ProventoFixo)->reajustarProventoFixo($id, $request->all(), Auth::user());
        return response()->json($data);
    }

    public function encerrar(ProventosFixosRequest $request, $id)
    {
        $data = (new ProventoFixo)->encerrarProventoFixo($id, $request->all(), Auth::user());
        return response()->json($data);
    }

    /**
     * Ocultação: oculta = 1 esconde a recorrência e todas as Ocorrências dela; oculta = 0 traz tudo de volta.
     */
    public function ocultar(Request $request, $id)
    {
        $recorrencia = (new ProventoFixo)->doUsuario($id, Auth::user());
        $oculta = (bool) $request->input('oculta', 1);

        $recorrencia->ocultar($oculta);

        return response()->json([
            'error' => false,
            'msg' => $oculta ? 'Provento fixo ocultado' : 'Provento fixo visível de novo',
        ]);
    }
}
