<?php

namespace App\Models;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use App\Recorrencia\Vigencia;

class DespesaFixa extends Model
{
    protected $table = 'despesas_fixas';
    protected $fillable = [
        'user_id',
        'nome_despesa',
        'despesa_tipo_id',
        'valor',
        'ativo',
        'mes_inicio',
        'mes_fim',
        'dia_vencimento',
    ];

    protected $casts = [
        'mes_inicio' => 'date',
        'mes_fim' => 'date',
        'ocorrencias_ate' => 'date',
    ];

    public function despesaTipo(){
        return $this->hasOne(TiposDespesa::class, 'id', 'despesa_tipo_id');
    }

    public function despesas(){
        return $this->hasMany(Despesa::class, 'despesa_fixa_id', 'id');
    }

    public function getDespesasFixas($request = [], $user){
        $conditions = [];
        $conditions[] = ['user_id', '=', $user->id];

        if(isset($request['nome_despesa']) && !empty($request['nome_despesa'])){
            $conditions[] = ['nome_despesa', 'LIKE', "%".$request['nome_despesa']."%"];
        }

        if(isset($request['despesa_tipo_id']) && !empty($request['despesa_tipo_id'])){
            $conditions[] = ['despesa_tipo_id', '=', $request['despesa_tipo_id']];
        }

        if(isset($request['ativo']) && $request['ativo'] !== ''){
            $conditions[] = ['ativo', '=', $request['ativo']];
        }

        return $this
            ->where($conditions)
            ->with('despesaTipo')
            ->withCount('despesas')
            ->orderBy('ativo', 'DESC')
            ->orderBy('valor', 'DESC');
    }

    public function saveDespesaFixa($request = [], $user){
        try{
            //A FIXA E TODAS AS OCORRÊNCIAS DELA SÃO GRAVADAS JUNTAS: SE UMA FALHAR, NADA FICA
            DB::transaction(function() use ($request, $user){
                $inicio = Carbon::createFromFormat('!Y-m', $request['mes_inicio']);

                if(!empty($request['parcelas'])){
                    $fim = Vigencia::fimPorParcelas($inicio, (int) $request['parcelas']);
                }else{
                    $fim = !empty($request['mes_fim']) ? Carbon::createFromFormat('!Y-m', $request['mes_fim']) : null;
                }

                $this->fill([
                    'user_id' => $user->id,
                    'nome_despesa' => $request['nome_despesa'],
                    'valor' => setToDecimal($request['valor']),
                    'ativo' => $request['ativo'] ?? 1,
                    'despesa_tipo_id' => (new TiposDespesa)->idTipoFixaDoUsuario($user),
                    'mes_inicio' => $inicio->format('Y-m-d'),
                    'mes_fim' => $fim ? $fim->format('Y-m-d') : null,
                    'dia_vencimento' => (int) $request['dia_vencimento'],
                ])->save();

                $this->gravarOcorrencias();
            });

            return [
                'error' => false,
                'msg' => 'Despesa fixa adicionada!'
            ];
        }catch(\Exception $error){
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => 'Não foi possível salvar o registro, tente de novo'
            ];
        }
    }

    public function vigencia(){
        return new Vigencia($this->mes_inicio, $this->mes_fim, (int) $this->dia_vencimento);
    }

    /**
     * Grava as Ocorrências que faltam (ADR-0001): só meses depois da última gravada, nunca meses passados.
     * Chamar dentro de uma transação.
     */
    public function gravarOcorrencias(){
        $ocorrencias = $this->vigencia()->ocorrenciasAGravar(Carbon::now(), $this->ocorrencias_ate);

        foreach($ocorrencias as $ocorrencia){
            Despesa::create([
                'user_id' => $this->user_id,
                'nome_despesa' => $ocorrencia->nome($this->nome_despesa),
                'despesa_tipo_id' => $this->despesa_tipo_id,
                'despesa_fixa_id' => $this->id,
                'valor_total' => $this->valor,
                'data_vencimento' => $ocorrencia->vencimento->format('Y-m-d'),
                'ativo' => 1,
                'pago' => 0,
            ]);
        }

        if(!empty($ocorrencias)){
            $this->ocorrencias_ate = end($ocorrencias)->mes->format('Y-m-d');
            $this->save();
        }

        return count($ocorrencias);
    }

    /**
     * Completa a janela de Ocorrências das fixas do usuário. Idempotente: abrir a tela de novo não grava nada.
     */
    public function completarJanela($user){
        $ids = $this
            ->where('user_id', $user->id)
            ->where('ativo', 1)
            ->pluck('id');

        $gravadas = 0;
        foreach($ids as $id){
            $gravadas += DB::transaction(function() use ($id){
                //TRAVA A FIXA: DUAS TELAS ABERTAS AO MESMO TEMPO NÃO GRAVAM A MESMA OCORRÊNCIA DUAS VEZES
                return static::lockForUpdate()->find($id)->gravarOcorrencias();
            });
        }

        return $gravadas;
    }

    /**
     * Fixa que já gerou despesa fica travada: só pode ser inativada/reativada.
     */
    public function isTravada(){
        return isset($this->despesas_count)
            ? $this->despesas_count > 0
            : $this->despesas()->exists();
    }

    public function updateDespesaFixa($id, $request = [], $user){
        try{
            $despesaFixa = $this->where('user_id', $user->id)->findOrFail($id);

            if($despesaFixa->isTravada()){
                //PRESERVA O HISTÓRICO: APENAS ATIVA/INATIVA
                $despesaFixa->ativo = $request['ativo'];
                $despesaFixa->save();
            }else{
                $request['valor'] = setToDecimal($request['valor']);
                unset($request['user_id'], $request['despesa_tipo_id']);
                $despesaFixa->fill($request)->save();
            }

            $this->completarJanela($user);

            return [
                'error' => false,
                'msg' => $despesaFixa->ativo ? 'Despesa fixa alterada com sucesso!' : 'Despesa fixa inativada!'
            ];
        }catch(\Exception $error){
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => 'Não foi possível alterar o registro, tente de novo.'
            ];
        }
    }

    public function deleteDespesaFixa($id, $user){
        try{
            $despesaFixa = $this->where('user_id', $user->id)->findOrFail($id);

            if($despesaFixa->isTravada()){
                return [
                    'error' => true,
                    'msg' => 'Esta despesa fixa já gerou despesas e não pode ser excluída. Inative-a para preservar o histórico.'
                ];
            }

            $despesaFixa->delete();

            return [
                'error' => false,
                'msg' => 'Registro excluído com sucesso!'
            ];
        }catch(\Exception $error){
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => 'Não foi possível excluír o registro, tente de novo'
            ];
        }
    }

    public function totalAtivas($user){
        return $this
            ->where('user_id', $user->id)
            ->where('ativo', 1)
            ->sum('valor');
    }
}
