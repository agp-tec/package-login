<?php
/**
 *
 * Data e hora: 2020-09-23 09:39:59
 * Model/Entity gerada automaticamente
 *
 */


namespace Agp\Modelo\Model\Entity;


use Agp\Modelo\Model\Observer\PaisObserver;
use Traits\ValidUserRegistry;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;


/**
 * Model Pais
 * Tabela: adm_pais
 * Comentario: Contém os paises.
 *
 * id
 * Chave primária, auto incremental.
 * Tipo: int	 | Chave: PRI	 | Obrigatório: Sim	 | Extra: auto_increment
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
 * exonimo_portugues
 * Nome do pais em portugues.
 * Tipo: varchar(45)	 | Chave: 	 | Obrigatório: Sim	 | Extra: 
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
 * exonimo_ingles
 * Nome do pais em ingles (EUA).
 * Tipo: varchar(45)	 | Chave: 	 | Obrigatório: Não	 | Extra: 
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
 * endonimo
 * Nome do pais no idioma nativo.
 * Tipo: varchar(45)	 | Chave: 	 | Obrigatório: Não	 | Extra: 
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
 * imagem
 * Nome do arquivo de bandeira do pais.
 * Tipo: varchar(25)	 | Chave: 	 | Obrigatório: Não	 | Extra: 
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
 */

class Pais extends BaseModel
{
    protected $table = "adm_pais";
    public $timestamps = false;
    protected $fillable = [
        "exonimo_portugues",
        "exonimo_ingles",
        "endonimo",
        "imagem",
    ];
    protected $fillableRelations = [
        "cidades",
    ];
    public function getRules()
    {
        return [
            "exonimo_portugues" => "string|max:45|required",
            "exonimo_ingles" => "string|max:45|nullable",
            "endonimo" => "string|max:45|nullable",
            "imagem" => "string|max:25|nullable",
        ];
    }

    protected static function boot()
    {
        parent::boot();
        Pais::observe(PaisObserver::class);
    }

   public function cidades()
   {
       return $this->belongsTo('Model\Entity\Cidade', 'adm_pais_id');
   }

}