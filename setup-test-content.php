<?php
/**
 * NewsCore Test Content Setup Script
 * Запуск: Разместить в корне темы и перейти по URL: http://вашсайт.ru/?setup_test_content=1
 */

// Проверка безопасности
if (!isset($_GET['setup_test_content']) || $_GET['setup_test_content'] !== '1') {
    return;
}

if (!current_user_can('manage_options')) {
    wp_die('Требуются права администратора');
}

// Подключаем WordPress
require_once(ABSPATH . 'wp-load.php');

// Проверяем nonce
if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'setup_test_content')) {
    wp_die('Неверный nonce');
}

// Инициализируем импортер
require_once get_template_directory() . '/import-test-content.php';

// Запускаем импорт
$importer = new NewsCore_Test_Content_Importer();
$result = $importer->import_all_content();

// Выводим результат
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройка тестового контента - NewsCore</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f1f1f1;
            color: #333;
            line-height: 1.6;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #0073aa;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #f05a28;
        }
        .result-box {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #0073aa;
        }
        .success { border-color: #28a745; background: #d4edda; }
        .error { border-color: #dc3545; background: #f8d7da; }
        .summary { 
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .summary h3 { color: #495057; margin-bottom: 10px; }
        .summary ul { margin-left: 20px; }
        .summary li { margin-bottom: 5px; }
        .buttons { margin-top: 30px; text-align: center; }
        .button {
            display: inline-block;
            padding: 12px 25px;
            background: #0073aa;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 0 10px;
            transition: background 0.3s;
        }
        .button:hover { background: #005a87; }
        .button-secondary { background: #6c757d; }
        .button-secondary:hover { background: #545b62; }
        .progress-bar {
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0073aa, #f05a28);
            width: 100%;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Настройка тестового контента - NewsCore</h1>
        
        <?php if ($result['success']) : ?>
            <div class="result-box success">
                <h2>✅ Настройка завершена успешно!</h2>
                <p><?php echo $result['message']; ?></p>
            </div>
            
            <div class="summary">
                <h3>📊 Создано:</h3>
                <ul>
                    <?php foreach ($result['summary'] as $item) : ?>
                        <li><?php echo $item; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            
            <div class="buttons">
                <a href="<?php echo home_url('/'); ?>" class="button" target="_blank">
                    Перейти на сайт
                </a>
                <a href="<?php echo admin_url(); ?>" class="button button-secondary">
                    В админку
                </a>
                <a href="<?php echo admin_url('tools.php?page=newscore-import-test-content'); ?>" class="button">
                    Управление контентом
                </a>
            </div>
            
        <?php else : ?>
            <div class="result-box error">
                <h2>❌ Ошибка настройки</h2>
                <p><?php echo $result['message']; ?></p>
            </div>
            
            <div class="buttons">
                <a href="<?php echo admin_url('tools.php?page=newscore-import-test-content'); ?>" class="button">
                    Попробовать через админку
                </a>
                <a href="<?php echo admin_url(); ?>" class="button button-secondary">
                    В админку
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Анимация прогресс-бара
        document.addEventListener('DOMContentLoaded', function() {
            var progressBar = document.querySelector('.progress-fill');
            if (progressBar) {
                progressBar.style.width = '0%';
                setTimeout(function() {
                    progressBar.style.transition = 'width 2s ease';
                    progressBar.style.width = '100%';
                }, 500);
            }
        });
    </script>
</body>
</html>
<?php
exit;