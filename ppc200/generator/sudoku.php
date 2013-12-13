<?php

class TimeExpiredException extends Exception
{
}

/* Указатель на ячейку в поле */
class CellPointer
{
	public $row;
	public $column;
	public function __construct($row, $column) {
		$this->row = $row;
		$this->column = $column;
	}
}

/* Ячейка */
class Cell
{
	public $value;
	public $variants;
	public $constant;
	public function __construct($value=NULL) {
		if ($value !== NULL) {
			$this->value = $value;
			$this->constant = true;
		}
		$this->variants = array();
	}
}

/* Судоку */
class Sudoku
{

/* время на генерацию */
private static $time_limit = 2;

/* СТРУКТУРА СУДОКУ */

/* поле, адресуется двойным индексом [row][column] */
private $field;
/* группы цветов, цвет -> массив указателей на ячейки поля ( [row,column], [row,column], ... ) */
private $groups;
/* маска групп */
private $mask;
/* строковое представление */
private $string;

/* МЕТОДЫ ПРОВЕРКИ ЯЧЕЕК */

/* true - когда такого значения нет в строке */
private function test_row($row, $column, $value) {
	$b = true;
	for ($i = 0; $i < 9; ++$i)
		if ($this->field[$row][$i]->value === $value) {
			$b = false;
			break;
		}
	return $b;
}

/* true - когда такого значения нет в столбце */
private function test_column($row, $column, $value) {
	$b = true;
	for ($i = 0; $i < 9; ++$i)
		if ($this->field[$i][$column]->value === $value) {
			$b = false;
			break;
		}
	return $b;
}

/* true - когда такого значения нет в группе */
function test_group($row, $column, $value) {
	$b = true;
	$group = $this->groups[(int)($this->mask[$row * 9 + $column]) - 1];
	foreach($group as $cell)
		if ($this->field[$cell->row][$cell->column]->value === $value) {
			$b = false;
			break;
		}
	return $b;
}

/* УДАЛИТЬ ЯЧЕЙКУ ИЗ ГРУППЫ */
private function delete_from_group($row, $column) {
	$group = (int)($this->mask[$row * 9 + $column]) - 1;
	foreach($this->groups[$group] as $index => $cell)
		if (($cell->row === $row) and ($cell->column === $column)) {
			unset($this->groups[$group][$index]);
			break;
		}
}

/* АЛГОРИТМ ЗАПОЛНЕНИЯ */

private function fill() {
	// $start = microtime(true);
	$start_time = time();
	for ($number = 0; $number < 81; ++$number) {
		if ((time() - $start_time) >= self::$time_limit)
			throw new TimeExpiredException();
		$row = intval($number / 9);
		$column = $number % 9;
		if ($this->field[$row][$column]->constant) {
			if ($otkat) {
				$number -= 2;
			}
			continue;
		}
		if ($otkat) {
			$otkat = false;
		} else {
			for ($value = 1; $value <= 9; ++$value) {
				if (
					$this->test_row($row, $column, $value) and
					$this->test_column($row, $column, $value) and
					$this->test_group($row, $column, $value)
				) {
					$this->field[$row][$column]->variants[] = $value;
				}
			}
			shuffle($this->field[$row][$column]->variants);
		}
		$value = array_pop($this->field[$row][$column]->variants);
		if ($value === NULL) {
			$otkat = true;
			$number -= 2;
			$this->delete_from_group($row, $column);
			$this->field[$row][$column]->value = NULL;
			continue;
		}
		$this->field[$row][$column]->value = $value;
		$this->groups[(int)($this->mask[$number]) - 1][] = new CellPointer($row, $column);
	}
}

/* КОНСТРУКТОР (уровень, [решение]) */

public function __construct($mask, $field=NULL) {
	$this->field = array( array(), array(), array(), array(), array(), array(), array(), array(), array() );
	for ($i = 0; $i < 9; ++$i) {
		for ($j = 0; $j < 9; ++$j) {
			$this->field[$i][$j] = new Cell();
		}
	}
	$this->groups = array( array(), array(), array(), array(), array(), array(), array(), array(), array() );
	$this->mask = $mask;
	if ($field === NULL) {
		$first_value = rand(1, 9);
		$this->field[0][0] = new Cell($first_value);
		$this->groups[0][] = new CellPointer(0, 0);
	} else {
		for ($i = 0; $i < 81; ++$i) {
			if ($field[$i] !== '0') {
				$row = intval($i / 9);
				$column = $i % 9;
				$this->field[$row][$column] = new Cell((int)($field[$i]));
				$this->groups[(int)($mask[$i]) - 1][] = new CellPointer($row, $column);
			}
		}
	}
	$this->fill();
}

/* ИНТЕРФЕЙС */

/* удалть $empty ячеек */
public function reduce($empty) {
	$empty_cells = array();
	for ($i = 0; $i < 81; ++$i)
		$empty_cells[] = $i;
	shuffle($empty_cells);
	$empty_cells = array_slice($empty_cells, 0, $empty);
	for ($i = 0; $i < $empty; ++$i) {
		$row = intval($empty_cells[$i] / 9);
		$column = $empty_cells[$i] % 9;
		$this->field[$row][$column]->value = 0;
	}
}

/* char(81), 1-9 цвета, 0 пустая */
public function as_string() {
	$this->string = '';
	for ($row = 0; $row < 9; ++$row) {
		for ($column = 0; $column < 9; ++$column) {
			$this->string .= $this->field[$row][$column]->value;
		}
	}
	return $this->string;
}

}

?>