<?php

namespace App\Http\Controllers;
use Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

//MODELS
use App\Models\Despesa;
use App\Models\Proventos;
use App\Models\Lancamento;

class HomeController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        $user = Auth::user();
        $despesa = new Despesa;
        $proventos = new Proventos;
        $lancamentos = new Lancamento;
        $now = Carbon::now();

        $countDespesas = $lancamentos->getLancamentos([], $user)->sum('valor');
        $countProventos = $proventos->getProventos([], $user)->sum('valor_provento');
        $daysActive = $now->diff(Auth::user()->created_at)->d;
        $ultimosLancamentos = $lancamentos->getUltimosLancamentos($user);

        return view('home.index')
            ->with('totalDespesas', $countDespesas)
            ->with('totalProventos', $countProventos)
            ->with('diasAtividade', $daysActive)
            ->with('dataUltimosLancamentos', $ultimosLancamentos);
    }
}
