<p align="center"><a href="https://www.agapesolucoes.com.br/" target="_blank"><img src="https://www.agapesolucoes.com.br/media/logos/AGP/logo-blue.svg" width="400"></a></p>

<br>
<p align="center">
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>
<br>

## Sobre Modelo

Esse pacote foi criado com o intuito de ajudar a você criar seu próprio package. 

Existem diferentes tipos de pacotes. Alguns pacotes são independentes, o que significa que funcionam com qualquer estrutura PHP. Esses pacotes podem ter rotas, controllers, composers, models, views e configurações especificamente destinadas a aprimorar um aplicativo Laravel.

<br>

## Como criar o seu Package

Se você é desenvolvedor e sente a necessidade de criar um novo package é necessário que primeiramente o gestor de projetos crie um repositório com o modelo vazio onde você pode começar do zero.

O modelo vazio vem com uma estrutura similar a que você está vendo aqui em baixo.


```bash
    agp
    └── nome_pacote
        ├── src
        │    └── Agp
        │       └── NomePacote
        │           ├── Controller
        │           ├── Form
        │           ├── Model
        │           ├── Routes
        │           ├── Views
        │           └── Agp <NomePacote> ServiceProvider.php
        │
        ├── composer.json
        └── README.md
```

Para que ele funcione em seu projeto de desenvolvimento precisamos dizer ao ```composer.json``` para carregar automaticamente nossos arquivos, adicione este código ao seu ```composer.json```:
            
```bash         
          "autoload": {
               "psr-4": {
                   "agp\\nome_pacote\\": "src/"
               }
           }
```

Ou em seu proprio composer.json do pacote adicione: 

```bash 
        "extra": {
            "laravel": {
                "providers": [
                    "Agp\\NomePacote\\Agp <NomePacote> ServiceProvider"
                ]
            }
        }
```

E pronto, agora só depende de você. Seja criativo! 😉

<br>
<br>

> por __Richard Pereira Cardoso__.

#### Referências do Pacote Modelo:
[publish-laravel-packagist](https://pusher.com/tutorials/publish-laravel-packagist)  - Último acesso em 25/09/2020 as 06:46.
