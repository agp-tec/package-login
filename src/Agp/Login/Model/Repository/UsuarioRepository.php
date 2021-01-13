<?php
/**
 *
 * Data e hora: 2020-09-23 09:39:59
 * Model/Repository gerada automaticamente
 *
 */


namespace Agp\Login\Model\Repository;


use App\Model\Entity\Usuario;
use Illuminate\Database\Eloquent\Model;


class UsuarioRepository
{
    /** Retorna a entidade Usuario identificada por $cpf
     * @param string $cpf
     * @return Usuario|Model|null
     * @throws \Exception
     */
    public function encontraUsuarioByCPF($cpf)
    {
        $usuarioEntity = config('login.user_entity');
        if (!class_exists($usuarioEntity))
            throw new \Exception('Classe "' . $usuarioEntity . '" não existe. Informe a entidade do usuário corretamente.');

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
     * @return Usuario|Model|null
     * @throws \Exception
     */
    public function encontraUsuarioByEmail($email)
    {
        $usuarioEntity = config('login.user_entity');
        if (!class_exists($usuarioEntity))
            throw new \Exception('Classe "' . $usuarioEntity . '" não existe. Informe a entidade do usuário corretamente.');

        return $usuarioEntity::query()
            ->where([
                'email' => encrypter($email, false),
            ])
            ->limit(1)
            ->get()
            ->first();
    }
}
