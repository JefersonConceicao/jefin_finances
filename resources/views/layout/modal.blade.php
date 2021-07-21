<form id="@yield('modal-form')">
    <div class="modal-header bg-primary">
        <h5 id="myModalLabel160" class="modal-title white">
            @yield('modal-header')
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        @yield('modal-content')
    </div>  
    <div class="modal-footer">
        @hasSection('btn_fechar')
            <button 
                class="btn btn-danger float-start" 
                data-bs-dismiss="modal"
                type="button"
            >
                        @yield('btn_fechar')
            </button>
        @endif
        @hasSection('btn_submit')
            <button 
                class="btn btn-primary btnSubmit float-end"
                type="submit"
            > 
                @yield('btn_submit') 
            </button>
        @endif 
    </div>
</form>


