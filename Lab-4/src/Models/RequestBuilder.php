<?php

namespace Src\Models;

class RequestBuilder
{
    private $stmt;
    private $prms;
    private $context; // Переданные параметры запроса

    public function __construct($stmt_base, array $context)
    {
        $this->stmt = $stmt_base;
        $this->prms = array();
        $this->context = $context;
    }

    public function stringFuzzy($field): RequestBuilder
    {
        // Если параметр для заданного поля не пустой
        if (
            $field !== "" and
            isset($this->context[$field]) and
            $this->context[$field] !== ""
        ) {
            $this->stmt .= "AND $field=:$field ";
            $this->prms[":$field"] = $this->context[$field];
        }
        return $this;
    }

    public function range($field): RequestBuilder
    {
        if (
            $field !== "" and
            isset($this->context[$field]) and
            isset($this->context[$field]['from']) and $this->context[$field]['from'] !== "" and
            isset($this->context[$field]['to']) and $this->context[$field]['to'] !== ""
        ) {
            $this->stmt .= "AND $field BETWEEN :$field" . "_from" . " AND :$field" . "_to" . " ";
            $this->prms[":$field" . "_from"] = $this->context[$field]['from'];
            $this->prms[":$field" . "_to"] = $this->context[$field]['to'];
        }
        return $this;
    }

    public function exact($field): RequestBuilder{
        if (
            $field !== "" and
            isset($this->context[$field]) and
            $this->context[$field] != ""
        ){
            $this->stmt .= "AND $field = :$field";
            $this->prms[":$field"] = $this->context[$field];
        }
        return $this;
    }

    public function build(): array {
        // Возвращает пару (stmt, params)
        return array($this->stmt, $this->prms);
    }
}

?>