<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

//REQUEST
use App\Http\Requests\LancamentosRequest;
//MODEL
use App\Models\Lancamento;
use App\Models\Proventos;
use App\Models\Despesa;

class LancamentosController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $proventos = new Proventos;
        $lancamento = new Lancamento;

        $totalProventos = $proventos
            ->getProventos($request->all(), $user)
            ->sum('valor_provento');

        $totalLancamentos = $lancamento->getTotalLancamentosByMonth($request->all(), $user);
        $dataLancamentosDespesa = $lancamento->getLancamentosDespesa($request->all(), $user);
        $dataOutrosLancamentos = $lancamento->getOutrosLancamentos($request->all(), $user);

        return view('lancamentos.index')
            ->with('optionsMeses', $this->optionsMeses)
            ->with('totalProventos', $totalProventos)
            ->with('dataLancamentosDespesa', $dataLancamentosDespesa)
            ->with('dataOutrosLancamentos', $dataOutrosLancamentos)
            ->with('totalLancamentos', $totalLancamentos);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $despesa = new Despesa;
        $user = Auth::user();

        return view('lancamentos.create');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LancamentosRequest $request)
    {
        $lancamento = new Lancamento;
        $user = Auth::user();

        $data = $lancamento->saveLancamento($request->all(), $user);
        return response()->json($data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $lancamento = new Lancamento;

        $data = $lancamento->deleteLancamento($id);
        return response()->json($data);
    }

    public function dataGastosGraficos(){

        $user = Auth::user();
        $lancamento = new Lancamento;

        $data = $lancamento
            ->getLancamentosGroupData($user)
            ->groupBy(function($item){
                return converteData($item->data_lancamento, 'Y-m');
            })
            ->take(6);

        return response()->json($data);
    }
}
