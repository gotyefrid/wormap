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

            // Контейнер для кнопок
            $(document).find('.buttons-container').remove();
            let container = $('<div class="buttons-container"></div>');

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
                let buttonClass = item.active ? 'btn active' : 'btn inactive';

                // Если элемент центральный, добавляем класс "me-point"
                if (index === centerIndex) {
                    buttonClass += ' me-point';
                }

                // Создаем кнопку с data-атрибутами
                let button = $(`<button class="${buttonClass}" data-x="${item.x}" data-y="${item.y}">${item.x}-${item.y}</button>`);

                // Добавляем кнопку в последний созданный ряд
                container.find('.row:last').append(button);
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

        // Обработчик клика на кнопки
        $(document).on('click', '.btn.active', function (e) { // Только активные кнопки
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
    .buttons-container {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .row {
        display: flex;
        gap: 5px;
    }

    .btn {
        padding: 10px;
        border: 1px solid #333;
        cursor: pointer;
        min-width: 50px;
        text-align: center;
    }

    /* Активные кнопки */
    .btn.active {
        background-color: #4CAF50; /* Зеленый цвет */
        color: white;
    }

    /* Неактивные кнопки */
    .btn.inactive {
        background-color: #ccc; /* Серый цвет */
        color: #666;
        cursor: not-allowed;
    }

    /* Центральная кнопка (позиция пользователя) */
    .btn.me-point {
        border: 2px solid blue;
        font-weight: bold;
    }

</style>
</body>
</html>