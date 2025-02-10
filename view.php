<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="jquery.js"></script>
</head>
<body>
<script>
    $(document).ready(function () {
        function renderField(data) {
            // Сортируем массив сначала по x, потом по y
            data.sort((a, b) => (a.x - b.x) || (a.y - b.y));

            // Контейнер для изображений
            $(document).find('.tiles-container').remove();
            let container = $('<div class="tiles-container"></div>');

            let currentX = null; // Для отслеживания смены ряда

            // Определяем центральную точку
            let size = Math.sqrt(data.length); // Предполагаем, что поле квадратное (3x3, 5x5 и т.д.)
            let centerIndex = Math.floor(data.length / 2); // Индекс центрального элемента

            data.forEach((item, index) => {
                // Если x изменился, создаем новый ряд
                if (currentX !== item.x) {
                    currentX = item.x;
                    container.append('<div class="row"></div>');
                }

                // Определяем класс стиля в зависимости от "active"
                let tileClass = item.active ? 'tile active' : 'tile inactive';

                // Если элемент центральный, добавляем класс "me-point"
                if (index === centerIndex) {
                    tileClass += ' me-point';
                }

                let imageSrc = `output_tiles/tile_${item.x}_${item.y}.png`;
                let img = new Image();
                img.src = item.fictive ? 'output_tiles/default.png' : imageSrc;
                img.onerror = function () {
                    this.onerror = null;
                    this.src = 'output_tiles/default.png';
                };
                let imageElement = $(img).addClass(tileClass).attr({
                    'data-x': item.x,
                    'data-y': item.y,
                    'alt': `Tile ${item.x}-${item.y}`
                });

                // Добавляем изображение в последний созданный ряд
                container.find('.row:last').append(imageElement);
            });

            // Добавляем контейнер в тело страницы (или нужный div)
            $('body').append(container);
        }

        $.ajax({
            url: '/get',
            method: 'GET',
            success: function (data) {
                renderField(data);
            },
        });

        // Обработчик клика на изображения
        $(document).on('click', '.tile.active', function (e) { // Только активные изображения
            e.preventDefault();

            let x = $(this).data('x'); // Получаем x из data-атрибутов
            let y = $(this).data('y'); // Получаем y из data-атрибутов

            $.ajax({
                url: '/set-location',
                method: 'POST',
                data: { x: x, y: y },
                success: function (data) {
                    renderField(data);
                }
            });
        });
    });
</script>

<style>
    .tiles-container {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .row {
        display: flex;
        gap: 1px;
    }

    /* Центральная кнопка (позиция пользователя) */
    img.me-point {
        border: 2px solid blue;
        font-weight: bold;
    }
</style>

</body>
</html>