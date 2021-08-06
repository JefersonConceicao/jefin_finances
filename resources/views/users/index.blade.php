@extends('layout.page')
@section('content_header')
    <h3> Usuários </h3>
    <p class="text-subtitle"> Gerenciamento de usuários </p>
@endsection

@section("content-page")
    @component('components.filtro')
        <form id="searchFormUsers">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label> Nome </label>
                        <input 
                            name="name"
                            type="text"
                            class="form-control"
                        />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> E-mail </label>
                        <input 
                            name="email"
                            type="text"
                            class="form-control"
                        />
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top:2%">
                <div class="col-md-3 pull-right">
                    <button class="btn btn-primary pull-right rounded-pill" type="submit">
                        <i class="fa fa-search"> </i> Pesquisar
                    </button>
                </div>
            </div>
        </form>
    @endcomponent
    <div id="gridUsers">
        <div class="card" >
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h1 class="card-title"> Total de registros: {{ count($dataUsers) }} </h1>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-primary float-end  rounded-pill" id="addUser"> 
                            Novo <i class="fa fa-plus-square"> </i> 
                        </button>
                    </div>
                </div> 
            </div>
            <div class="card-content">
                <div class="card-body">
                    @if(count($dataUsers) > 0)
                        <div class="table-responsive" style="overflow:auto;">
                            <table class="table table-hover">
                                <thead> 
                                    <tr> 
                                        <th> Nome  </th>
                                        <th> E-mail </th>
                                        <th> Criado em </th>
                                        <th width="5%"> Ativo </th>
                                        @if(Auth::user()->id == 16) 
                                            <th width="2%"> Ações </th> 
                                        @endif
                                    </tr>
                                </thead>
                                <tbody> 
                                    @foreach($dataUsers as $user)
                                        <tr> 
                                            <td> {{ $user->name." ".$user->last_name}} </td>
                                            <td> {{ $user->email }} </td>
                                            <td> {{ converteData($user->created_at, 'd/m/Y H:i:s') }} </td>
                                            <td> 
                                                <span class="badge bg-{{ $user->ativo ? "success" : "danger"}}">
                                                    {{ $user->ativo ? "Sim" : "Não"}}
                                                </span>
                                            </td>
                                            @if(Auth::user()->id == 16)
                                                <td> 
                                                    <div class="text-center" style="display:flex;">
                                                        <button 
                                                            class="btnEditUser btn btn-secondary rounded-pill"
                                                            id="{{ $user->id }}"    
                                                        >
                                                            <i class="fa fa-edit"> </i>
                                                        </button>
                                                        &nbsp;
                                                        <button 
                                                            class="btnDeleteUser btn btn-danger rounded-pill"
                                                            id="{{ $user->id }}"
                                                        >
                                                            <i class="fa fa-trash"> </i>
                                                        </button>
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else 
                        <div class="text-center">
                            <h4> Nenhum registro encontrado. </h4>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection