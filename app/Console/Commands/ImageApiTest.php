<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImageApiTest extends Command
{
    protected $signature = 'gemini:img';

    public function handle(): int
    {
        $apiKey = 'AIzaSyB9xQYHEos-ZI27psFlEkS79YTHZmfd3_U';

        // URL для генерации изображений
                $modelUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent';

        // Промпт для изображения
                $prompt = "Сгененрируй доброго котика, в разрешение очень маленьком hd.";

        // Имя файла, в который будет сохранено изображение
                $outputFileName = 'generated_image.jpg';

        // ===================================================
        // Шаг 2: Формирование запроса
        // ===================================================

        $requestBody = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ],

            ]
        ]);

        // ===================================================
        // Шаг 3: Настройка и выполнение cURL
        // ===================================================

        echo "⏳ Отправка запроса к Gemini API для генерации изображения...\n";

        $ch = curl_init($modelUrl . '?key=' . $apiKey);

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $requestBody,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60 * 10, // Установка таймаута на 60 секунд для генерации изображения
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($requestBody)
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo "❌ Ошибка cURL: " . curl_error($ch) . "\n";
            exit(1);
        }

        curl_close($ch);

        // ===================================================
        // Шаг 4: Обработка и сохранение ответа
        // ===================================================

                if ($httpCode !== 200) {
                    echo "❌ Ошибка API. HTTP-код: " . $httpCode . "\n";
                    echo "Ответ API: " . $response . "\n";
                    exit(1);
                }

                $data = json_decode($response, true);
        print_r($data);

            // Проверяем, есть ли данные изображения в ответе
                $base64Image = $data['candidates'][0]['content']['parts'][0]['inlineData']['data'] ?? null;

                if (!$base64Image) {
                    echo "❌ Failed to generate: No image data received from Gemini (как в вашей предыдущей ошибке).\n";
                    // Дополнительный анализ ошибки
                    $finishReason = $data['candidates'][0]['finishReason'] ?? 'N/A';
                    echo "Причина завершения: " . $finishReason . "\n";
                    echo "Проверьте ваш промпт на соответствие политике безопасности.\n";
                    exit(1);
                }

            // Декодируем Base64 строку в бинарные данные изображения
                $imageData = base64_decode($base64Image);

            // Сохраняем бинарные данные в файл
        if (file_put_contents($outputFileName, $imageData) !== false) {
            echo "✅ Изображение успешно сгенерировано и сохранено в файл: " . $outputFileName . "\n";
        } else {
            echo "❌ Ошибка при сохранении данных в файл " . $outputFileName . "\n";
            exit(1);
        }
        return  1;
    }
}