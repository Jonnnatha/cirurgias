# Cirurgias

Sistema Laravel para gerenciar solicitações de cirurgias com agendamento por sala, checklists e uma API de calendário.

## Requisitos

- PHP 8.1+
- Node.js 18+
- Banco de dados MySQL ou compatível
- Composer e npm

## Instalação

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate --seed
```

## Desenvolvimento

Execute o backend e o frontend em terminais separados:

```bash
php artisan serve        # http://localhost:8000
npm run dev              # compila os assets com Vite
```

## Testes

```bash
php artisan test
```

## Recursos

- **Agendamento por sala**: cada solicitação registra um número de sala (1–9) com validação para evitar sobreposições de horário.
- **Checklists**: administradores e enfermeiros gerenciam modelos de checklist e marcam itens para cada cirurgia.
- **API de calendário**: consulte cirurgias por sala e intervalo de datas; veja [docs/calendar.md](docs/calendar.md).

## Login padrão

Após `php artisan migrate --seed` um usuário admin é criado:

- `nome`: Admin
- `password`: 123

## Solução de problemas

- Garanta que as versões de PHP e Node atendam aos requisitos.
- Verifique as credenciais do banco no `.env` antes de rodar as migrações.
- Para mais detalhes do framework consulte a [documentação do Laravel](https://laravel.com/docs).

