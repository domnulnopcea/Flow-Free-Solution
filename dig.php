<?php

echo "Finding solution..." . PHP_EOL;

$input = [];
$colors = [];
$colorsInitialPosition = [];
$output = [];
$rows = -1;
$cols = -1;
$allColoursConnected = false;
$paths = [];
$temporaryPath = [];

const MAX_DIRECTIONS = 4;
const DIRECTION_UP = 0;
const DIRECTION_RIGHT = 1;
const DIRECTION_DOWN = 2;
const DIRECTION_LEFT = 3;

function checkOutOfBounds($position) {
    global $cols, $rows;

    $isValid = true;

    if ($position[0] < 0 || $position[0] > $rows || $position[1] < 0 || $position[1] > $cols) {
        $isValid = false;
    }

    return $isValid;
}

function isValid($colorIndex) {
    global $paths, $input, $colors, $temporaryPath, $colorsInitialPosition;

    if (count($temporaryPath[$colorIndex])) {
        $lastPosition = $temporaryPath[$colorIndex][count($temporaryPath[$colorIndex]) - 1];
    } else {
        $lastPosition = $colorsInitialPosition[$colorIndex];
    }
    $isValid = checkOutOfBounds($lastPosition);

    if (!$isValid) {
        return false;
    }

    // do not land on a different color
    if ($input[$lastPosition[0]][$lastPosition[1]] != '*' && $input[$lastPosition[0]][$lastPosition[1]] != $colors[$colorIndex]) {
        return false;
    }

    if (count($temporaryPath[$colorIndex]) > 1) {
        // check not to turn back on the same road :)
        for ($j = 0; $j < count($temporaryPath[$colorIndex]) - 1; $j++) {
            $element = $temporaryPath[$colorIndex];
            if ($element[$j][0] == $lastPosition[0] && $element[$j][1] == $lastPosition[1]) {
                return false;
            }
        }

        // check not to cross an already found path
        for ($j = 0; $j < count($paths); $j++) {
            for ($p = 0; $p < count($paths[$j]['position']); $p++) {
                if ($paths[$j]['position'][$p][0] == $lastPosition[0] && $paths[$j]['position'][$p][1] == $lastPosition[1]) {
                    return false;
                }
            }
        }
    }

    return $isValid;
}

function solution($colorIndex, $k) {
    global $colorsInitialPosition, $input, $temporaryPath;

    $lastPosition = $temporaryPath[$colorIndex][count($temporaryPath[$colorIndex]) - 1];

    return ($k >= 1 &&
        $input[$colorsInitialPosition[$colorIndex][0]][$colorsInitialPosition[$colorIndex][1]] == $input[$lastPosition[0]][$lastPosition[1]]);
}

function savePath($colorIndex, $direction, $k) {
    global $colorsInitialPosition, $paths;

    $lastPosition = $colorsInitialPosition[$colorIndex];
    $paths[$colorIndex] = [];
    $paths[$colorIndex]['position'] = [];
    for ($i = 0; $i < $k; $i++) {
        switch ($direction[$i]) {
            case DIRECTION_UP:
                $lastPosition[0] = $lastPosition[0] - 1;
                break;

            case DIRECTION_RIGHT:
                $lastPosition[1] = $lastPosition[1] + 1;
                break;

            case DIRECTION_DOWN:
                $lastPosition[0] = $lastPosition[0] + 1;
                break;

            case DIRECTION_LEFT:
                $lastPosition[1] = $lastPosition[1] - 1;
                break;
        }
        $paths[$colorIndex]['position'][] = $lastPosition;
    }

    $paths[$colorIndex]['direction'] = $direction;
    $paths[$colorIndex]['length'] = $k;
}

function calculatePosition($colorIndex, $k, $direction) {

    global $colorsInitialPosition, $temporaryPath;

    if (!isset($temporaryPath[$colorIndex]) || !count($temporaryPath[$colorIndex])) {
        $lastPosition = $colorsInitialPosition[$colorIndex];
        $temporaryPath[$colorIndex] = [];
        $temporaryPath[$colorIndex][] = $lastPosition;
    }
    $lastPosition = $temporaryPath[$colorIndex][count($temporaryPath[$colorIndex]) - 1];

    switch ($direction[$k]) {
        case DIRECTION_UP:
            $lastPosition[0] = $lastPosition[0] - 1;
            break;

        case DIRECTION_RIGHT:
            $lastPosition[1] = $lastPosition[1] + 1;
            break;

        case DIRECTION_DOWN:
            $lastPosition[0] = $lastPosition[0] + 1;
            break;

        case DIRECTION_LEFT:
            $lastPosition[1] = $lastPosition[1] - 1;
            break;
    }
    $temporaryPath[$colorIndex][] = $lastPosition;
}

function findSolution()
{
    global $colors, $allColoursConnected, $paths, $input, $temporaryPath;

    $colorIndex = 0;
    $resumeLastPath = false;

    while (!$allColoursConnected) {
        $foundOneSolution = false;

        if (!$resumeLastPath) {
            $k = 0;
            $d[$k] = -1;
        }
        while ($k >= 0) {
            $d[$k] = $d[$k] + 1;
            if ($d[$k] < MAX_DIRECTIONS) {
                calculatePosition($colorIndex, $k, $d);
                if (isValid($colorIndex)) {
                    if (solution($colorIndex, $k)) {
                        if ($colorIndex == count($colors) - 1) {
                            // print solution
                            echo "Output data is" . PHP_EOL;
                            savePath($colorIndex, $d, $k);
                            for ($i = 0; $i < count($paths); $i++) {
                                for ($j = 0; $j < count($paths[$i]['position']); $j++) {
                                    $row = $paths[$i]['position'][$j][0];
                                    $column = $paths[$i]['position'][$j][1];
                                    $input[$row][$column] = $colors[$i];
                                }
                            }
                            foreach ($input as $line) {
                                echo implode(' ', $line) . PHP_EOL;
                            }
                            die();
                        }
                        savePath($colorIndex, $d, $k);
                        $colorIndex++;

                        echo $colorIndex . PHP_EOL;
                        echo "Steps: " . $k . ", path: " . implode(", ", $d) . PHP_EOL;
                        $k = -1;
                        $d = [];
                        $foundOneSolution = true;
                        $resumeLastPath = false;
                        $temporaryPath[$colorIndex] = [];
                    } else {
                        $k = $k + 1;
                        $d[$k] = -1;
                    }
                } else {
                    unset($temporaryPath[$colorIndex][$k + 1]);
                    $temporaryPath[$colorIndex] = array_values($temporaryPath[$colorIndex]);
                }
            } else {
                $temporaryPath[$colorIndex] = array_slice($temporaryPath[$colorIndex], 0, $k);
                $k = $k - 1;
                $resumeLastPath = false;
                if (!count($temporaryPath[$colorIndex])) {
                    unset($temporaryPath[$colorIndex]);
                }
            }
        }

        if (!$foundOneSolution) {
            unset($temporaryPath[$colorIndex]);
            $colorIndex = $colorIndex - 1;
            unset($temporaryPath[$colorIndex][count($temporaryPath[$colorIndex]) - 1]);
            $temporaryPath[$colorIndex] = array_values($temporaryPath[$colorIndex]);
            if ($colorIndex == -1) {
                echo "No solution found" . PHP_EOL;
                die();
            }
            $d = $paths[$colorIndex]['direction'];
            $k = $paths[$colorIndex]['length'];
            unset($paths[$colorIndex]);
            $resumeLastPath = true;
        }
    }
}

function readData() {
    global $input, $colors, $colorsInitialPosition, $rows, $cols;
    $handle = fopen(__DIR__ . '/input_4.txt', 'r');
    echo "Input data is" . PHP_EOL;

    while (($buffer = fgets($handle)) !== false) {
        echo $buffer;
        $rows++;
        $data = explode(" ", $buffer);
        $cols = count($data) - 1;
        foreach ($data as $index => $cell) {
            $cell = trim($cell);
            if ($cell != '*' && $cell != ' ' && !in_array($cell, $colors)) {
                $colors[] = $cell;
                $colorsInitialPosition[] = [$rows, $index];
            }
        }
        $input[] = array_map("trim", $data);
    }

    echo PHP_EOL;
}

readData();
findSolution();