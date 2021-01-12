<?php
namespace Agp\Modelo\Traits;

/**
 * Trait ValidUserRegistry
 * Verifica se usuário tem acesso ao objeto
 */
trait ValidUserRegistry
{
    public function validUserRegistry(){
        return $this->adm_empresa_id == auth()->user()->getAdmEmpresaId();
    }
}
