@extends('layout.page')
@section('content_header')
    <h3> Lançamentos </h3> 
    <p class="text-subtitle"> Histórico de gastos. </p>
@endsection

@section('content-page')
    <div class="row"> 
        <div class="col-md-12 ">
            <button class="btn btn-primary rounded-pill float-end" id="addLancamento"> 
               <i class="fa fa-plus-square"> </i> Novo Lancamento
            </button>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 col-lg-4">
        <b> Despesas </b>
            <div class="card" id="gridLancamentos"> 
                <div class="card-header"> 
                    <form id="searchFormLancamentosDespesa">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label> Data inicial </label>
                                    <input 
                                        name="data_inicial"
                                        type="text"
                                        class="form-control" 
                                    />
                                </div>  
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label> Data Final </label>
                                    <input 
                                        name="data_final"
                                        type="text"
                                        class="form-control" 
                                    />
                                </div>  
                            </div>
                            <div class="col-md-2" style="margin-top:5%">
                                <button class="btn btn-primary rounded-pill"> 
                                    <i class="fa fa-search"> </i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body"> 
                    <div class="card-content table-responsive"> 
                        <table class="table table-hover"> 
                            <thead> 
                                <tr> 
                                    <th> Descrição </th>
                                    <th> Valor </th>
                                    <th width="1%"> Ações </th>
                                </tr>
                            <thead> 
                            <tbody> 

                            </tbody>
                        </table>
                    </div>  
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <b> Geral </b>
            <div class="card"> 
                <div class="card-header"> 
                    <form id="searchFormLancamentosGeral">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label> Data inicial </label>
                                    <input 
                                        name="data_inicial"
                                        type="text"
                                        class="form-control" 
                                    />
                                </div>  
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label> Data Final </label>
                                    <input 
                                        name="data_final"
                                        type="text"
                                        class="form-control" 
                                    />
                                </div>  
                            </div>
                            <div class="col-md-2" style="margin-top:5%">
                                <button class="btn btn-primary rounded-pill"> 
                                    <i class="fa fa-search"> </i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body"> 
                    <div class="card-content table-responsive"> 
                        <table class="table table-hover"> 
                            <thead> 
                                <tr> 
                                    <th> Descrição </th>
                                    <th> Valor </th>
                                    <th width="1%"> Ações </th>
                                </tr>
                            <thead> 
                            <tbody> 

                            </tbody>
                        </table>
                    </div>  
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <b> Outros gastos </b>
            <div class="card"> 
                <div class="card-header"> 
                    <form id="searchFormLancamentosOutros">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label> Data inicial </label>
                                    <input 
                                        name="data_inicial"
                                        type="text"
                                        class="form-control" 
                                    />
                                </div>  
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label> Data Final </label>
                                    <input 
                                        name="data_final"
                                        type="text"
                                        class="form-control" 
                                    />
                                </div>  
                            </div>
                            <div class="col-md-2" style="margin-top:5%">
                                <button class="btn btn-primary rounded-pill"> 
                                    <i class="fa fa-search"> </i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body"> 
                    <div class="card-content table-responsive"> 
                        <table class="table table-hover"> 
                            <thead> 
                                <tr> 
                                    <th> Descrição </th>
                                    <th> Valor </th>
                                    <th width="1%"> Ações </th>
                                </tr>
                            <thead> 
                            <tbody> 

                            </tbody>
                        </table>
                    </div>  
                </div>
            </div>
        </div>
    </div>
@endsection