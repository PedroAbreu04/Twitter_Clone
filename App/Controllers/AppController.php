<?php

namespace App\Controllers;

//Os recursos do miniframework

use MF\Controller\Action;
use MF\Model\Container;

class AppController extends Action
{

    public function timeline()
    {

        $this->validaAutenticacao();

        //recuperar tweets
        $tweet = Container::getModel('tweet');

        $tweet->__set('id_usuario', $_SESSION['id']);

        $usuario = Container::getModel('Usuario');
        $usuario->__set('id', $_SESSION['id']);

        $this->view->info_usuario = $usuario->getInfoUsuario();
        $this->view->total_tweets = $usuario->getTotalTweets();
        $this->view->total_seguindo =  $usuario->getTotalSeguindo();
        $this->view->total_seguidores =  $usuario->getTotalSeguidores();

        $total_registros_pagina = 10;
        $pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
        $deslocamento = ($pagina - 1) * $total_registros_pagina;

        $tweets = $tweet->getPorPagina($total_registros_pagina, $deslocamento);
        $this->view->total_de_paginas = ceil($this->view->total_tweets["total_tweet"] / $total_registros_pagina);
        $this->view->pagina_ativa = $pagina;

        $this->view->tweets = $tweets;

        

        $this->render('timeline');
    }

    public function tweet()
    {

        $this->validaAutenticacao();

        $tweet = Container::getModel('Tweet');

        $tweet->__set('tweet', $_POST['tweet']);
        $tweet->__set('id_usuario', $_SESSION['id']);

        $tweet->salvar();

        header('Location: /timeline');
    }

    public function quemSeguir()
    {

        $this->validaAutenticacao();

        $pesquisarPor = isset($_GET['pesquisarPor']) ? $_GET['pesquisarPor'] : '';

        $usuarios = array();

        $usuario = Container::getModel('Usuario');
        $usuario->__set('id', $_SESSION['id']);
        if ($pesquisarPor != '') {

            $usuario->__set('nome', $pesquisarPor);

            $usuarios = $usuario->getAll();
        }
        $this->view->info_usuario = $usuario->getInfoUsuario();
        $this->view->total_tweets = $usuario->getTotalTweets();
        $this->view->total_seguindo =  $usuario->getTotalSeguindo();
        $this->view->total_seguidores =  $usuario->getTotalSeguidores();

        $this->view->usuarios = $usuarios;
        $this->render('quemSeguir');
    }

    public function validaAutenticacao()
    {
        session_start();

        if (!isset($_SESSION['id']) || $_SESSION['id'] == '' || !isset($_SESSION['nome']) || $_SESSION['nome'] == '') {
            header('Location: /?login=erro');
        } else {
        }
    }

    public function acao(){
        $this->validaAutenticacao();

        $acao = isset($_GET['acao']) ? $_GET['acao'] : '';
        $id_usuario_seguindo = isset($_GET['id_usuario']) ? $_GET['id_usuario'] : '';

        $usuario = Container::getModel('Usuario');
        $usuario->__set('id', $_SESSION['id']);

        if($acao == 'seguir'){
            $usuario->seguirUsuario($id_usuario_seguindo);
        }else if($acao == 'deixar_de_seguir'){
            $usuario->deixarSeguirUsuario($id_usuario_seguindo);
        }
        header('Location: /quem_seguir');
    }

    public function remover(){
        $this->validaAutenticacao();
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        $tweet = Container::getModel('Tweet');
        $tweet->__set('id',$id);
        $tweet->remover();
        header('location: /timeline');
    }
}
