<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

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

            if(isset($request['period_type']) && $request['period_type'] === 'anual'){
                $year = !empty($request['data_provento']) ? Carbon::createFromFormat('Y-m-d', $request['data_provento'])->year : date('Y');

                DB::beginTransaction();
                $lastCreated = null;
                for($month = 1; $month <= 12; $month++){
                    $item = self::create([
                        'user_id' => $user->id,
                        'descricao_provento' => $request['descricao_provento'],
                        'valor_provento' => $request['valor_provento'],
                        'data_provento' => sprintf('%04d-%02d-01', $year, $month)
                    ]);
                    $lastCreated = $item;
                }
                DB::commit();

                return [
                    'error' => false,
                    'msg' => 'Provento anual adicionado!',
                    'dataAdded' => $lastCreated
                ];
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
                    $item = self::create([
                        'user_id' => $user->id,
                        'descricao_provento' => $request['descricao_provento'],
                        'valor_provento' => $request['valor_provento'],
                        'data_provento' => $next->format('Y-m-d')
                    ]);
                    $lastCreated = $item;
                    $next->addMonth();
                }
                DB::commit();

                return [
                    'error' => false,
                    'msg' => 'Proventos do período adicionado!',
                    'dataAdded' => $lastCreated
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
            return [
                'error' => true,
                'msg' => 'Não foi possível salvar o registro.',
                'error_message' => $error->getMessage()
            ];
        }
    }

    public function updateProvento($id, $request = []){
        try{
            if(isset($request['valor_provento']) && !empty($request['valor_provento'])){
                $request['valor_provento'] = setToDecimal($request['valor_provento']);
            }

            if(isset($request['data_provento']) && !empty($request['data_provento'])){
                $request['data_provento'] = converteData(str_replace('/','-', $request['data_provento']), 'Y-m-d');
            }

            $provento = $this->find($id);
            $provento->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Provento alterado!',
                'dataUpdated' => $provento
            ];
        }catch(\Exception $error){
            return [
                'error' => false,
                'msg' => 'Não foi possível alterar o registro.',
            ];
        }
    }

    public function deleteProvento($id){
        if($this->find($id)->delete()){
            return [
                'error' => false,
                'msg' => 'Registro excluído com sucesso!',
            ];
        }else{
            return [
                'error' => true,
                'msg' => 'Não foi possível excluír o registro, tente de novo',
            ];
        }
    }

    public function copyLastMonth($request = []){
        try{
            $lastProventos = $this
                ->whereMonth('data_provento', $request['mes'] - 1)
                ->whereYear('data_provento', $request['ano']);

            if($lastProventos->count() > 0){
                DB::beginTransaction();
                foreach($lastProventos->get()->toArray() as $k => $v){
                    $formData = [
                        'user_id' => $v['user_id'],
                        'valor_provento' => $v['valor_provento'],
                        'data_provento' => $request['ano'].'-'.$request['mes'].'-'.date('d'),
                        'descricao_provento' => $v['descricao_provento']
                    ];

                    $this->create($formData);
                    unset($formData);
                }
            }

            DB::commit();
            return [
                'error' => false,
                'msg' => 'Proventos adicionados!'
            ];
        }catch(\Exception $error){
            DB::rollback();
            return [
                'error' => true,
                'msg' => 'Não foi possível adicionar os proventos, tente de novo'
            ];
        }
    }
}
