<ul class="navbar-nav ms-auto mb-2 mb-lg-0"></ul>
<div class="dropdown">
    <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
        <div class="user-menu d-flex">
            <div class="user-name text-end me-3">
                <h6 class="mb-0 text-gray-600"> {{ Auth::user()->name }}</h6>
            </div>
            <div class="user-img d-flex align-items-center">
                <div class="avatar avatar-md">
                    <img src="https://img1.pnghut.com/10/13/18/wqCgb35w2c/user-drawing-avatar-blue-orange.jpg">
                </div>
            </div>
        </div>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledy="dropdownMenuButton">
        <li> 
            <h6 class="dropdown-header"> Olá, {{ Auth::user()->name }} </h6>
        </li>
        <li> 
            <a class="dropdown-item" href="#"> 
                <i class="far fa-id-badge me-2"></i> Meu Perfil
            </a>
        </li>
        <li> 
            <a class="dropdown-item" href="/logout"> 
                <i style="color:red;" class="fas fa-sign-out-alt me-2"></i> Sair
            </a>
        </li> 
    </ul>
</div>