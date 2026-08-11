<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

//MODELS
use App\Models\Despesa;
use App\Models\Proventos; 

class ProventosController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $proventos = new Proventos;

        $data = $proventos->getProventos($request->all(), $user)->get();
        return response()->json($data);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $user = Auth::user();
        $proventos = new Proventos;

        $data = $proventos->saveProvento($request->all(), $user);
        return response()->json($data);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $proventos = new Proventos;

        $data = $proventos->updateProvento($id, $request->all());
        return response()->json($data);
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $user = Auth::user();
        $proventos = new Proventos;

        $data = $proventos->deleteProvento($id);
        return response()->json($data);
    }

    /**
     * Retorna o saldo restante do mês/ano informado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saldoMes(Request $request)
    {
        $user = Auth::user();
        $mes = $request->input('mes');
        $ano = $request->input('ano');

        if(empty($mes) || empty($ano)){
            return response()->json([
                'error' => true,
                'msg' => 'Os parâmetros mes e ano são obrigatórios.'
            ], 400);
        }

        if(!is_numeric($mes) || !is_numeric($ano) || $mes < 1 || $mes > 12){
            return response()->json([
                'error' => true,
                'msg' => 'Os parâmetros mes e ano devem ser valores numéricos válidos.'
            ], 400);
        }

        $mes = intval($mes);
        $ano = intval($ano);

        $totalProventos = Proventos::where('user_id', $user->id)
            ->whereMonth('data_provento', $mes)
            ->whereYear('data_provento', $ano)
            ->sum('valor_provento');

        $totalDespesas = Despesa::where('user_id', $user->id)
            ->whereMonth('created_at', $mes)
            ->whereYear('created_at', $ano)
            ->sum('valor_total');

        return response()->json([
            'error' => false,
            'mes' => $mes,
            'ano' => $ano,
            'total_proventos' => floatval($totalProventos),
            'total_despesas' => floatval($totalDespesas),
            'saldo_restante' => floatval($totalProventos - $totalDespesas)
        ]);
    }
}
