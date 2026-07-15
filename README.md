### For what?

Rabbit mq utils that are not available via Rabbit MQ UI.


### How to run app

Create .env.local at the root
```
touch .env.local
```
> And type all the necessary envs from .env file

Copy docker setting files and edit then if needed
```
cp docker/dev/compose.override.yaml.dist docker/dev/compose.override.yaml
```
```
cp docker/dev/compose.yaml.dist docker/dev/compose.yaml
```
> If you need your own networks to access specific rabbit mq host, just add
```
# in the frankenphp service section of compose.yaml
networks:
    external_rabbit_mq:

# at the end of compose.yaml
networks:
    external_rabbit_mq:
        external: true
```

If you build this not for the first time, start with the following command at `docker/dev`
```
docker compose build --no-cache
```

Run application, execute at `docker/dev`
```
docker compose down && docker compose up -d
```

In the end you can stop the application
```
docker compose down
```


### How to use utils

Download full messages from rabbit mq queue and return them back
```
clear && 2>&1 docker exec -it rabbitmq_not_ui_available_util php /app/src/save_queue_to_xml.php
```
