<?php

namespace App\Projecao;

use Carbon\Carbon;
use App\Models\Despesa;
use App\Models\DespesaFixa;
use App\Models\Proventos;
use App\Models\ProventoFixo;

/**
 * A Projeção mês a mês, do mês atual até o Horizonte. Só lê o que está gravado
 * (Ocorrências, Despesas Variáveis e Proventos), então os ajustes feitos em meses futuros aparecem nela.
 */
class Projecao
{
    const HORIZONTES = [3, 6, 12];
    const HORIZONTE_PADRAO = 6;

    private $user;
    private $horizonte;
    private $mesAtual;
    private $despesasFixas = null;
    private $proventosFixos = null;
    private $recorrencias = null;
    private $nomesDosMeses = [];
    private $totaisPorMes = [];

    public function __construct($user, $horizonte = null, Carbon $hoje = null)
    {
        $this->user = $user;
        $this->horizonte = self::horizonteValido($horizonte);
        $this->mesAtual = ($hoje ?: Carbon::now())->copy()->startOfMonth();
    }

    //UM HORIZONTE FORA DE 3, 6 OU 12 VOLTA PARA O PADRÃO
    public static function horizonteValido($horizonte)
    {
        return in_array((int) $horizonte, self::HORIZONTES, true) ? (int) $horizonte : self::HORIZONTE_PADRAO;
    }

    public function horizonte()
    {
        return $this->horizonte;
    }

    public function mesAtual()
    {
        return $this->mesAtual->copy();
    }

    //ÚLTIMO MÊS DA PROJEÇÃO
    public function ultimoMes()
    {
        return $this->mesAtual->copy()->addMonthsNoOverflow($this->horizonte);
    }

    public function meses($nomesDosMeses)
    {
        $despesa = new Despesa;
        $proventos = new Proventos;

        $meses = [];
        $saldoAcumulado = 0;

        for($i = 0; $i <= $this->horizonte; $i++){
            $data = $this->mesAtual->copy()->addMonthsNoOverflow($i);
            $filtro = ['mes' => $data->format('m'), 'ano' => $data->format('Y')];
            $isMesAtual = $i === 0;

            $totalProventos = (float) $proventos->getProventos($filtro, $this->user)->sum('valor_provento');

            $variaveis = $despesa->getDespesas($filtro, $this->user)->whereNull('despesa_fixa_id')->get();
            $totalVariaveis = (float) $variaveis->sum('valor_total');
            $totalFixas = (float) $despesa->getDespesas($filtro, $this->user)->whereNotNull('despesa_fixa_id')->sum('valor_total');

            $totalDespesas = $totalFixas + $totalVariaveis;
            $saldo = $totalProventos - $totalDespesas;
            //O SALDO ACUMULADO COMEÇA DO ZERO NO MÊS ATUAL
            $saldoAcumulado += $saldo;

            $mes = [
                'mes' => $filtro['mes'],
                'ano' => $filtro['ano'],
                'label' => $nomesDosMeses[$filtro['mes']] . '/' . $filtro['ano'],
                'isMesAtual' => $isMesAtual,
                'proventos' => $totalProventos,
                'fixas' => $totalFixas,
                'variaveis' => $totalVariaveis,
                'listaVariaveis' => $variaveis,
                'total' => $totalDespesas,
                'saldo' => $saldo,
                'saldoAcumulado' => $saldoAcumulado,
                'percentual' => $totalProventos > 0 ? ($totalDespesas / $totalProventos) * 100 : null,
            ];

            $mes['marcos'] = $this->marcosDoMes($data);

            if($isMesAtual){
                $mes['aPagar'] = (float) $despesa->getDespesas($filtro, $this->user)->where('pago', 0)->sum('valor_total');
                $mes['pago'] = $totalDespesas - $mes['aPagar'];
            }

            $meses[] = $mes;
        }

        return $meses;
    }

    /**
     * Distância em meses do mês atual até o mês informado; null se estiver fora da Projeção.
     */
    private function distancia(?Carbon $mes)
    {
        if(!$mes){
            return null;
        }

        $mes = $mes->copy()->startOfMonth();
        if($mes->lt($this->mesAtual) || $mes->gt($this->ultimoMes())){
            return null;
        }

        return (int) $this->mesAtual->diffInMonths($mes);
    }

    private function despesasFixas()
    {
        if($this->despesasFixas === null){
            $this->despesasFixas = DespesaFixa::where('user_id', $this->user->id)->where('ativo', 1)->where('oculta', 0)->get();
        }

        return $this->despesasFixas;
    }

    private function proventosFixos()
    {
        if($this->proventosFixos === null){
            $this->proventosFixos = ProventoFixo::where('user_id', $this->user->id)->where('ativo', 1)->where('oculta', 0)->get();
        }

        return $this->proventosFixos;
    }

    /**
     * O que começa e o que termina: a recorrência no mês de início ou no mês de fim da Vigência.
     * Despesa Fixa com fim mostra a última parcela ("Cartão (10/10) termina").
     */
    private function recorrencias()
    {
        if($this->recorrencias !== null){
            return $this->recorrencias;
        }

        $recorrencias = [];

        foreach($this->despesasFixas() as $fixa){
            $recorrencias[] = [
                'despesa' => true,
                'nome' => $fixa->nome_despesa,
                'nomeNoFim' => $this->nomeNoFim($fixa),
                'valor' => (float) $fixa->valor,
                'inicio' => $fixa->mes_inicio,
                'fim' => $fixa->mes_fim,
            ];
        }

        foreach($this->proventosFixos() as $fixo){
            $recorrencias[] = [
                'despesa' => false,
                'nome' => $fixo->descricao,
                'nomeNoFim' => $fixo->descricao,
                'valor' => (float) $fixo->valor,
                'inicio' => $fixo->mes_inicio,
                'fim' => $fixo->mes_fim,
            ];
        }

        return $this->recorrencias = $recorrencias;
    }

    /**
     * Nome da Despesa Fixa no mês de fim: o da Ocorrência gravada nele, que mostra a parcela
     * ("Cartão (10/10)"; "Cartão (4/10)" se foi encerrada antes). Sem Ocorrência, o calculado pela Vigência.
     */
    private function nomeNoFim(DespesaFixa $fixa)
    {
        if(!$fixa->mes_fim){
            return $fixa->nome_despesa;
        }

        $ultima = $fixa->ocorrenciasAPartirDe($fixa->mes_fim)
            ->where('data_vencimento', '<=', $fixa->mes_fim->copy()->endOfMonth()->format('Y-m-d'))
            ->value('nome_despesa');

        return $ultima ?: $fixa->vigencia()->ocorrenciaNoMes($fixa->mes_fim)->nome($fixa->nome_despesa);
    }

    private function marcosDoMes(Carbon $mes)
    {
        $marcos = [];

        foreach($this->recorrencias() as $recorrencia){
            //EFEITO NO FLUXO: DESPESA QUE COMEÇA OU PROVENTO QUE TERMINA PEDE ATENÇÃO; O CONTRÁRIO É GANHO
            if($recorrencia['inicio']->format('Y-m') === $mes->format('Y-m')){
                $marcos[] = [
                    'tipo' => 'comeca',
                    'descricao' => "{$recorrencia['nome']} começa",
                    'efeito' => $recorrencia['despesa'] ? 'atencao' : 'ganho',
                ];
            }
            if($recorrencia['fim'] && $recorrencia['fim']->format('Y-m') === $mes->format('Y-m')){
                $marcos[] = [
                    'tipo' => 'termina',
                    'descricao' => "{$recorrencia['nomeNoFim']} termina",
                    'efeito' => $recorrencia['despesa'] ? 'ganho' : 'atencao',
                ];
            }
        }

        return $marcos;
    }

    private static function emMeses($distancia)
    {
        return $distancia === 1 ? 'Em 1 mês' : "Em {$distancia} meses";
    }

    private static function reais($valor)
    {
        return 'R$ ' . convertValorReal($valor);
    }

    private function nomeDoMes(Carbon $mes)
    {
        return $this->nomesDosMeses[$mes->format('m')] . '/' . $mes->format('Y');
    }

    /**
     * Total de Despesas Fixas e de Proventos gravados num mês (para o "total atualizado" do Alerta).
     */
    private function totaisDoMes(Carbon $mes)
    {
        $chave = $mes->format('Y-m');

        if(!isset($this->totaisPorMes[$chave])){
            $filtro = ['mes' => $mes->format('m'), 'ano' => $mes->format('Y')];
            $this->totaisPorMes[$chave] = [
                'fixas' => (float) (new Despesa)->getDespesas($filtro, $this->user)->whereNotNull('despesa_fixa_id')->sum('valor_total'),
                'proventos' => (float) (new Proventos)->getProventos($filtro, $this->user)->sum('valor_provento'),
            ];
        }

        return $this->totaisPorMes[$chave];
    }

    /**
     * Como fica o total afetado por um Marco: no próprio mês, quando começa; no mês seguinte, quando termina
     * (o mês de fim ainda conta a última Ocorrência).
     */
    private function totalAtualizado(array $recorrencia, $comeca)
    {
        $mes = $comeca ? $recorrencia['inicio']->copy()->startOfMonth() : $recorrencia['fim']->copy()->startOfMonth()->addMonthNoOverflow();
        $chave = $recorrencia['despesa'] ? 'fixas' : 'proventos';
        $rotulo = $recorrencia['despesa'] ? 'Despesas Fixas' : 'Proventos';
        $quando = $comeca ? '' : 'em ' . $this->nomeDoMes($mes) . ', ';

        return " · {$quando}{$rotulo} passam a " . self::reais($this->totaisDoMes($mes)[$chave]);
    }

    private function alerta($tipo, Carbon $mes, $distancia, $valor, $descricao)
    {
        return [
            'tipo' => $tipo,
            'mes' => $mes->format('Y-m'),
            'distancia' => $distancia,
            'quando' => self::emMeses($distancia) . ' (' . $this->nomeDoMes($mes) . ')',
            'valor' => (float) $valor,
            'descricao' => $descricao,
        ];
    }

    /**
     * Alertas dos meses futuros da Projeção, do mais próximo ao mais distante.
     * Ganho: Despesa Fixa termina ou Provento Fixo começa. Atenção: Despesa Fixa começa, Provento Fixo termina
     * ou Despesa Variável com Competência futura. O valor de uma recorrência é o Valor Previsto dela.
     */
    public function alertas($nomesDosMeses)
    {
        $this->nomesDosMeses = $nomesDosMeses;
        $alertas = [];

        foreach($this->recorrencias() as $r){
            $distancia = $this->distancia($r['inicio']);
            if($distancia){
                $alertas[] = $r['despesa']
                    ? $this->alerta('atencao', $r['inicio'], $distancia, $r['valor'],
                        "Começa {$r['nome']}: -" . self::reais($r['valor']) . $this->totalAtualizado($r, true))
                    : $this->alerta('ganho', $r['inicio'], $distancia, $r['valor'],
                        "Começa {$r['nome']}: +" . self::reais($r['valor']) . $this->totalAtualizado($r, true));
            }

            $distancia = $this->distancia($r['fim']);
            if($distancia){
                $alertas[] = $r['despesa']
                    ? $this->alerta('ganho', $r['fim'], $distancia, $r['valor'],
                        "Termina {$r['nomeNoFim']}: seu fluxo melhora em " . self::reais($r['valor']) . $this->totalAtualizado($r, false))
                    : $this->alerta('atencao', $r['fim'], $distancia, $r['valor'],
                        "Termina {$r['nomeNoFim']}: -" . self::reais($r['valor']) . $this->totalAtualizado($r, false));
            }
        }

        //DESPESAS VARIÁVEIS COM COMPETÊNCIA NOS MESES FUTUROS DA PROJEÇÃO
        $variaveis = Despesa::where('user_id', $this->user->id)
            ->whereNull('despesa_fixa_id')
            ->whereNotNull('data_vencimento')
            ->where('data_vencimento', '>=', $this->mesAtual->copy()->addMonthNoOverflow()->format('Y-m-d'))
            ->where('data_vencimento', '<=', $this->ultimoMes()->endOfMonth()->format('Y-m-d'))
            ->orderBy('data_vencimento')
            ->get();

        foreach($variaveis as $variavel){
            $mes = $variavel->competencia();
            $distancia = $this->distancia($mes);
            $alertas[] = $this->alerta('atencao', $mes, $distancia, $variavel->valor_total,
                "Vence {$variavel->nome_despesa}: -" . self::reais($variavel->valor_total));
        }

        usort($alertas, function($a, $b){
            return $a['distancia'] <=> $b['distancia'];
        });

        return $alertas;
    }

    /**
     * Os Alertas agrupados por mês. Mês com mais de um Alerta ganha o resumo: quanto entra, quanto sai
     * e o efeito líquido no fluxo.
     */
    public static function alertasPorMes(array $alertas)
    {
        $grupos = [];

        foreach($alertas as $alerta){
            if(!isset($grupos[$alerta['mes']])){
                $grupos[$alerta['mes']] = ['mes' => $alerta['mes'], 'quando' => $alerta['quando'], 'alertas' => [], 'entra' => 0.0, 'sai' => 0.0];
            }

            $grupos[$alerta['mes']]['alertas'][] = $alerta;
            $grupos[$alerta['mes']][$alerta['tipo'] === 'ganho' ? 'entra' : 'sai'] += $alerta['valor'];
        }

        return array_values(array_map(function($grupo){
            $grupo['liquido'] = $grupo['entra'] - $grupo['sai'];
            $grupo['resumo'] = count($grupo['alertas']) > 1
                ? 'Efeito no fluxo: ' . ($grupo['liquido'] < 0 ? '-' : '+') . self::reais(abs($grupo['liquido']))
                    . ' (entra ' . self::reais($grupo['entra']) . ', sai ' . self::reais($grupo['sai']) . ')'
                : null;

            return $grupo;
        }, $grupos));
    }
}
