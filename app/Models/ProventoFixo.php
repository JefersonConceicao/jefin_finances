<?php

namespace App\Models;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use App\Recorrencia\Ocorrencia;
use App\Recorrencia\Recorrente;
use App\Recorrencia\SemOcorrenciasOcultas;

/**
 * Provento recorrente (ex.: salário) que se repete todo mês dentro da sua Vigência.
 * Cada Ocorrência é um Provento ligado a ele, com data no dia 1º do mês.
 */
class ProventoFixo extends Model
{
    use Recorrente;

    protected $table = 'proventos_fixos';
    protected $fillable = [
        'user_id',
        'descricao',
        'valor',
        'ativo',
        'mes_inicio',
        'mes_fim',
    ];

    protected $casts = [
        'mes_inicio' => 'date',
        'mes_fim' => 'date',
        'ocorrencias_ate' => 'date',
    ];

    //O PRÓPRIO PROVENTO FIXO ENXERGA AS SUAS OCORRÊNCIAS MESMO OCULTO (PARA ENCERRAR E EXCLUIR)
    public function ocorrencias(){
        return $this->hasMany(Proventos::class, 'provento_fixo_id', 'id')->withoutGlobalScope(SemOcorrenciasOcultas::class);
    }

    protected function colunaCompetencia(){
        return 'data_provento';
    }

    //O PROVENTO DO MÊS FICA NO DIA 1º
    protected function diaDaOcorrencia(){
        return 1;
    }

    protected function criarOcorrencia(Ocorrencia $ocorrencia){
        //provento_fixo_id FICA FORA DO fillable: O FORMULÁRIO DE PROVENTO NÃO LIGA UM PROVENTO A UM PROVENTO FIXO
        (new Proventos)->forceFill([
            'user_id' => $this->user_id,
            'provento_fixo_id' => $this->id,
            'descricao_provento' => $this->descricao,
            'valor_provento' => $this->valor,
            'data_provento' => $ocorrencia->vencimento->format('Y-m-d'),
        ])->save();
    }

    protected function reajustarOcorrencia($provento, Ocorrencia $noMes){
        $provento->valor_provento = $this->valor;
    }

    protected function excluirOcorrencia($provento){
        $provento->delete();
    }

    public function getProventosFixos($user, $mostrarOcultas = false){
        $query = $this->where('user_id', $user->id);

        //OS OCULTOS SÓ APARECEM COM "MOSTRAR OCULTAS", DEPOIS DOS VISÍVEIS
        if(!$mostrarOcultas){
            $query->where('oculta', 0);
        }

        return $query
            ->withCount('ocorrencias')
            ->orderBy('oculta', 'ASC')
            ->orderBy('valor', 'DESC');
    }

    public function doUsuario($id, $user){
        return $this->where('user_id', $user->id)->findOrFail($id);
    }

    private function resposta(callable $operacao, $sucesso, $falha){
        try{
            $operacao();

            return [
                'error' => false,
                'msg' => $sucesso
            ];
        }catch(\Exception $error){
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => $falha
            ];
        }
    }

    public function saveProventoFixo($request, $user){
        return $this->resposta(function() use ($request, $user){
            //O PROVENTO FIXO E TODAS AS OCORRÊNCIAS DELE SÃO GRAVADOS JUNTOS: SE UMA FALHAR, NADA FICA
            DB::transaction(function() use ($request, $user){
                $this->fill([
                    'user_id' => $user->id,
                    'descricao' => $request['descricao'],
                    'valor' => setToDecimal($request['valor']),
                    'ativo' => 1,
                    'mes_inicio' => Carbon::createFromFormat('!Y-m', $request['mes_inicio'])->format('Y-m-d'),
                    'mes_fim' => !empty($request['mes_fim'])
                        ? Carbon::createFromFormat('!Y-m', $request['mes_fim'])->format('Y-m-d')
                        : null,
                ])->save();

                $this->gravarOcorrencias();
            });
        }, 'Provento fixo adicionado!', 'Não foi possível salvar o registro, tente de novo');
    }

    /**
     * Altera a descrição. A nova vale para as Ocorrências do mês atual em diante; as anteriores ficam como estão.
     */
    public function updateProventoFixo($id, $request, $user){
        $proventoFixo = $this->doUsuario($id, $user);

        return $this->resposta(function() use ($proventoFixo, $request){
            DB::transaction(function() use ($proventoFixo, $request){
                $proventoFixo->descricao = $request['descricao'];
                $proventoFixo->save();

                $proventoFixo->ocorrenciasAPartirDe(Carbon::now())->update(['descricao_provento' => $proventoFixo->descricao]);
            });
        }, 'Provento fixo alterado!', 'Não foi possível alterar o registro, tente de novo.');
    }

    public function reajustarProventoFixo($id, $request, $user){
        $proventoFixo = $this->doUsuario($id, $user);

        return $this->resposta(function() use ($proventoFixo, $request){
            $proventoFixo->reajustar(Carbon::createFromFormat('!Y-m', $request['a_partir_de']), setToDecimal($request['valor']));
        }, 'Reajuste aplicado!', 'Não foi possível reajustar, tente de novo.');
    }

    public function encerrarProventoFixo($id, $request, $user){
        $proventoFixo = $this->doUsuario($id, $user);

        $fim = Carbon::createFromFormat('!Y-m', $request['mes_fim']);
        if($proventoFixo->fimDepoisDoAtual($fim)){
            return [
                'error' => true,
                'msg' => 'A Vigência já termina em ' . $proventoFixo->mes_fim->format('m/Y') . '. Para estender, cadastre um novo Provento Fixo.'
            ];
        }

        return $this->resposta(function() use ($proventoFixo, $request){
            $proventoFixo->encerrar(Carbon::createFromFormat('!Y-m', $request['mes_fim']));
        }, 'Provento fixo encerrado!', 'Não foi possível encerrar, tente de novo.');
    }

    public function deleteProventoFixo($id, $user){
        $proventoFixo = $this->doUsuario($id, $user);

        //COM OCORRÊNCIAS, EXCLUIR APAGARIA O HISTÓRICO: O CAMINHO É O ENCERRAMENTO
        if($proventoFixo->temOcorrencias()){
            return [
                'error' => true,
                'msg' => 'Este provento fixo já tem Ocorrências. Use "Encerrar" para terminar a Vigência.'
            ];
        }

        return $this->resposta(function() use ($proventoFixo){
            $proventoFixo->delete();
        }, 'Registro excluído com sucesso!', 'Não foi possível excluír o registro, tente de novo');
    }
}
