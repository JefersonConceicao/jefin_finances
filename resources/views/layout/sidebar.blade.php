<ul class="menu">
    <li class="sidebar-title"> Menu </li>

    <li class="sidebar-item {{ request()->is('projecao') ? 'active' : '' }}">
        <a href="/projecao" class="sidebar-link">
            <i class="fas fa-chart-line"></i>
            <span> Projeção </span>
        </a>
    </li>

    <li class="sidebar-item has-sub {{ request()->is('proventos*') ? 'active' : '' }}">
        <a href="#" class="sidebar-link">
            <i class="fas fa-hand-holding-usd"></i>
            <span> Proventos </span>
        </a>
        <ul class="submenu {{ request()->is('proventos*') ? 'active' : '' }}">
            <li class="submenu-item {{ request()->is('proventos') ? 'active' : '' }}">
                <a href="/proventos">
                    <i class="fas fa-angle-right"></i>
                    &nbsp;
                    Proventos
                </a>
            </li>
            <li class="submenu-item {{ request()->is('proventosFixos*') ? 'active' : '' }}">
                <a href="/proventosFixos">
                    <i class="fas fa-angle-right"></i>
                    &nbsp;
                    Proventos Fixos
                </a>
            </li>
        </ul>
    </li>

    <li class="sidebar-item has-sub {{ request()->is('despesas*') ? 'active' : '' }}">
        <a href="#" class="sidebar-link">
            <i class="fas fa-file-invoice-dollar"></i>
            <span> Despesas </span>
        </a>
        <ul class="submenu {{ request()->is('despesas*') ? 'active' : '' }}">
            <li class="submenu-item {{ request()->is('despesas') ? 'active' : '' }}">
                <a href="/despesas">
                    <i class="fas fa-angle-right"></i>
                    &nbsp;
                    Despesas
                </a>
            </li>
            <li class="submenu-item {{ request()->is('despesasFixas*') ? 'active' : '' }}">
                <a href="/despesasFixas">
                    <i class="fas fa-angle-right"></i>
                    &nbsp;
                    Despesas Fixas
                </a>
            </li>
        </ul>
    </li>

    <li class="sidebar-item {{ request()->is('guia') ? 'active' : '' }}">
        <a href="/guia" class="sidebar-link">
            <i class="fas fa-book"></i>
            <span> Guia do Usuário </span>
        </a>
    </li>

    @if(Auth::user()->id == 16)
        <li class="sidebar-item">
            <a href="/users" class="sidebar-link">
                <i class="fa fa-users"> </i>
                <span> Usuários </span>
            </a>
        </li>
    @endif
</ul>
