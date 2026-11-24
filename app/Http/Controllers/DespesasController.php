<?php

namespace App\Http\Controllers;
use Auth;
use Illuminate\Http\Request;

//REQUEST
use App\Http\Requests\DespesasRequest;

//MODELS
use App\Models\Despesa;
use App\Models\DespesaFixa;
use App\Models\Proventos;
use Illuminate\View\View;

class DespesasController extends Controller
{
    /**
     * @return View
     */
    public function index(Request $request)
    {
        $despesa = new Despesa;
        $proventos = new Proventos;
        $despesaFixa = new DespesaFixa;
        $user = Auth::user();

        //COMPLETA A JANELA DE OCORRÊNCIAS DAS DESPESAS FIXAS (ADR-0001)
        $despesaFixa->completarJanela($user);

        $data = $despesa->getDespesas($request->all(), $user)->orderBy('valor_total','DESC')->get();

        $totalValor = $despesa
            ->getDespesas($request->all(), $user)
            ->sum('valor_total');

        $totalFixas = $despesa
            ->getDespesas($request->all(), $user)
            ->whereNotNull('despesa_fixa_id')
            ->sum('valor_total');

        $totalValorDividasAtivas = $despesa
            ->getDespesas($request->all(), $user)
            ->where('pago', '=', 0)
            ->sum('valor_total');

        $totalProventos = $proventos
            ->getProventos($request->all(), $user)
            ->sum('valor_provento');

        return view('despesas.index')
            ->with('dataDespesas', $data)
            ->with('optionsMeses', $this->optionsMeses)
            ->with('totalValor', $totalValor)
            ->with('totalFixas', $totalFixas)
            ->with('totalVariaveis', $totalValor - $totalFixas)
            ->with('totalDividasAtivas', $totalValorDividasAtivas)
            ->with('totalProventos', $totalProventos);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('despesas.create');
    }
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DespesasRequest $request)
    {
        $user = Auth::user();
        $despesa = new Despesa;

        $data = $despesa->saveDespesa($request->all(), $user);
        return response()->json($data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Auth::user();
        $despesa = new Despesa;

        $data = $despesa->doUsuario($id, $user);

        return view('despesas.edit')
            ->with('despesa', $data);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(DespesasRequest $request, $id)
    {
        $despesa = new Despesa;

        $data = $despesa->updateDespesa($id, $request->all(), Auth::user());
        return response()->json($data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $despesa = new Despesa;

        $escopo = request('escopo') === Despesa::ESCOPO_SEGUINTES ? Despesa::ESCOPO_SEGUINTES : Despesa::ESCOPO_SO_ESTA;
        $data = $despesa->deleteDespesa($id, Auth::user(), $escopo);
        return response()->json($data);
    }

    public function delcararPagamentoDespesa($id){
        $user = Auth::user();
        $despesa = new Despesa;

        $data = $despesa->pagamentoDespesa($id, $user);
        return response()->json($data);
    }
}
