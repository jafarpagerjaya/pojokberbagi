<?php
class kirimWA {
    
    public static function apiKirimWaRequest(array $params) {
        $httpStreamOptions = [
          'method' => $params['method'] ?? 'GET',
          'header' => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . ($params['token'] ?? '')
          ],
          'timeout' => 15,
          'ignore_errors' => true
        ];
      
        if ($httpStreamOptions['method'] === 'POST') {
          $httpStreamOptions['header'][] = sprintf('Content-Length: %d', strlen($params['payload'] ?? ''));
          $httpStreamOptions['content'] = $params['payload'];
        }
      
        // Join the headers using CRLF
        $httpStreamOptions['header'] = implode("\r\n", $httpStreamOptions['header']) . "\r\n";
      
        $stream = stream_context_create(['http' => $httpStreamOptions]);
        $response = file_get_contents($params['url'], false, $stream);
      
        // Headers response are created magically and injected into
        // variable named $http_response_header
        $httpStatus = $http_response_header[0];
      
        preg_match('#HTTP/[\d\.]+\s(\d{3})#i', $httpStatus, $matches);
      
        if (! isset($matches[1])) {
          throw new Exception('Can not fetch HTTP response header.');
        }
      
        $statusCode = (int)$matches[1];
        if ($statusCode >= 200 && $statusCode < 300) {
          return ['body' => $response, 'statusCode' => $statusCode, 'headers' => $http_response_header];
        }
      
        throw new Exception($response, $statusCode);
    }

    public static function run() {
        try {
        $reqParams = [
            'token' => getenv('API_TOKEN'),
            'url' => 'https://api.kirimwa.id/v1/devices',
            'method' => 'POST',
            'payload' => json_encode([
            'device_id' => 'iphone-x-pro'
            ])
        ];
        
            $response = self::apiKirimWaRequest($reqParams);
            echo $response['body'];
        } catch (Exception $e) {
            print_r($e);
        }
    }
}