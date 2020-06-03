<?php

/**
 * Controlador da Classe Admin
 * responsavel pelo gerenciamento 
 * das paginas da classe Admin
 */
class AdminController extends Admin
{

    public function admins($method, $route) //parâmetros recebidos do castHandler (Opcional) 
    {
        AdminControllerData::getAllAdmins();
    }
}


/**
 * Responsavel pelas requisições ao ORM
 */
class AdminControllerDB extends AdminController
{

    public static function getAll()
    {
        $response = $GLOBALS["ORM"]->getAll(new Admin);

        return $response->status ? $response->data : [];
    }
}

/**
 * Responsavel pelas regras envolvidas 
 * com a classe Admin (verificações de permições, tratamento de expt e etc)
 */
class AdminControllerData extends AdminController
{


    /**
     * 
     * verifica se o usuario tem permição para 
     * executar esta ação
     *
     * @return array
     */
    public static function getAllAdmins()
    {
        if (!self::verifyAuth())
            Pages::ERROR403();

        $data = AdminControllerDB::getAll();
        return (isset($data[0])) ? Returns::msgData(Returns::BUSCA_SUCCESS, $data) : Returns::simpleMsgError(Returns::BUSCA_EMPTY);
    }

    /**
     * 
     * verifica se o usuario tem permição para 
     * executar esta ação
     *
     * @return bool
     */
    public static function verifyAuth()
    {
    }
}
