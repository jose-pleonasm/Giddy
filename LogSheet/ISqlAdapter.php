<?php

/**
 * Rozhrani SQL adapteru pro LogSheet
 *
 * @link       https://github.com/jose-pleonasm/Giddy
 * @category   Giddy
 * @package    Giddy_LogSheet
 * @author     Josef Hrabec
 * @version    $Id: ISqlAdapter.php, 2009-12-29 12:59 $
 */

/**
 * Interface SQL Adapteru pro LogSheet
 *
 * @package    Giddy
 * @subpackage LogSheet
 * @author     Josef Hrabec
 * @version    Release: 0.5
 * @since      Trida je pristupna od verze 0.5
 */
interface LogSheet_ISqlAdapter
{
  const TABLE = '_logs';

  /**
   * Zjisti zda tabulka existuje
   *
   * @return unknown_type
   */
  public function tableExists();

  /**
   * Vytvori tabulku pro logy
   *
   * @return void
   */
  public function createTable();

  /**
   * Vlozi pole do tabulky
   * Jedna polozka pole reprezentuje jeden radek tabulky
   *
   * @param  array  $array
   * @return void
   */
  public function insertArray($array);

  /**
   * Vykona SQL dotaz
   *
   * @param  string  $query
   * @param  int     $resultType
   * @return mixed
   */
  public function query($query, $resultType = NULL);

  /**
   * Vykonna SQL dotaz a vysledek vrati jako pole
   * @param  string  $query
   * @param  mixed   $resultType
   * @return array
   */
  public function arrayQuery($query, $resultType = NULL);

  /**
   * Odstrani tabulku s logy/DB
   *
   * @return void
   */
  public function clear();
}
