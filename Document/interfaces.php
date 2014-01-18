<?php

interface Document_Basic {}

/**
 * Tabulkovy
 */
interface Tabular extends Document_Basic
{
  public function addTableValue($value);

  public function tableRowMove();

  public function setCellValue($column, $row, $value);
}

/**
 * Strukturalni
 */
interface Structural extends Document_Basic
{
  public function addHeadline($text, $level = 1);

  public function addP($text);
}
