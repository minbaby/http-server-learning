<?php

namespace Minbaby\HttpServer;

class HttpBuffer
{
    const READ_LENGTH = 4096;

    protected $buffer = "";

    protected $bufferLength = 0;

    public function readDataFromSocket($socket)
    {
        $data = stream_socket_recvfrom($socket, self::READ_LENGTH);
        $dataLength = strlen($data);

        $this->buffer .= $data;
        $this->bufferLength += $dataLength;

        $statusCodes = [];

        while (null !== ($record = $this->readRecord())) {
            $statusCode = $this->processRecord($record);

            if (null != $statusCode) {
                $statusCodes[] = $statusCode;
            }
        }
    }

    private function readRecord()
    {
        // Not enough data to read header
        if ($this->bufferLength < 8) {
            return;
        }

        $headerData = substr($this->buffer, 0, 8);

        $headerFormat = 'Cversion/Ctype/nrequestId/ncontentLength/CpaddingLength/x';

        $record = unpack($headerFormat, $headerData);

        // Not enough data to read rest of record
        if ($this->bufferLength - 8 < $record['contentLength'] + $record['paddingLength']) {
            return;
        }

        $record['contentData'] = substr($this->buffer, 8, $record['contentLength']);

        // Remove the record from the buffer
        $recordSize = 8 + $record['contentLength'] + $record['paddingLength'];

        $this->buffer        = substr($this->buffer, $recordSize);
        $this->bufferLength -= $recordSize;

        return $record;
    }

    private function processRecord($record)
    {
        var_dump($record);
        return;
    }
}
