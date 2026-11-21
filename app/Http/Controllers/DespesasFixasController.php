<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

//REQUEST
use App\Http\Requests\DespesasFixasRequest;

//MODELS
use App\Models\DespesaFixa;

class DespesasFixasController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $despesaFixa = new DespesaFixa;

        $despesaFixa->completarJanela($user);
        $data = $despesaFixa->getDespesasFixas($request->all(), $user)->get();

        return view('despesas_fixas.index')
            ->with('dataDespesasFixas', $data)
            ->with('totalBasal', $despesaFixa->totalAtivas($user));
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
}
