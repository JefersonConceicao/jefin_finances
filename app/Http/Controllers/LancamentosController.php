<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

//REQUEST
use App\Http\Requests\LancamentosRequest;
//MODEL
use App\Models\Lancamento;
use App\Models\Proventos;

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

        $dataLancamentos = $lancamento->getLancamentos($request->all(), $user);
        $dataLancamentosDespesa = $lancamento->getLancamentosDespesa($request->all(), $user);
        $dataOutrosLancamentos = $lancamento->getOutrosLancamentos($request->all(), $user);

        return view('lancamentos.index')
            ->with('optionsMeses', $this->optionsMeses)
            ->with('totalProventos', $totalProventos)
            ->with('dataLancamentos', $dataLancamentos)
            ->with('dataLancamentosDespesa', $dataLancamentosDespesa)
            ->with('dataOutrosLancamentos', $dataOutrosLancamentos);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('lancamentos.create');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
