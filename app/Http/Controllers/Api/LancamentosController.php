<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

//MODELS
use App\Models\Lancamento;

class LancamentosController extends Controller
{
    public function index(Request $request){
        $user = Auth::user();
        $lancamento = new Lancamento; 

        if(isset($request->despesa) && $request->despesa){
            $data = $lancamento->getLancamentosDespesa($request->all(), $user);
        }else{
            $data = $lancamento->getOutrosLancamentos($request->all(), $user);
        }
    
        return response()->json($data);
    }

    public function store(Request $request){
        $user = Auth::user();
        $lancamento = new Lancamento;

        $data = $lancamento->saveLancamento($request->all(), $user);
        return response()->json($data);
    }
}
