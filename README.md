
# Diretório MenoTech

Aplicação para gerenciar e exibir profissionais certificados pela MenoTech.

## Estrutura do Projeto

```
diretoriomenotech/
├── admin/                  # Painel administrativo
│   ├── adicionar.php       # Adicionar profissional
│   ├── deletar.php         # Deletar profissional
│   ├── editar.php          # Editar profissional
│   ├── index.php           # Dashboard
│   ├── login.php           # Login admin
│   └── logout.php          # Logout
├── assets/                 # Arquivos estáticos
│   ├── css/
│   ├── js/
│   └── images/             # Coloque o selo-certificado.png aqui
├── uploads/                # Arquivos enviados (fotos dos profissionais)
├── ajax-filtrar.php        # Handler AJAX para filtros
├── config.php              # Configurações do banco de dados
├── database.sql            # Estrutura do banco de dados
├── index.php               # Página pública do diretório
└── perfil.php              # Página individual do profissional
```

## Instalação

### 1. Banco de Dados

1. Acesse o phpMyAdmin (http://localhost/phpmyadmin)
2. Crie um novo banco de dados com o nome `diretoriomenotech`
3. Importe o arquivo `database.sql` para criar as tabelas e o usuário admin padrão

### 2. Configuração

Edite o arquivo `config.php` se necessário (credenciais do banco de dados):

```php
$host = 'localhost';
$dbname = 'diretoriomenotech';
$username = 'root';
$password = '';
```

### 3. Selo Certificado

Coloque a imagem do selo certificado na pasta `assets/images/` com o nome `Selo_MenoTech_Principal_Vinho.png`.

### 4. Permissões

Certifique-se que a pasta `uploads/` tem permissão de escrita (no Windows isso geralmente já está okay).

## Acesso

- **Pública**: http://localhost/diretoriomenotech/
- **Admin**: http://localhost/diretoriomenotech/admin/
- **Credenciais padrão**:
  - Usuário: `admin`
  - Senha: `admin123`

## Funcionalidades

- Listagem de profissionais com filtros (especialidade, estado, busca por nome)
- Página individual de cada profissional
- Painel administrativo para gerenciar profissionais
- Upload de fotos
- Links para redes sociais e contato

## Especialidades Disponíveis

- Ginecologista
- Endocrinologista
- Nutricionista
- Psicóloga
- Psiquiatra
- Fisioterapeuta
- Educadora Física
- Farmaceutica
- Enfermeira
- Coach de Menopausa
- Terapeuta Holística
