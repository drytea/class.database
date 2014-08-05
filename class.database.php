<?php

class db
{
    public static $instance;

    public function getInstance()
    {
        if (!self::$instance) {

            try {
                self::$instance = new PDO("mysql:host=localhost;dbname=DB_NAME", "USER_NAME", "PASSWORD");

            } catch (PDOException $e) {

                $error = $e->getMessage();

                echo ' Veri Tabanı Hatası ' . $error;
            }
        }

        return self::$instance;
    }
}

class Sql extends db
{

    public function Select($table_colum, $table_name, $inner_join, $table_name_colum, $where, $where_opt = "and", $order, $limit)
    {
        if (is_array($table_colum)) {

            $sql = " SELECT " . $table_colum . " FROM " . $table_name;
        } else {

            $sql = "SELECT * FROM " . $table_name;
        }

        if (is_array($inner_join)) {

            $values = array_values($inner_join);
            $keys = array_keys($inner_join);

            for ($i = 0; $i < count($values); $i++) {

                if ($i == count($values) - 1) {
                    $sql .= " INNER JOIN " . $keys[$i] . " ON " . $keys[$i] . "." . $values[$i] . " = " . $table_name . "." . $table_name_colum[$i];
                } else {
                    $sql .= " INNER JOIN " . $keys[$i] . " ON " . $keys[$i] . "." . $values[$i] . " = " . $table_name . "." . $table_name_colum[$i];
                }
            }
        }
        if (is_array($where)) {

            $sql .= " WHERE ";
            $values = array_values($where);
            $keys = array_keys($where);

            for ($i = 0; $i < count($values); $i++) {

                if ($i == count($values) - 1) {
                    $sql .= $values[$i] . " = " . $keys[$i];
                } else {

                    $sql .= $values[$i] . " = " . $keys[$i] . " " . $where_opt . " ";
                }
            }
        }

        if (is_array($order)) {

            $values = array_values($order);
            $keys = array_keys($order);

            $sql .= " ORDER BY " . $keys[0] . " " . $values[0];
        }

        if (is_array($limit)) {

            $values = array_values($limit);
            $keys = array_keys($limit);

            $sql .= " LIMIT " . $keys[0] . "," . $values[0];
        }

        $results = parent::getInstance()->prepare($sql);
        $results->execute();

        return $results;
    }

    public function Insert($table_name, $insert)
    {
        $keys = implode(", ", array_keys($insert));
        $values = array_values($insert);
        $valueCount = count($values);

        $str = '?';
        $str .= str_repeat(", ?", $valueCount - 1);

        $sql = " INSERT INTO " . $table_name . "(" . $keys . ") VALUES (" . $str . ")";

        $results = parent::getInstance()->prepare($sql);
        $results->execute($values);

        return $results;
    }

    public function Update($table_name, $update, $where)
    {

        $sql = " UPDATE " . $table_name . " SET ";

        $values = array_values($update);
        $keys = array_keys($update);

        for ($i = 0; $i < count($keys); $i++) {
            if ($i == count($keys) - 1) {
                $sql .= $keys[$i] . " = " . " ? WHERE ";
            } else {
                $sql .= $keys[$i] . " = " . " ? ,";
            }
        }
        if (is_array($where)) {

            $where_keys = array_keys($where);
            $where_values = array_values($where);

            for ($i = 0; $i < count($where_keys); $i++) {

                if ($i == count($where_keys) - 1) {
                    $sql .= $where_keys[$i] . " = " . " ' " . $where_values[$i] . " ' ";
                } else {
                    $sql .= $where_keys[$i] . " = " . " ' " . $where_values[$i] . " ' " . " and " . " ";
                }

            }

        } else {
            $sql .= " id = " . " ' " . $where . " ' ";
        }

        $results = parent::getInstance()->prepare($sql);
        $results->execute($values);

        return $results;

    }

    public function Delete($table_name, $delete, $delOpt = 'and')
    {
        $sql = " DELETE FROM " . $table_name . " WHERE ";

        if (is_array($delete)) {

            $keys = array_keys($delete);
            $values = array_values($delete);

            for ($i = 0; $i < count($keys); $i++) {
                if ($i == count($keys) - 1) {
                    $sql .= $keys[$i] . " " . " = " . " ?";
                } else {
                    $sql .= $keys[$i] . " " . " = " . " ? " . $delOpt . " ";
                }
            }

        } else {

            $values = array($delete);
            $sql .= " id = ? ";

        }

        try {

            $result = parent::getInstance()->prepare($sql);
            $result->execute($values);
            $count = $result->rowCount();

            if ($count) {

                return $count;

            } else {

                return false;

            }

        } catch (PDOException $e) {
            return 'Sorgu Hatası : ' . $e->getMessage() . "</br>";
        }

    }
}

