<?php

namespace Agp\Modelo\Model\Entity;


use Agp\Modelo\Model\Observer\CidadeObserver;
use Traits\ValidUserRegistry;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;


/**
 * Model Cidade
 * Tabela: adm_cidade
 * Comentario: Contém as cidades (provincias).
 *
 * id
 * Chave primária, código do município de acordo com tabela do IBGE.
 * Tipo: int     | Chave: PRI     | Obrigatório: Sim     | Extra:
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * nome
 * Nome da cidade.
 * Tipo: varchar(45)     | Chave:     | Obrigatório: Sim     | Extra:
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * uf
 * Código do estado no formato ISO 3166-2. Exemplo: SP
 * Tipo: varchar(2)     | Chave:     | Obrigatório: Sim     | Extra:
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * id_uf
 * Código da UF de acordo com tabela do IBGE.
 * Tipo: int     | Chave:     | Obrigatório: Sim     | Extra:
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * sigla_pais
 * Código do país no formato ISO 3166-1 alpha-2. Exemplo: BR
 * Tipo: varchar(2)     | Chave:     | Obrigatório: Sim     | Extra:
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * adm_pais_id
 * Chave estrangeira de adm_pais.id.
 * Tipo: int     | Chave: MUL     | Obrigatório: Sim     | Extra:
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */
class Cidade extends BaseModel
{

    protected $table = "adm_cidade";
    public $timestamps = false;
    protected $fillable = [
        "nome",
        "uf",
        "id_uf",
        "sigla_pais",
        "adm_pais_id",
    ];
    protected $fillableRelations = [
        "pais",
        "pessoaEnderecos",
    ];

    public function getRules()
    {
        return [
            "nome" => "string|max:45|required",
            "uf" => "string|max:2|required",
            "id_uf" => "integer|required",
            "sigla_pais" => "string|max:2|required",
            "pais" => "required",
        ];
    }

    protected static function boot()
    {
        parent::boot();
        Cidade::observe(CidadeObserver::class);
    }

    public function pais()
    {
        return $this->hasMany('Agp/Modelo/src/app', 'adm_pais_id');
    }

}