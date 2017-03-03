# nette-custom-logger
Odchytava Nette errory a posle log na email a smaze log


## config.neon changes


```sh
parameters:
    logger:
        params:
            debug: false
            del_log: true
            use_messanger: 'mail'
            recipients: 
                - hippo@project.cz
                - next@email.cz
            mailfrom: noreply@app.cz
            subject: null
```

Nette < 2.4.0
```sh
services:
    tracy.logger:
        class: Tracy\ILogger
        factory: Hippotame\Logger\Logger("%appDir%/../log","%logger.params%")
```
Nette >= 2.4.0
```sh
services:
    tracy.logger: 
        autowired: no
    - Hippotame\Logger\Logger("%appDir%/../log","%logger.params%")

```
