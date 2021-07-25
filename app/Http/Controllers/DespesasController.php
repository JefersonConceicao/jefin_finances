<?php

namespace App\Http\Controllers;
use Auth;
use Illuminate\Http\Request;
//REQUEST 
use App\Http\Requests\DespesasRequest;
//MODELS
use App\Models\Despesa;
use App\Models\TiposDespesa; 

class DespesasController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $despesa = new Despesa;
        $tipoDespesa = new TiposDespesa;
        $user = Auth::user();

        $data = $despesa->getDespesas($request->all(), $user)->get();

        $totalValor = $despesa->getDespesas($request->all(), $user)->sum('valor_total');
        $totalFixa  =$despesa->getDespesas($request->all(), $user)->where('despesa_tipo_id' ,1)->sum('valor_total');
        $totalVariavel =  $despesa->getDespesas($request->all(), $user)->where('despesa_tipo_id' ,3)->sum('valor_total');
        $optionsTipoDesesa = $tipoDespesa->pluck('nome', 'id');

        return view('despesas.index')
            ->with('dataDespesas', $data)
            ->with('optionsMeses', $this->optionsMeses)
            ->with('optionsTipoDespesa', $optionsTipoDesesa)
            ->with('totalValor', $totalValor)
            ->with('totalFixa', $totalFixa)
            ->with('totalVariavel', $totalVariavel);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function create()
    {       
        $tipoDespesa = new TiposDespesa;

        $optionsTipoDespesa = $tipoDespesa->pluck('nome', 'id');
        return view('despesas.create')
            ->with('optionsTipoDespesa', $optionsTipoDespesa);
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
        $tipoDespesa = new TiposDespesa;
        $despesa = new Despesa; 

        $data = $despesa->find($id);
        $optionsTipoDesesa = $tipoDespesa->pluck('nome', 'id');

        return view('despesas.edit')
            ->with('despesa', $data)
            ->with('optionsTipoDespesa', $optionsTipoDesesa);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(DespesasRequest $request, $id)
    {
        $despesa = new Despesa;
        
        $data = $despesa->updateDespesa($id, $request->all());
        return response()->json($data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $despesa = new Despesa;

        $data = $despesa->deleteDespesa($id);
        return response()->json($data);
    }

    public function delcararPagamentoDespesa($id){
        $despesa = new Despesa;

        $data = $despesa->pagamentoDespesa($id);
        return response()->json($data);
    }

    public function copyDespesas(Request $request){
        $despesa = new Despesa;
        
        $data = $despesa->copyDespesas($request->all());
        return response()->json($data);
    }
}
