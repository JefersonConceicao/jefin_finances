<?php

namespace App\Recorrencia;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Ocultação: as Ocorrências de uma Despesa Fixa ou Provento Fixo oculto somem de todo o app,
 * como se nunca tivessem existido. Registros avulsos (sem recorrência) não são afetados.
 *
 * Só a própria recorrência enxerga as Ocorrências dela quando oculta (a relação ocorrencias() tira o scope),
 * para que Encerrar e Excluir continuem funcionando.
 */
class SemOcorrenciasOcultas implements Scope
{
    private $colunaRecorrencia;
    private $tabelaRecorrencia;

    public function __construct($colunaRecorrencia, $tabelaRecorrencia)
    {
        $this->colunaRecorrencia = $colunaRecorrencia;
        $this->tabelaRecorrencia = $tabelaRecorrencia;
    }

    public function apply(Builder $builder, Model $model)
    {
        $coluna = $model->getTable() . '.' . $this->colunaRecorrencia;
        $tabela = $this->tabelaRecorrencia;

        $builder->where(function($query) use ($coluna, $tabela){
            $query->whereNull($coluna)
                ->orWhereIn($coluna, function($recorrencias) use ($tabela){
                    $recorrencias->select('id')->from($tabela)->where('oculta', 0);
                });
        });
    }
}
