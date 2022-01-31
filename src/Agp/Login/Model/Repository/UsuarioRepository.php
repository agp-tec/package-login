<?php
/**
 *
 * Data e hora: 2020-09-23 09:39:59
 * Model/Repository gerada automaticamente
 *
 */


namespace Agp\Login\Model\Repository;


use Agp\BaseUtils\Helper\Utils;
use Exception;
use Illuminate\Database\Eloquent\Model;


class UsuarioRepository
{
    /** Retorna a entidade Usuario identificada por ID
     * @param int $id
     * @return Model|null
     * @throws Exception
     */
    public function getById($id)
    {
        $usuarioEntity = config('login.user_entity');
        if (!class_exists($usuarioEntity))
            throw new Exception('Classe "' . $usuarioEntity . '" não existe. Informe a entidade do usuário corretamente.');
        
        return $usuarioEntity::query()
            ->where([
                'adm_pessoa_id' => $id,
            ])
            ->limit(1)
            ->get()
            ->first();
    }

    /** Retorna a entidade Usuario identificada por $cpf
     * @param string $cpf
     * @return Model|null
     * @throws Exception
     */
    public function encontraUsuarioByCPF($cpf)
    {
        $usuarioEntity = config('login.user_entity');
        if (!class_exists($usuarioEntity))
            throw new Exception('Classe "' . $usuarioEntity . '" não existe. Informe a entidade do usuário corretamente.');

        return $usuarioEntity::query()
            ->where([
                'doc' => encrypter($cpf, false),
            ])
            ->limit(1)
            ->get()
            ->first();
    }

    /** Retorna a entidade Usuario identificada por $email
     * @param string $email
     * @return Model|null
     * @throws Exception
     */
    public function encontraUsuarioByEmail($email)
    {
        $usuarioEntity = config('login.user_entity');
        if (!class_exists($usuarioEntity))
            throw new Exception('Classe "' . $usuarioEntity . '" não existe. Informe a entidade do usuário corretamente.');

        return $usuarioEntity::query()
            ->where([
                'email' => encrypter($email, false),
            ])
            ->limit(1)
            ->get()
            ->first();
    }

    /** Retorna a entidade Usuario identificada por $email ou $cpf
     * @param string $value
     * @return Model|null
     * @throws Exception
     */
    public function encontraUsuarioByEmailOrCpf($value)
    {
        $usuarioEntity = config('login.user_entity');
        if (!class_exists($usuarioEntity))
            throw new Exception('Classe "' . $usuarioEntity . '" não existe. Informe a entidade do usuário corretamente.');

        return $usuarioEntity::query()
            ->where(function ($query) use ($value) {
                $query
                    ->orWhere('doc', '=', encrypter(Utils::mask('###.###.###-##', $value), false))
                    ->orWhere('email', '=', encrypter($value, false));
            })
            ->first();
    }
}
