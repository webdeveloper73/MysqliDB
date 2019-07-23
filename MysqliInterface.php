<?php
/**
 * Database Interface for the MysqliDB class
 * @author steve
 *
 */
Interface MysqliInterface
{   
    public function rawQuery($query);
    public function insert($tbl,$data,$escape);
    public function insertID();
    public function update($tbl,$data,$escape);
    public function delete();
    public function escape($str);
    public function num_rows();
    public function select($field);
    public function selectCustom($select);
    public function from($tbl);
    public function where($field,$value,$operator,$separator,$escape);
    public function whereCustom($where,$separator);
    public function whereByArray($data,$separator,$escape);
    public function whereIN($field,$data,$separator);
    public function whereNotIN($field,$data,$separator);
    public function limit($limit,$offset);
    public function order_by($field,$sort);
    public function result();
    public function row();
    public function run();
    public function getLastQuery();
}