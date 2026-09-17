<?php

/**
 * Быстро разбирает ответ от get_get_info()
 * @return array ['ok' => bool, 'data' => mixed, 'errorCode' => ?string]
 */
function parse_curl_response($curlResult) {
    // Если есть ошибка на уровне HTTP/cURL
    if (isset($curlResult['error'])) {
        $decoded = @json_decode($curlResult['response'] ?? '', true);
        return [
            'ok'        => false,
            'errorCode' => $decoded['errorCode'] ?? null,
            'message'   => $decoded['message'] ?? ($curlResult['response'] ?? 'Unknown error'),
            'httpCode'  => $curlResult['code'] ?? 500
        ];
    }
    
    // Успех — данные уже в массиве
    return [
        'ok'   => true,
        'data' => $curlResult // или $curlResult['response'], если успех тоже приходит в 'response'
    ];
}

?>