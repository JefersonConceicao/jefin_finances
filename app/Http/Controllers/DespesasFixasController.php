<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

//REQUEST
use App\Http\Requests\DespesasFixasRequest;

//MODELS
use App\Models\DespesaFixa;
use App\Models\Proventos;
use Carbon\Carbon;

class DespesasFixasController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $despesaFixa = new DespesaFixa;

        $despesaFixa->completarJanela($user);
        $data = $despesaFixa->getDespesasFixas($request->all(), $user)->get();
        $totalVigentes = (float) $despesaFixa->totalVigentes($user);

        //COMPROMETIMENTO DA RENDA COM DESPESAS FIXAS: A MESMA BASE DE PROVENTOS DO CARD DO MÊS ATUAL NA PROJEÇÃO
        $hoje = Carbon::now();
        $proventosDoMes = (float) (new Proventos)
            ->getProventos(['mes' => $hoje->format('m'), 'ano' => $hoje->format('Y')], $user)
            ->sum('valor_provento');

        return view('despesas_fixas.index')
            ->with('dataDespesasFixas', $data)
            ->with('totalVigentes', $totalVigentes)
            ->with('proventosDoMes', $proventosDoMes)
            ->with('comprometimento', $proventosDoMes > 0 ? ($totalVigentes / $proventosDoMes) * 100 : null);
    }

    public function create()
    {
        return view('despesas_fixas.create');
    }

    public function store(DespesasFixasRequest $request)
    {
        $user = Auth::user();
        $despesaFixa = new DespesaFixa;

        $data = $despesaFixa->saveDespesaFixa($request->all(), $user);
        return response()->json($data);
    }

    public function edit($id)
    {
        $user = Auth::user();
        $despesaFixa = new DespesaFixa;

        $data = $despesaFixa->where('user_id', $user->id)->findOrFail($id);

        return view('despesas_fixas.edit')
            ->with('despesaFixa', $data);
    }

    public function update(DespesasFixasRequest $request, $id)
    {
        $user = Auth::user();
        $despesaFixa = new DespesaFixa;

        $data = $despesaFixa->updateDespesaFixa($id, $request->all(), $user);
        return response()->json($data);
    }

    public function delete($id)
    {
        $user = Auth::user();
        $despesaFixa = new DespesaFixa;

        $data = $despesaFixa->deleteDespesaFixa($id, $user);
        return response()->json($data);
    }

    public function reajustar(DespesasFixasRequest $request, $id)
    {
        $despesaFixa = new DespesaFixa;

        $data = $despesaFixa->reajustarDespesaFixa($id, $request->all(), Auth::user());
        return response()->json($data);
    }

    public function encerrar(DespesasFixasRequest $request, $id)
    {
        $despesaFixa = new DespesaFixa;

        $data = $despesaFixa->encerrarDespesaFixa($id, $request->all(), Auth::user());
        return response()->json($data);
    }

    /**
     * Ocultação: oculta = 1 esconde a recorrência e todas as Ocorrências dela; oculta = 0 traz tudo de volta.
     */
    public function ocultar(Request $request, $id)
    {
        $recorrencia = (new DespesaFixa)->doUsuario($id, Auth::user());
        $oculta = (bool) $request->input('oculta', 1);

        $recorrencia->ocultar($oculta);

        return response()->json([
            'error' => false,
            'msg' => $oculta ? 'Despesa fixa ocultada' : 'Despesa fixa visível de novo',
        ]);
    }
}
