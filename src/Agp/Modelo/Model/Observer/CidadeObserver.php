<?php
/**
 *
 * Data e hora: 2020-09-23 09:39:57
 * Model/Observer gerada automaticamente
 *
 */


namespace Agp\Modelo\Model\Observer;


use App\Exception\CustomUnauthorizedException;
use Agp\Modelo\Model\Entity\Cidade;
use Agp\Modelo\Utils\Log;


class CidadeObserver extends BaseObserver
{
    public function __construct()
    {
        //TODO Dados gerados automaticamente. Altere de acordo com os dados da entidade Cidade
        $this->nome = 'Cidade';
        $this->campo = 'nome';
        $this->genero = 'o';
    }


}