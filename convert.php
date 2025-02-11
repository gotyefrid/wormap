<?php
function splitImage($imagePath, $outputDir, $rows = 15, $cols = 15) {
    // Проверяем существование исходного изображения
    if (!file_exists($imagePath)) {
        die("Файл изображения не найден.");
    }

    // Создаём выходную папку, если её нет
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    // Загружаем изображение
    $image = imagecreatefrompng($imagePath);
    if (!$image) {
        die("Ошибка загрузки изображения.");
    }

    $width = imagesx($image);
    $height = imagesy($image);

    $tileWidth = $width / $cols;
    $tileHeight = $height / $rows;

    for ($row = 1; $row < $rows; $row++) {
        for ($col = 1; $col < $cols; $col++) {
            $tile = imagecreatetruecolor($tileWidth, $tileHeight);
            imagecopy($tile, $image, 0, 0, $col * $tileWidth, $row * $tileHeight, $tileWidth, $tileHeight);

            $tilePath = sprintf("%s/tile_%d_%d.png", $outputDir, $row, $col);
            imagepng($tile, $tilePath);
            imagedestroy($tile);
        }
    }

    imagedestroy($image);
    echo "Разбиение завершено! Фрагменты сохранены в $outputDir";
}

// Использование:
$sourceImage = 'map.png'; // Замените на путь к вашему изображению
$outputDirectory = 'output_tiles';
splitImage($sourceImage, $outputDirectory, 10, 10);
?>
