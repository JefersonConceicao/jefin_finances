<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;
use App\Recorrencia\SemOcorrenciasOcultas;

class Proventos extends Model
{
    protected $table = 'proventos';
    protected $fillable = [
        'user_id',
        'valor_provento',
        'data_provento',
        'created_at',
        'updated_at',
        'descricao_provento'
    ];

    public $timestamps = true;

    protected static function boot(){
        parent::boot();

        //OCORRÊNCIAS DE PROVENTO FIXO OCULTO SOMEM DE TODO O APP
        static::addGlobalScope(new SemOcorrenciasOcultas('provento_fixo_id', 'proventos_fixos'));
    }

    public function proventoFixo(){
        return $this->belongsTo(ProventoFixo::class, 'provento_fixo_id', 'id');
    }

    //MÊS EM QUE O PROVENTO CONTA
    public function competencia(){
        return Carbon::parse($this->data_provento)->startOfMonth();
    }

    const MSG_SEGUINTES_NO_PASSADO = '"Este e os seguintes" vale só a partir do mês atual';

    //"ESTE E OS SEGUINTES" (REAJUSTE OU ENCERRAMENTO) SÓ VALE NO MÊS ATUAL OU NUM MÊS FUTURO
    private function seguintesNoPassado(){
        return $this->competencia()->lt(Carbon::now()->startOfMonth());
    }

    public function getProventos($request = [], $user){
        $conditions = [];
        $conditions[] = ['user_id', '=', $user->id];

        if(isset($request['descricao_provento']) && !empty($request['descricao_provento'])){
            $conditions[] = ['descricao_provento', 'LIKE', "%".$request['descricao_provento']."%"];
        }

        $dataWithFilter = $this
            ->where($conditions)
            ->whereMonth('data_provento', !empty($request['mes']) ? $request['mes'] : null)
            ->whereYear('data_provento',  !empty($request['ano']) ? $request['ano'] : null);

        if(empty($request['mes']) && isset($request['ano']) && !empty($request['ano'])){
            return $this
                ->where($conditions)
                ->whereYear('data_provento', $request['ano']);
        }

        if(empty($request['ano']) && isset($request['mes']) && !empty($request['mes'])){
            return $this
            ->where($conditions)
            ->whereMonth('data_provento', $request['mes']);
        }

        if(empty($request['mes']) && empty($request['ano'])){
            return $this
                ->where($conditions);
        }

        return $dataWithFilter;
    }

    public function saveProvento($request = [], $user){
        try{
            $request['user_id'] = $user->id;

            if(isset($request['valor_provento']) && !empty($request['valor_provento'])){
                $request['valor_provento'] = setToDecimal($request['valor_provento']);
            }

            if(isset($request['data_provento']) && !empty($request['data_provento'])){
                $request['data_provento'] = converteData(str_replace('/','-', $request['data_provento']), 'Y-m-d');
            }

            if(isset($request['period_type']) && $request['period_type'] === 'especifico'){
                $start = Carbon::createFromFormat('d/m/Y', $request['periodo_inicio']);
                $end = Carbon::createFromFormat('d/m/Y', $request['periodo_fim']);

                if($start->gt($end)){
                    $temp = $start; $start = $end; $end = $temp;
                }

                DB::beginTransaction();
                $lastCreated = null;
                $next = $start->copy();
                while($next->lte($end)){
                    $exists = $this->where('user_id', $user->id)
                        ->where('descricao_provento', $request['descricao_provento'])
                        ->whereMonth('data_provento', $next->month)
                        ->whereYear('data_provento', $next->year)
                        ->first();

                    if($exists){
                        $exists->valor_provento += $request['valor_provento'];
                        $exists->save();
                        $lastCreated = $exists;
                    } else {
                        $item = self::create([
                            'user_id' => $user->id,
                            'descricao_provento' => $request['descricao_provento'],
                            'valor_provento' => $request['valor_provento'],
                            'data_provento' => $next->format('Y-m-d')
                        ]);
                        $lastCreated = $item;
                    }
                    $next->addMonth();
                }
                DB::commit();

                return [
                    'error' => false,
                    'msg' => 'Proventos do período adicionado!',
                    'dataAdded' => $lastCreated
                ];
            }

            // Normal save with existence check (only month and year)
            $date = Carbon::parse($request['data_provento']);
            $exists = $this->where('user_id', $user->id)
                ->where('descricao_provento', $request['descricao_provento'])
                ->whereMonth('data_provento', $date->month)
                ->whereYear('data_provento', $date->year)
                ->first();

            if($exists){
                $exists->valor_provento += $request['valor_provento'];
                $exists->save();
                return [
                    'error' => false,
                    'msg' => 'Valor somado ao provento existente!',
                    'dataAdded' => $exists
                ];
            }

            $this->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Provento adicionado!',
                'dataAdded' => $this->find($this->id)
            ];
        }catch(\Exception $error){
            DB::rollback();
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => 'Não foi possível salvar o registro.'
            ];
        }
    }

    /**
     * Busca o Provento só entre os do usuário: o de outro usuário é tratado como inexistente (404).
     */
    public function doUsuario($id, $user){
        return $this->where('user_id', $user->id)->findOrFail($id);
    }

    public function updateProvento($id, $request = [], $user = null){
        //FORA DO try: PROVENTO DE OUTRO USUÁRIO VIRA 404, E NÃO UM ERRO GENÉRICO
        $provento = $this->doUsuario($id, $user);

        try{
            if(isset($request['valor_provento']) && !empty($request['valor_provento'])){
                $request['valor_provento'] = setToDecimal($request['valor_provento']);
            }

            if(isset($request['data_provento']) && !empty($request['data_provento'])){
                $request['data_provento'] = converteData(str_replace('/','-', $request['data_provento']), 'Y-m-d');
            }

            $seguintes = isset($request['update_subsequent']) && $request['update_subsequent'] == 'true';

            if($seguintes && $provento->proventoFixo && $provento->seguintesNoPassado()){
                return ['error' => true, 'msg' => self::MSG_SEGUINTES_NO_PASSADO];
            }

            if($seguintes && $provento->proventoFixo){
                //O REAJUSTE MUDA O VALOR, NÃO O MÊS: MOVER A OCORRÊNCIA DE MÊS É "APENAS ESTE"
                if(!empty($request['data_provento']) && Carbon::parse($request['data_provento'])->format('Y-m') !== $provento->competencia()->format('Y-m')){
                    return ['error' => true, 'msg' => 'Para mudar o mês, use "Apenas este"'];
                }

                DB::transaction(function() use ($provento, $request){
                    //OCORRÊNCIA DE PROVENTO FIXO: "ESTE E OS SUBSEQUENTES" É O REAJUSTE, A PARTIR DO MÊS DELA
                    $provento->proventoFixo->reajustar($provento->competencia(), $request['valor_provento']);

                    //A DESCRIÇÃO NOVA VALE SÓ PARA ESTE MÊS
                    if(!empty($request['descricao_provento'])){
                        $provento->refresh();
                        $provento->descricao_provento = $request['descricao_provento'];
                        $provento->save();
                    }
                });
                $msg = 'Reajuste aplicado a este mês e aos seguintes!';
            }elseif($seguintes){
                $originalDescription = $provento->descricao_provento;
                $originalUserId = $provento->user_id;
                $originalDate = $provento->data_provento;

                $this->where('user_id', $originalUserId)
                    ->where('descricao_provento', $originalDescription)
                    ->whereDate('data_provento', '>=', $originalDate)
                    ->update([
                        'descricao_provento' => $request['descricao_provento'] ?? $originalDescription,
                        'valor_provento' => $request['valor_provento'] ?? $provento->valor_provento
                    ]);
                
                $msg = 'Provento e registros subsequentes alterados!';
            } else {
                $provento->fill($request)->save();
                $msg = 'Provento alterado!';
            }

            return [
                'error' => false,
                'msg' => $msg,
                'dataUpdated' => $provento
            ];
        }catch(\Exception $error){
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => 'Não foi possível alterar o registro.',
            ];
        }
    }

    public function deleteProvento($id, $request = [], $user = null){
        //FORA DO try: PROVENTO DE OUTRO USUÁRIO VIRA 404, E NÃO UM ERRO GENÉRICO
        $provento = $this->doUsuario($id, $user);

        try {
            $seguintes = isset($request['delete_subsequent']) && $request['delete_subsequent'] == 'true';

            if($seguintes && $provento->proventoFixo && $provento->seguintesNoPassado()){
                return ['error' => true, 'msg' => self::MSG_SEGUINTES_NO_PASSADO];
            }

            if($seguintes && $provento->proventoFixo){
                //OCORRÊNCIA DE PROVENTO FIXO: "ESTE E OS SUBSEQUENTES" É O ENCERRAMENTO NO MÊS ANTERIOR
                $provento->proventoFixo->encerrar($provento->competencia()->subMonthNoOverflow());
                $msg = 'Provento fixo encerrado no mês anterior!';
            }elseif($seguintes){
                $this->where('user_id', $provento->user_id)
                    ->where('descricao_provento', $provento->descricao_provento)
                    ->whereDate('data_provento', '>=', $provento->data_provento)
                    ->delete();
                $msg = 'Registros excluídos com sucesso!';
            } else {
                $provento->delete();
                $msg = 'Registro excluído com sucesso!';
            }

            return [
                'error' => false,
                'msg' => $msg,
            ];
        } catch (\Exception $e) {
            \Log::error($e->getMessage(), ['exception' => $e]);
            return [
                'error' => true,
                'msg' => 'Não foi possível excluír o registro, tente de novo',
            ];
        }
    }

    //"REPETIR PROVENTOS DO MÊS ANTERIOR": COPIA OS PROVENTOS AVULSOS DO USUÁRIO DO MÊS ANTERIOR PARA O MÊS DO FILTRO.
    //OCORRÊNCIAS DE PROVENTO FIXO FICAM DE FORA: O PROVENTO FIXO JÁ GRAVA AS DO MÊS DE DESTINO
    public function copyLastMonth($request = [], $user){
        if(empty($request['mes']) || empty($request['ano'])){
            return [
                'error' => true,
                'msg' => 'Escolha o mês e o ano para onde copiar'
            ];
        }

        try{
            $destino = Carbon::create((int) $request['ano'], (int) $request['mes'], 1)->startOfDay();
            $origem = $destino->copy()->subMonthNoOverflow();

            $anteriores = $this->where('user_id', $user->id)
                ->whereNull('provento_fixo_id')
                ->whereDate('data_provento', '>=', $origem->toDateString())
                ->whereDate('data_provento', '<=', $origem->copy()->endOfMonth()->toDateString())
                ->get();

            DB::transaction(function() use ($anteriores, $destino, $user){
                foreach($anteriores as $anterior){
                    //MESMO DIA NO MÊS DE DESTINO; 31 VIRA O ÚLTIMO DIA EM MÊS MAIS CURTO
                    $dia = min(Carbon::parse($anterior->data_provento)->day, $destino->daysInMonth);

                    $this->create([
                        'user_id' => $user->id,
                        'valor_provento' => $anterior->valor_provento,
                        'data_provento' => $destino->copy()->day($dia)->toDateString(),
                        'descricao_provento' => $anterior->descricao_provento
                    ]);
                }
            });

            return [
                'error' => false,
                'msg' => 'Proventos adicionados!'
            ];
        }catch(\Exception $error){
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => 'Não foi possível adicionar os proventos, tente de novo'
            ];
        }
    }
}
