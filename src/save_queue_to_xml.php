<?php

namespace App;

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$absPathSaveQueueToXml = \sprintf(
    '/%s/%s',
    \trim(getenv('PROJECT_DIR'), '/\\'),
    \trim(\getenv('SAVE_QUEUE_TO_XML_REL_PATH'), '/\\'),
);
$readMessages = (int)\getenv('SAVE_QUEUE_TO_XML_READ_MESSAGES');
$maxAllowedMessagesToRead = (int)\getenv('SAVE_QUEUE_TO_XML_MAX_ALLOWED_MESSAGES_TO_READ');

$host =\getenv('RABBIT_MQ_HOST');
$port =\getenv('RABBIT_MQ_PORT');

$user = \getenv('RABBIT_MQ_USER');
$password = \getenv('RABBIT_MQ_PASSWORD');

$vhost = \getenv('SAVE_QUEUE_TO_XML_VHOST');
$queue = \getenv('SAVE_QUEUE_TO_XML_QUEUE');

if (
    empty($absPathSaveQueueToXml)
    || empty($maxAllowedMessagesToRead)
    || empty($readMessages)
    || empty($host)
    || empty($port)
    || empty($user)
    || empty($vhost)
    || empty($queue)
) {
    throw new \LogicException(
        'Определи все необходимые переменные среды в .env.local и перезапусти контейнер'
    );
}

if ($maxAllowedMessagesToRead < $readMessages) {
    $message = \sprintf(
        "Максимально можно скачать %s сообщений, но если сообщения маленькие, то можно в .env.local увеличить значение SAVE_QUEUE_TO_XML_MAX_ALLOWED_MESSAGES_TO_READ, %s",
        $maxAllowedMessagesToRead,
        'иначе есть риск падения скрипта',
    );
    throw new \LogicException($message);
}

if (!\is_dir($absPathSaveQueueToXml)) {
    \mkdir($absPathSaveQueueToXml);
}

$connection = new AMQPStreamConnection(
    $host,
    $port,
    $user,
    $password,
    $vhost
);

$channel = $connection->channel();

/**
 * Не даём RabbitMQ отправлять больше одного сообщения одновременно.
 */
$channel->basic_qos(
    null,
    1,
    null
);

$messages = [];

for ($i = 1; $i <= $readMessages; $i++) {

    $message = $channel->basic_get($queue, false);

    if ($message === null) {
        echo "Queue is empty, finish" . \PHP_EOL;
        break;
    }

    echo "Received message #{$i}, bytes: "
        . \strlen($message->body)
        . \PHP_EOL;

    $filename = \sprintf(
        "%s/message_%s_%s_%d.xml",
        \rtrim($absPathSaveQueueToXml, '/\\'),
        $vhost,
        $queue,
        $i
    );

    \file_put_contents(
        $filename,
        $message->body
    );

    echo "\033[32mSaved {$filename}\033[0m" . \PHP_EOL;

    /**
     * ВАЖНО:
     * Не возвращаем сообщение сейчас.
     * Оно остаётся unacked.
     */
    $messages[] = $message;
}

echo \sprintf(
    "\033[33mRead messages: %s\033[0m%s",
    \count($messages),
    \PHP_EOL,
);

/**
 * Возвращаем все полученные сообщения обратно.
 */
foreach ($messages as $message) {
    $channel->basic_nack(
        $message->delivery_info['delivery_tag'],
        false,
        true
    );
}

$channel->close();
$connection->close();

echo "Done" . \PHP_EOL;
