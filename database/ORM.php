<?php

/**
 * @author Jonas Lima
 * @version 1.3.0
 */

class ORM
{
    /* INICIO VARIAVEIS */

    /* INICIO DAS VARIAVEIS PRIVADAS */

    public $conn;

    /* FIM DAS VARIAVEIS PRIVADAS */

    /* INICIO DAS VARIAVEIS PUBLICAS */

    public $data;

    /* FIM DAS VARIAVEIS PUBLICAS */

    /* FIM VARIAVEIS */

    /* INICIO METODOS */

    /* METODO CONSTRUTOR */
    public function __construct()
    {
        $this->allRequires();
    }
    /* FIM METODO CONSTRUTOR */

    /* INICIO DOS METODOS PUBLICOS */

    /* METODO PARA LIGAÇÃO COM BANCO DE DADOS */
    public function create($model)
    {
        $conn = new Conn(
            $model->host,
            $model->user,
            $model->password,
            $model->database
        );

        if (!$conn || !$conn->conn) {
            $this->conn = $conn;
            Returns::simpleMsgError(utf8_encode($conn->error));
        } else {
            $this->conn = $conn;
            return (object) [
                "status" => true,
                "error" => null
            ];
        }
    }
    /* FIM DO METODO DE LIGAÇÃO */

    /* INICIO DOS METODOS DE SELEÇÃO */

    public function getAll($model)
    {
        $class = $this->getClassName($model);
        if (!$class) {
            return $this->returnError("Class Name not found!");
        }

        $res = $this->conn->select("*", strtolower($class));

        if (!$res && $this->conn->error != null) {
            $er = $this->conn->error;
            $this->conn->error = null;
            return $this->returnError($er);
        } else {
            if (!isset($res[0])) {
                return $this->returnError("Nenhum resultado encontrado!");
            } else {
                $idt['id'] = "*";

                $res = $this->fetchResults($res, $class);

                return $this->returnSucess($res);
            }
        }
    }

    /* Esse metodo suporta  =, !=,  LIKE, NOT LIKE, BETWEEN, NOT BETWEEN*/
    public function getAny($model)
    {
        $class = $this->getClassName($model);

        if (!$class) {
            return $this->returnError("Class Name not found!");
        }

        $condition = $this->buildConditionAny($model);
        if (isset($condition->error)) {
            return $this->returnError($condition->error);
        }
        $res = $this->conn->select("*", strtolower($class), $condition);

        if (!$res && $this->conn->error != null) {
            $er = $this->conn->error;
            $this->conn->error = null;
            return $this->returnError($er);
        } else {
            if (!isset($res[0])) {
                return $this->returnError("Nenhum resultado encontrado!");
            } else {
                $res = $this->fetchResults($res, $class);

                return $this->returnSucess($res);
            }
        }
    }

    /* FIM DOS METODOS DE SELEÇÃO */

    /* INICIO DOS METODOS DE INSERÇÃO */

    public function insertOne($model)
    {
        $class = $this->getClassName($model);

        if (!$class) {
            return $this->returnError("Class Name not found!");
        }

        $data = $this->buildInsertArray($model);

        $res = $this->conn->insert(
            strtolower($class),
            $data[0],
            $data[1]
        );

        if (!$res && $this->conn->error != null) {
            $er = $this->conn->error;
            $this->conn->error = null;
            return $this->returnError($er);
        } else {
            $GLOBALS['mysqli_insert_id']
                = mysqli_insert_id($this->conn->conn);
            $idt['id'] = mysqli_insert_id($this->conn->conn);
            self::log(strtolower($class), $idt, 'Inseriu');
            return $this->returnSucess(
                (object) [
                    "msg" => "Inserido com Sucesso"
                ]
            );
        }
    }

    public function insertN($arrayModels)
    {
        $i = 0;
        $return = array();
        while (isset($arrayModels[$i])) {
            $model = $arrayModels[$i];
            $class = $this->getClassName($model);

            if (!$class) {
                array_push(
                    $return,
                    $this->returnError("Class Name not found!")
                );
            } else {
                $data = $this->buildInsertArray($model);

                $res = $this->conn->insert(
                    strtolower($class),
                    $data[0],
                    $data[1]
                );
                if (!$res && $this->conn->error != null) {
                    $er = $this->conn->error;
                    $this->conn->error = null;
                    array_push($return, $this->returnError($er));
                } else {
                    $idt['id'] = mysqli_insert_id($this->conn->conn);
                    self::log(strtolower($class), $idt, 'Inseriu');
                    array_push(
                        $return,
                        $this->returnSucess(
                            (object) [
                                "msg" => "Inserido com Sucesso"
                            ]
                        )
                    );
                }
            }
            $i++;
        }

        return $return;
    }

    /* FIM DOS METODOS DE INSERÇÃO */

    /* INICIO DOS METODOS DE ATUALIZAÇÃO */

    public function updateOne($model)
    {
        $class = $this->getClassName($model);

        if (!$class) {
            return $this->returnError("Class Name not found!");
        }

        $data = $this->buildConditionUpdate($model);
        if (isset($data["cond"])) {
            $cond = $data["cond"];
        } else {
            $cond = "";
        }
        $res = $this->conn->update(
            strtolower($class),
            $data["col"],
            $data["val"],
            $cond
        );

        if (!$res && $this->conn->error != null) {
            $er = $this->conn->error;
            $this->conn->error = null;
            return $this->returnError($er);
        } else {
            $idt['condicao'] = $cond;
            self::log(strtolower($class), $idt, 'Atualizou');

            return $this->returnSucess(
                (object) [
                    "msg" => "Atualizado com Sucesso"
                ]
            );
        }
    }

    /* FIM DOS METODOS DE ATUALIZAÇÃO */

    /* INICIO DOS METODOS DE DELEÇÃO */

    public function deleteOne($model)
    {
        $class = $this->getClassName($model);

        if (!$class) {
            return $this->returnError("Class Name not found!");
        }

        $condition = $this->buildConditionDelete($model);

        $res = $this->conn->delete(strtolower($class), $condition);

        if (!$res && $this->conn->error != null) {
            $er = $this->conn->error;
            $this->conn->error = null;
            return $this->returnError($er);
        } else {
            $idt['condicao'] = $condition;
            self::log(strtolower($class), $idt, 'Deletou');
            return $this->returnSucess(
                (object) [
                    "msg" => "Deletado com Sucesso"
                ]
            );
        }
    }

    /* FIM DOS METODOS DE DELEÇÃO */

    /* FIM DOS METODOS PUBLICOS */

    /* INICIO DOS METODOS PRIVADOS */

    /* INICIO DAS FERRAMENTAS MUITO UTILIZADAS */
    private function returnError($error)
    {
        return (object) [
            "status" => false,
            "error" => $error
        ];
    }

    private function returnSucess($data)
    {
        return (object) [
            "status" => true,
            "error" => null,
            "data" => $data
        ];
    }

    private function fetchResults($res, $class)
    {
        $ret = [];
        $i = 0;
        while (isset($res[$i])) {
            $ret[$i] = (object) [];
            foreach ($res[$i] as $key => $value) {
                $ret[$i]->$key = $value;
            }
            $ret[$i] = (object) $ret[$i];
            $i++;
        }

        return $ret;
    }

    private function getClassName($model)
    {
        if (isset($model->class_name)) {
            $class = $model->class_name;
            unset($model->class_name);
        } else {
            $class = get_class($model);
            if ($class == "stdClass") {
                $class = false;
            }
        }
        return $class;
    }

    private static function log($tabela, $idt, $acao)
    {
        if ($tabela != 'notifications') {
            # code...

            $today = date("d-m-Y H:i:s");
            $idt_linha = json_encode($idt);
            $ses = json_encode($_SESSION);
            $idt_linha = mysqli_real_escape_string(
                $GLOBALS['conn'],
                trim($idt_linha)
            );
            $query = "INSERT INTO `logs` ( 
                `tabela`, 
                `idt_linha`, 
                `acao`, 
                `time`, 
                `session_info`
                ) VALUES (
                    '$tabela',
                    '$idt_linha',   
                    '$acao', 
                    '$today', 
                    '$ses'
                )";

            mysqli_query($GLOBALS['conn'], $query);
        }
    }

    /* FIM DAS FERRAMENTAS MUITO UTILIZADAS */

    /* INICIO DOS ESTRUTURADORES DE CONDIÇÃO */

    private function buildInsertArray($model)
    {
        $i = 0;
        foreach ($model as $key => $value) {
            $data[0][$i] = $key;
            $data[1][$i] = $value;
            $i++;
        }
        return $data;
    }

    private function buildConditionAny($model)
    {
        $condition = "";
        $i = 0;
        foreach ($model as $key => $value) {
            if ($key != "properties" && !empty($value)) {
                if ($i != 0) {
                    $condition .= " AND ";
                } else {
                    $condition .= " WHERE ";
                }

                if ($key == "EQUAL") {
                    $j = 0;
                    foreach ($value as $keys => $values) {
                        if ($j != 0) {
                            $condition .= " AND ";
                        }
                        $condition .= $keys . " = '" . $values . "' ";
                        $j++;
                    }
                } elseif ($key == "NOT EQUAL") {
                    $j = 0;
                    foreach ($value as $key => $values) {
                        if ($j != 0) {
                            $condition .= " AND ";
                        }
                        $condition .= $key . " != '" . $values . "' ";
                        $j++;
                    }
                } elseif ($key == "LIKE") {
                    $j = 0;
                    foreach ($value as $key => $values) {
                        if ($j != 0) {
                            $condition .= " AND ";
                        }
                        $condition .= $key . " LIKE '%" . $values . "%' ";
                        $j++;
                    }
                } elseif ($key == "NOT LIKE") {
                    $j = 0;
                    foreach ($value as $key => $values) {
                        if ($j != 0) {
                            $condition .= " AND ";
                        }
                        $condition .= $key . " NOT LIKE '" . $values . "' ";
                        $j++;
                    }
                } elseif ($key == "BETWEEN") {
                    $j = 0;
                    foreach ($value as $key => $values) {
                        if ($j != 0) {
                            $condition .= " AND ";
                        }
                        $condition .=
                            $key .
                            " BETWEEN '" .
                            $values[0] .
                            "' AND '" .
                            $values[1] .
                            "' ";
                        $j++;
                    }
                } elseif ($key == "NOT BETWEEN") {
                    $j = 0;
                    foreach ($value as $key => $values) {
                        if ($j != 0) {
                            $condition .= " AND ";
                        }
                        $condition .=
                            $key .
                            " NOT BETWEEN '" .
                            $values[0] .
                            "' AND '" .
                            $values[1] .
                            "' ";
                        $j++;
                    }
                } else {
                    return (object) [
                        "error" =>
                        "Erro na construção, verifique o parametro enviado: " .
                            $key
                    ];
                }

                $i++;
            }
        }
        if (isset($model->properties)) {
            foreach ($model->properties as $key => $value) {
                if ($key == "LIMIT") {
                    $later = " " . $key . " " . $value . " ";
                } else {
                    if ($value === true) {
                        $condition .= " " . $key . " ";
                    } elseif ($value === false) {
                    } else {
                        $condition .= " " . $key . " " . $value . " ";
                    }
                }
            }
            $condition .= $later;
        }
        return $condition;
    }

    private function buildConditionUpdate($model)
    {
        if (isset($model->identifier)) {
            $condition .= " WHERE ";
            foreach ($model->identifier as $key => $value) {
                $condition .= " `" . $key . "` = '" . $value . "' AND";
            }
            $condition = substr($condition, 0, -3);
        }
        $i = 0;
        foreach ($model->sets as $key => $value) {
            $col[$i] = $key;
            $val[$i] = $value;
            $i++;
        }
        $data["cond"] = $condition;
        $data["col"] = $col;
        $data["val"] = $val;
        return $data;
    }

    private function buildConditionDelete($model)
    {
        if (isset($model->identifier)) {
            $condition .= " WHERE ";
            foreach ($model->identifier as $key => $value) {
                $condition .= " `" . $key . "` = '" . $value . "' AND";
            }
            $condition = substr($condition, 0, -3);
        }

        return $condition;
    }
    /* FIM DOS ESTRUTURADORES DE CONDIÇÃO */

    /* METODO DE LOAD DAS DEPENDENCIAS */
    private function allRequires()
    {
        require_once __DIR__ . '/dependences/Conn.class.php';
    }
    /* FIM METODO DE LOAD DAS DEPENDENCIAS */

    /* FIM DOS METODOS PRIVADOS */

    /* FIM METODOS */
}
