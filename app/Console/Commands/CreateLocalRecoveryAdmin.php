<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateLocalRecoveryAdmin extends Command
{
    protected $signature = 'fuel:create-recovery-admin {username? : Nome de acesso local} {--name= : Nome de exibição} {--email= : E-mail opcional}';

    protected $description = 'Cria uma conta administrativa local de recuperação sem usar o Active Directory';

    public function handle(): int
    {
        $username = trim((string) ($this->argument('username') ?: $this->ask('Nome de acesso local')));
        $name = trim((string) ($this->option('name') ?: $this->ask('Nome de exibição')));

        if (! preg_match('/^[A-Za-z0-9._-]{3,64}$/', $username)) {
            $this->error('O nome de acesso deve ter de 3 a 64 caracteres: letras, números, ponto, hífen ou sublinhado.');
            return self::FAILURE;
        }

        if ($name === '') {
            $this->error('O nome de exibição é obrigatório.');
            return self::FAILURE;
        }

        if (User::query()->where('username', $username)->exists()) {
            $this->error('Já existe uma conta com este nome de acesso.');
            return self::FAILURE;
        }

        do {
            $password = (string) $this->secret('Senha local de recuperação');
            $confirmation = (string) $this->secret('Confirme a senha local de recuperação');

            $valid = strlen($password) >= 14
                && preg_match('/[A-Z]/', $password)
                && preg_match('/[a-z]/', $password)
                && preg_match('/\d/', $password)
                && preg_match('/[^A-Za-z0-9]/', $password);

            if (! $valid) {
                $this->warn('A senha deve ter no mínimo 14 caracteres, maiúscula, minúscula, número e símbolo.');
            } elseif ($password !== $confirmation) {
                $this->warn('As senhas informadas não coincidem.');
                $valid = false;
            }
        } while (! $valid);

        User::query()->create([
            'name' => $name,
            'username' => $username,
            'email' => $this->option('email') ?: null,
            'password' => Hash::make($password),
            'auth_source' => 'local',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->info('Conta local de recuperação criada. A senha não foi exibida nem gravada no histórico.');

        return self::SUCCESS;
    }
}
