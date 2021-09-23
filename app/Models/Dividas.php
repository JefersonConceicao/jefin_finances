<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use DB;

class Dividas extends Model
{
    protected $table = 'dividas';
    protected $fillable = [
        'descricao_divida',
        'qtd_parcela_total',
        'qtd_parcela_parcial',
        'valor_total',
        'valor_parcial',
        'valor_parcela',
        'created_at',
        'updated_at',
        'data_fim_divida',
        'data_inicial_divida',
        'pago',
        'user_id'
    ];

    public function getDividas($request = [], $user){
        $conditions = [];
        $conditions[] = ['user_id', '=', $user->id];
        
        if(isset($request['descricao_divida']) && !empty($request['descricao_divida'])){
            $conditions[] = ['descricao_divida', 'LIKE', "%".$request['descricao_divida']."%"];
        }

        if(isset($request['data_inicial_divida']) && !empty($request['data_inicial_divida'])){
            $conditions[] = [
                'data_inicial_divida', 
                '>=', 
                converteData(str_replace('/', '-', $request['data_inicial_divida']), 'Y-m-d')
            ];
        }

        if(isset($request['data_final_divida']) && !empty($request['data_final_divida'])){
            $conditions[] = [
                'data_fim_divida', 
                '<=', 
                converteData(str_replace('/','-',$request['data_final_divida']), 'Y-m-d')
            ];
        }

        if(isset($request['pago']) && !empty($request['pago'])){
            $conditions[] = ['pago','=', $request['pago']];
        }
        
       return $this
        ->where($conditions)
        ->get();
    }

    public function saveDivida($request = [], $user){
        $dataInicialDivida = converteData(str_replace('/','-',$request['data_inicial_divida']), 'Y-m-d');
        $carbon = new Carbon($dataInicialDivida);

        try{
            $this->fill([
                'descricao_divida' => $request['descricao_divida'],
                'data_inicial_divida' => $dataInicialDivida,
                'valor_total' => setToDecimal($request['valor_total']),
                'qtd_parcela_total' => $request['qtd_parcela_total'],
                'valor_parcela'=> setToDecimal($request['valor_total']) / setToDecimal($request['qtd_parcela_total']), 
                'qtd_parcela_parcial' => 0,
                'valor_parcial' => 0,
                'data_fim_divida' => $carbon->addMonth($request['qtd_parcela_total'])->toDateString(),
                'user_id' => $user->id,
            ])->save();

            return [
                'error' => false,
                'msg' => 'Registro adiciondao com sucesso!'
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível excluír o registro, tente de novo'
            ];
        }
    }

    public function getDividasByID($id){
        return $this->find($id);
    }

    public function deleteDivida($id){
        try{
            $this->find($id)->delete();

            return [
                'error' => false,
                'msg' => 'Registro excluído com sucesso!'
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível excluir o registro, tente de novo'
            ];
        }
    }

    public function payDebt($id){
        try{
            $debt = $this->find($id);
            $debt->qtd_parcela_parcial = $debt->qtd_parcela_parcial + 1;
            $debt->valor_parcial = $debt->valor_parcial + $debt->valor_parcela; 
            
            //VERIFICA SE É DECLARAÇÃO DA ULTIMA PARCELA
            if($debt->qtd_parcela_total == $debt->qtd_parcela_parcial){
                $debt->pago = 1;
            }

            $debt->save();
            return [
                'error' => false,
                'msg' => 'Pagamento efetuado, dívida atualizada'
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Ocorreu um erro ao declarar pagamento, tente de novo.'
            ];
        }
    }

    public function getTotalValorDivida($user, $request = []){
        $conditions = [];
        $conditions[] = ['user_id', '=', $user->id];
        
        if(isset($request['pago']) && !empty($request['pago'])){
            $conditions[] = ['pago','=',$request['pago']];
        }

        return $this
            ->select(DB::raw('SUM(valor_total) - SUM(valor_parcial) as valor_total_pagar'))
            ->where($conditions)
            ->first()
            ->valor_total_pagar;
    }
}
