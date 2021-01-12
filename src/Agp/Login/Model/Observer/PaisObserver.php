<?php
/**
 *
 * Data e hora: 2020-09-23 09:39:59
 * Model/Observer gerada automaticamente
 *
 */


namespace Agp\Login\Model\Observer;


use Agp\Login\Utils\Log;
use App\Exception\CustomUnauthorizedException;


class PaisObserver extends BaseObserver
{
    public function __construct()
    {
        //TODO Dados gerados automaticamente. Altere de acordo com os dados da entidade Pais
        $this->nome = 'Pais';
        $this->campo = 'nome';
        $this->genero = 'o';
    }


}
