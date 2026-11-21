<?php

namespace App\Models;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;

class DespesaFixa extends Model
{
    protected $table = 'despesas_fixas';
    protected $fillable = [
        'user_id',
        'nome_despesa',
        'despesa_tipo_id',
        'valor',
        'ativo',
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
            $request['user_id'] = $user->id;
            $request['valor'] = setToDecimal($request['valor']);
            $request['despesa_tipo_id'] = (new TiposDespesa)->idTipoFixaDoUsuario($user);

            $this->fill($request)->save();
            $this->gerarParaMes($user, date('m'), date('Y'));

            return [
                'error' => false,
                'msg' => 'Despesa fixa adicionada!'
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível salvar o registro, tente de novo',
                'error_message' => $error->getMessage()
            ];
        }
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

            $this->gerarParaMes($user, date('m'), date('Y'));

            return [
                'error' => false,
                'msg' => $despesaFixa->ativo ? 'Despesa fixa alterada com sucesso!' : 'Despesa fixa inativada!'
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível alterar o registro, tente de novo.',
                'error_message' => $error->getMessage()
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

    /**
     * Cria as despesas do mês a partir das despesas fixas ativas.
     * Meses futuros não são gravados (apenas projetados).
     */
    public function gerarParaMes($user, $mes, $ano){
        if(empty($mes) || empty($ano) || !is_numeric($mes) || !is_numeric($ano)){
            return 0;
        }

        $inicioMes = Carbon::create((int) $ano, (int) $mes, 1)->startOfDay();
        if($inicioMes->gt(Carbon::now()->startOfMonth())){
            return 0;
        }

        $fimMes = $inicioMes->copy()->endOfMonth();

        $fixasPendentes = $this
            ->where('user_id', $user->id)
            ->where('ativo', 1)
            ->where('created_at', '<=', $fimMes)
            ->whereDoesntHave('despesas', function($query) use ($inicioMes, $fimMes){
                $query->whereBetween('created_at', [$inicioMes, $fimMes]);
            })
            ->get();

        if($fixasPendentes->isEmpty()){
            return 0;
        }

        DB::transaction(function() use ($fixasPendentes, $user, $inicioMes){
            foreach($fixasPendentes as $fixa){
                Despesa::create([
                    'user_id' => $user->id,
                    'nome_despesa' => $fixa->nome_despesa,
                    'despesa_tipo_id' => $fixa->despesa_tipo_id,
                    'despesa_fixa_id' => $fixa->id,
                    'valor_total' => $fixa->valor,
                    'ativo' => 1,
                    'pago' => 0,
                    'created_at' => $inicioMes->format('Y-m-d H:i:s'),
                ]);
            }
        });

        return $fixasPendentes->count();
    }
}
