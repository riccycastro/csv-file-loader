# csv-file-loader
https://github.com/riccycastro/csv-file-loader

### Requirements
The file can contain an arbitrary number of rows.
Each row contains such fields in the following order:

- email
- lastname
- firstname
- fiscal code
- description
- last access date

NOTE:

- There cannot be more users with the same email in the DB
- All fields (with the exception of email) can be `null`
- Any file is the new master

### Scenario

- Given a new file to import, when I have imported the file, then the db state has to be updated
- Given an existing user, when the new imported file contains the same email, then the existing user data has to be updated
- Given a new file to import, when I upload a *.txt file, then I should receive an error
- Given a new file to import, when I upload a file that exceeds the limit (in MB), then I should receive an error


### Environment setup

First, make sure you have Docker and Docker-compose installed in your machine.
Clone this repo and enter the project folder.

Go to the application folder and clone the .env.example to .env.
Since this is for test purpose I'm going to type the correct configs here to help you out

``` dotenv
    DATABASE_URL=mysql://root:1admin!@csv-file-loader-mysql/loader-database
    MESSENGER_TRANSPORT_DSN=amqp://guest:guest@csv-file-loader-rabbitmq:5672
```

Copy the content from webserver/hosts and paste it in you local hosts file.

___

Now you can start your server

``` bash
    docker-compose up --build
```

This should be enough to have the app server up and running

_Be aware that at the first time you start the server, it will install the project
dependencies. It takes some seconds to complete. Run the bellow command if you want to keep track of the composer install_

``` bash
    docker logs -f --tail 100 csv-file-loader-app
```

Open your browser and go to http://localhost:8080, you should see PHPMyAdmin page. Create a new data base: '__loader-database__'

### Execution
Using Insomnia.rest(is an httpClient) import the _Insomnia_ file.

    Using a different httpClient?

        Config a post request to

        POST:http://csv-file-loader.local/files

        with a multipart body {file: your_file}

Enter the php container

``` bash
    docker exec -it csv-file-loader-app bash
```

Run migrations
``` bash
    bin/console doctrine:migrations:migrate
```

And start the consumer

``` bash
    bin/console messenger:consume
```

Execute the http-request. In the consumer cmd you should see something similar to this message
``` bash
    ## 2021-06-20 18:07:27: Message "App\Message\UserFileLoaderMessage" consumed successfully
```

Open your browser and go to http://localhost:8080, and open the user table where you should find the imported users

### Tests
To run tests enter the application container
``` bash
    docker exec -it csv-file-loader-app bash
```
Run the phpunit command
``` bash
    php bin/phpunit
```

This command will execute unit and integrations tests

_Notice that at the first time you execute this command, it will install all
phpunit dependencies and then start the tests_
