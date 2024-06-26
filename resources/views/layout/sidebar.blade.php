
<ul class="menu">
    <li class="sidebar-title"> Menu </li>

    <li class="sidebar-item">
        <a href="/home" class="sidebar-link">
            <i class="fa fa-house-user"> </i>
            <span> Home </span>
        </a>
    </li>

    <li class="sidebar-item">
        <a href="/dividas" class="sidebar-link">
            <i class="fab fa-amazon-pay"></i>
            &nbsp;
            <span> Dívidas </span>
        </a>
    </li>

    <li class="sidebar-item has-sub">
        <a href="#" class="sidebar-link">
            <i class="fas fa-hand-holding-usd"></i>
            <span> Meu dinheiro </span>
        </a>
        <ul class="submenu">
            <li class="submenu-item">
                <a href="/orcamentario">
                    <i class="fas fa-angle-right"></i>
                    &nbsp;
                    Calculadora de Proventos
                </a>
            </li>
            <li class="submenu-item">
                <a href="/despesas">
                    <i class="fas fa-angle-right"></i>
                    &nbsp;
                    Despesas
                </a>
            </li>

            <li class="submenu-item">
                <a href="/tiposDespesas">
                    <i class="fas fa-angle-right"></i>
                    &nbsp;
                    Despesas Tipos
                </a>
            </li>
        </ul>
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
