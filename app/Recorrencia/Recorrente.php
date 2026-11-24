<?php

namespace App\Recorrencia;

use Carbon\Carbon;
use DB;

/**
 * Comportamento comum de Despesa Fixa e Provento Fixo: Vigência, Ocorrências gravadas antecipadamente
 * (ADR-0001), Reajuste e Encerramento.
 *
 * O model usa as colunas mes_inicio, mes_fim, ocorrencias_ate e valor, e informa como criar, reajustar
 * e excluir a sua Ocorrência. Cada Ocorrência precisa de um método competencia() que devolve o mês dela.
 */
trait Recorrente
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    abstract public function ocorrencias();

    //EXPRESSÃO SQL DO DIA EM QUE A OCORRÊNCIA CONTA (A COMPETÊNCIA É O MÊS DELE)
    abstract protected function colunaCompetencia();

    abstract protected function criarOcorrencia(Ocorrencia $ocorrencia);

    //SOBRESCREVE VALOR E DATA DA OCORRÊNCIA COM OS DA RECORRÊNCIA NAQUELE MÊS
    abstract protected function reajustarOcorrencia($ocorrencia, Ocorrencia $noMes);

    abstract protected function excluirOcorrencia($ocorrencia);

    //DIA DO MÊS DE CADA OCORRÊNCIA
    abstract protected function diaDaOcorrencia();

    //OCORRÊNCIA QUE O REAJUSTE E O ENCERRAMENTO NÃO TOCAM (EX.: DESPESA JÁ PAGA, QUE É HISTÓRICO)
    protected function ocorrenciaPreservada($ocorrencia){
        return false;
    }

    public function vigencia(){
        return new Vigencia($this->mes_inicio, $this->mes_fim, (int) $this->diaDaOcorrencia());
    }

    /**
     * Grava as Ocorrências que faltam: só meses depois da última gravada, nunca meses passados.
     * Com $ate, grava até aquele mês, mesmo além da janela. Chamar dentro de uma transação.
     */
    public function gravarOcorrencias(?Carbon $ate = null){
        $ocorrencias = $this->vigencia()->ocorrenciasAGravar(Carbon::now(), $this->ocorrencias_ate, $ate);

        foreach($ocorrencias as $ocorrencia){
            $this->criarOcorrencia($ocorrencia);
        }

        if(!empty($ocorrencias)){
            $this->ocorrencias_ate = end($ocorrencias)->mes->format('Y-m-d');
            $this->save();
        }

        return count($ocorrencias);
    }

    /**
     * Ocorrências com Competência no mês informado ou depois.
     */
    public function ocorrenciasAPartirDe(Carbon $mes){
        return $this->ocorrencias()
            ->where(DB::raw($this->colunaCompetencia()), '>=', $mes->copy()->startOfMonth()->format('Y-m-d'));
    }

    /**
     * Reajuste: o novo Valor Previsto (e o dia, quando há) vale do mês informado em diante, sobrescrevendo
     * também as Ocorrências ajustadas à mão (menos as preservadas). As Ocorrências anteriores não mudam.
     */
    public function reajustar(Carbon $aPartirDe, $valor = null, $dia = null){
        DB::transaction(function() use ($aPartirDe, $valor, $dia){
            $recorrencia = static::lockForUpdate()->findOrFail($this->id);

            //OS MESES ANTES DO REAJUSTE QUE AINDA NÃO FORAM GRAVADOS FICAM COM O VALOR ANTIGO
            $recorrencia->gravarOcorrencias($aPartirDe->copy()->startOfMonth()->subMonthNoOverflow());

            if($valor !== null){
                $recorrencia->valor = $valor;
            }
            if($dia !== null){
                $recorrencia->dia_vencimento = (int) $dia;
            }
            $recorrencia->save();

            $vigencia = $recorrencia->vigencia();
            foreach($recorrencia->ocorrenciasAPartirDe($aPartirDe)->get() as $ocorrencia){
                if($recorrencia->ocorrenciaPreservada($ocorrencia)){
                    continue;
                }
                $recorrencia->reajustarOcorrencia($ocorrencia, $vigencia->ocorrenciaNoMes($ocorrencia->competencia()));
                $ocorrencia->save();
            }

            $this->setRawAttributes($recorrencia->getAttributes(), true);
        });
    }

    /**
     * Encerramento: o fim da Vigência passa a ser o mês informado, e as Ocorrências depois dele são excluídas
     * (menos as preservadas). Se o fim ficar antes do início e nada sobrar, a recorrência é excluída.
     */
    public function encerrar(Carbon $fim){
        DB::transaction(function() use ($fim){
            $recorrencia = static::lockForUpdate()->findOrFail($this->id);
            $fim = $fim->copy()->startOfMonth();

            foreach($recorrencia->ocorrenciasAPartirDe($fim->copy()->addMonthNoOverflow())->get() as $ocorrencia){
                if($recorrencia->ocorrenciaPreservada($ocorrencia)){
                    //A OCORRÊNCIA PRESERVADA CONTINUA DENTRO DA VIGÊNCIA: O FIM VAI ATÉ ELA
                    if($ocorrencia->competencia()->gt($fim)){
                        $fim = $ocorrencia->competencia();
                    }
                    continue;
                }
                $recorrencia->excluirOcorrencia($ocorrencia);
            }

            //SEM VIGÊNCIA E SEM NENHUMA OCORRÊNCIA PRESERVADA, A RECORRÊNCIA DEIXA DE EXISTIR
            if($fim->lt($recorrencia->mes_inicio)){
                $recorrencia->delete();
                return;
            }

            $recorrencia->mes_fim = $fim->format('Y-m-d');
            //NADA DEPOIS DO FIM FICA MARCADO COMO GRAVADO
            if($recorrencia->ocorrencias_ate && $recorrencia->ocorrencias_ate->gt($fim)){
                $recorrencia->ocorrencias_ate = $fim->format('Y-m-d');
            }
            $recorrencia->save();
            $this->setRawAttributes($recorrencia->getAttributes(), true);
        });
    }

    /**
     * Completa a janela de Ocorrências das recorrências do usuário. Idempotente: abrir a tela de novo não grava nada.
     */
    public function completarJanela($user){
        //A OCULTA NÃO GRAVA NADA ENQUANTO ESTIVER OCULTA; AO DESOCULTAR, A JANELA SE COMPLETA DO MÊS ATUAL EM DIANTE
        //(SEM GERAÇÃO RETROATIVA: MESES QUE PASSARAM ENQUANTO ELA ESTAVA OCULTA E FORA DA JANELA NÃO SÃO GRAVADOS)
        $ids = $this
            ->where('user_id', $user->id)
            ->where('ativo', 1)
            ->where('oculta', 0)
            ->pluck('id');

        $gravadas = 0;
        foreach($ids as $id){
            $gravadas += DB::transaction(function() use ($id){
                //TRAVA A RECORRÊNCIA: DUAS TELAS ABERTAS AO MESMO TEMPO NÃO GRAVAM A MESMA OCORRÊNCIA DUAS VEZES
                return static::lockForUpdate()->find($id)->gravarOcorrencias();
            });
        }

        return $gravadas;
    }

    /**
     * O Encerramento só encurta a Vigência: um fim depois do atual estenderia a recorrência.
     */
    public function fimDepoisDoAtual(Carbon $fim){
        return $this->mes_fim && $fim->copy()->startOfMonth()->gt($this->mes_fim);
    }

    /**
     * Ocultação: esconde (ou mostra de novo) a recorrência e todas as Ocorrências dela, sem alterar mais nada.
     */
    public function ocultar($oculta = true){
        $this->oculta = $oculta ? 1 : 0;
        $this->save();
    }

    public function temOcorrencias(){
        return isset($this->ocorrencias_count)
            ? $this->ocorrencias_count > 0
            : $this->ocorrencias()->exists();
    }
}
