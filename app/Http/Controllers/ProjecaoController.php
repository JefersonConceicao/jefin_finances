<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

//MODELS
use App\Models\DespesaFixa;
use App\Models\ProventoFixo;

//PROJEÇÃO
use App\Projecao\Projecao;

class ProjecaoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        //A PROJEÇÃO SÓ LÊ O QUE ESTÁ GRAVADO: ANTES, COMPLETA A JANELA DAS RECORRÊNCIAS (ADR-0001)
        (new DespesaFixa)->completarJanela($user);
        (new ProventoFixo)->completarJanela($user);

        $projecao = new Projecao($user, $request->input('horizonte'));

        return view('projecao.index')
            ->with('horizonte', $projecao->horizonte())
            ->with('horizontes', Projecao::HORIZONTES)
            ->with('meses', $projecao->meses($this->optionsMeses))
            ->with('alertas', $projecao->alertas());
    }
}
