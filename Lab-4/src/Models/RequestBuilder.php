<?php

namespace src\Models;

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

    public function stringFuzzy($field, $mask = null): RequestBuilder
    {
        // Если параметр для заданного поля не пустой
        if (
            $field !== "" and
            isset($this->context[$field]) and
            $this->context[$field] !== ""
        ) {
            $this->stmt .= "AND LOWER(".($mask ?? $field).") LIKE CONCAT(\"%\",LOWER(:$field),\"%\") ";
            $this->prms[":$field"] = $this->context[$field];
        }
        return $this;
    }

    public function range($field, $mask=null): RequestBuilder
    {
        // field - how it is written in filter
        // mask - how it will be presented in SLQ query
        if ($field !== "" and isset($this->context[$field])){
            $lowerbound = $this->context[$field]['from'] ?? "";
            $upperbound = $this->context[$field]['to'] ?? "";
            if ($lowerbound == "" and $upperbound !== ""){
                // Only upperbound
                $this->stmt .= "AND ".($mask??$field)." <= :$field" . "_to" . " ";
                $this->prms[":$field" . "_to"] = $upperbound;
            }
            if ($lowerbound != "" and $upperbound == ""){
                // Only lowerbound
                $this->stmt .= "AND ".($mask??$field)." >= :$field" . "_from" . " ";
                $this->prms[":$field" . "_from"] = $lowerbound;
            }
            if ($lowerbound !== "" and $upperbound !== ""){
                // Both
                $this->stmt .= "AND ".($mask??$field)." BETWEEN :$field" . "_from" . " AND :$field" . "_to" . " ";
                $this->prms[":$field" . "_from"] = $lowerbound;
                $this->prms[":$field" . "_to"] = $upperbound;
            }
        }
        return $this;
    }

    public function exact($field, $mask = null): RequestBuilder
    {
        if (
            $field !== "" and
            isset($this->context[$field]) and
            $this->context[$field] != ""
        ) {
            $this->stmt .= "AND ". ($mask ?? $field) ." = :$field ";
            $this->prms[":$field"] = $this->context[$field];
        }
        return $this;
    }

    public function build(): array
    {
        // Возвращает пару (stmt, params)
        return array($this->stmt, $this->prms);
    }
}

?>