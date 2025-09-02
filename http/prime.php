<?php

$VALID_OP = "isPrime";
$AUDIT_SOCK_ADDR = "/tmp/test.sock";

class PrimeValidationService {

    private string $daemonSocket;

    public function __construct(string $socketPath)
    {
        $this->daemonSocket = $socketPath;
    }

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->logger("Invalid request method");
            http_response_code(405);
            return;
        }

        $data = file_get_contents('php://input');
        $msg = json_decode($data, true);
        if (is_null($msg)) {
            $this->logger("failed to parse request: " . json_last_error_msg());
            http_response_code(400);
            return;
        }

        if (!$this->validateMessage($msg)) {
            $this->logger("failed to validate message: " . json_last_error_msg());
            http_response_code(400);
        }

        $number = $msg['number'];
        $isPrime = $this->isPrime($number);

        global $VALID_OP;

        $response = [
            'method' => $VALID_OP,
            'prime'  => $isPrime
        ];
        echo json_encode($response);

        if ($isPrime) {
            $this->pushAuditEvent($number);
        }
    }

    public function isPrime(int $n): bool
    {
        if ($n < 0) {
            return false;
        }
        if ($n === 0) {
            return false;
        }
        if ($n === 1) {
            return false;
        }
        for ($i = 2; $i < $n; $i++) {
            if ($n % $i === 0 ) {
                return false;
            }
        }
        return true;
    }

    public function validateMessage(array $message): bool
    {
        global $VALID_OP;
        $opType = isset($message['method']) ? $message['method'] : "";

        if ($opType !== $VALID_OP) {
            return false;
        }

        if (!isset($message['number']) || !is_int($message['number'])) {
            return false;
        }

        return true;
    }

    public function pushAuditEvent(int $number): void
    {
        if (!file_exists($this->daemonSocket)) {
            return;
        }
        $socket = "unix://{$this->daemonSocket}";

        $fp = fsockopen($socket, -1);
        stream_set_timeout($fp, 1);
        fwrite($fp, (string) $number);
        fclose($fp);
    }

    /*
     * We will include logging instruments of the wider system we plug into
     */
    private function logger(string $msg, int $lvl=0): void
    {
        echo $msg . PHP_EOL;
    }
}

$PVS = new PrimeValidationService($AUDIT_SOCK_ADDR);
$PVS->handleRequest();